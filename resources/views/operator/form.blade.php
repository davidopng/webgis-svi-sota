@extends('layouts.app', [
    'role' => $role ?? 'operator',
    'userName' => $userName ?? 'Maria K.'
])

@php
    $effectiveRole = Auth::user()?->role ?? ($role ?? 'operator');
    $isEdit = isset($id);
    $statusVerifikasi = $data['status_verifikasi'] ?? 'menunggu_verifikasi';
    $isLocked = $isEdit && $effectiveRole === 'operator' && $statusVerifikasi === 'terverifikasi';
    $pageTitle = $isLocked ? 'Lihat Data Warga' : ($isEdit ? 'Ubah Data Warga' : 'Input Data Warga Baru');
@endphp

@section('title', $pageTitle . ' — SOTA-GIS')
@section('page-title', $pageTitle)

@push('head')
    {{-- Ambil Koordinat Leaflet Preview & Script --}}
    @vite(['resources/js/ambil-koordinat.js'])
    <style>
        .sota-preview-marker {
            background: transparent !important;
            border: none !important;
        }
        #map-preview {
            isolation: isolate !important;
            z-index: 0 !important;
            overflow: hidden !important;
        }
        #map-preview .leaflet-pane,
        #map-preview .leaflet-top,
        #map-preview .leaflet-bottom {
            z-index: 1 !important;
        }
    </style>
@endpush

@section('content')
<script>
function sotaFormWarga() {
    return {
    // State Formulir & Progres
    editId: '{{ $id ?? '' }}',
    isEdit: {{ $isEdit ? 'true' : 'false' }},
    isLocked: {{ $isLocked ? 'true' : 'false' }},
    isSubmitting: false,
    errorMessage: '',
    modalKonfirmasiBatal: false,
    formDirty: false,

    // Step Progress Status & Bagian Aktif (1, 2, atau 3)
    activeSection: 1,
    step1Complete: false,
    step2Complete: false,
    step3Count: 0,

    // Nilai Form
    namaKK: @json($data->nama_kk ?? ($data['nama_kk'] ?? '')),
    statusPenduduk: @json($data->status_penduduk ?? ($data['status_penduduk'] ?? 'Tetap')),
    jalur: @json($data->jalur ?? ($data['jalur'] ?? 'Poros Trans')),
    rt: @json($data->rt ?? ($data['rt'] ?? 'RT 01')),
    rw: @json($data->rw ?? ($data['rw'] ?? 'RW 01')),
    noRumah: @json($data->no_rumah ?? ($data['no_rumah'] ?? '')),
    patokan: @json($data->patokan ?? ($data['patokan'] ?? '')),

    // GPS State
    gpsState: @json((isset($data->latitude) || isset($data['latitude'])) ? 'success' : 'idle'),
    gpsMessage: '',
    manualCoords: false,
    lat: @json((string)($data->latitude ?? ($data['latitude'] ?? ''))),
    lng: @json((string)($data->longitude ?? ($data['longitude'] ?? ''))),
    accuracy: {{ (isset($data->latitude) || isset($data['latitude'])) ? 6 : 'null' }},
    accuracyText: @json((isset($data->latitude) || isset($data['latitude'])) ? 'Akurasi ±6 meter · WGS 84' : ''),

    // Foto State
    fotoPreview: @json($data->foto_rumah ?? ($data['foto_rumah'] ?? '')),
    fotoNama: @json((isset($data->foto_rumah) || isset($data['foto_rumah'])) ? 'foto-rumah-terverifikasi.jpg' : ''),
    fotoUkuran: @json((isset($data->foto_rumah) || isset($data['foto_rumah'])) ? '1,4 MB' : ''),
    fotoError: '',

    // 9 Variabel Kuesioner SVI
    q1: @json($data->pendapatan ?? ($data['pendapatan'] ?? '')),
    q2: @json($data->pekerjaan ?? ($data['pekerjaan'] ?? '')),
    q2_other: '',
    q2_skor: '2',
    q3: @json($data->pendidikan ?? ($data['pendidikan'] ?? '')),
    q3_other: '',
    q3_skor: '3',
    q4: @json((string)($data->jumlah_anggota ?? ($data->jumlah_anggota_keluarga ?? ($data['jumlah_anggota_keluarga'] ?? '')))),
    q5a: @json($data->jenis_dinding ?? ($data['jenis_dinding'] ?? '')),
    q5a_other: '',
    q5a_skor: '3',
    q5b: @json($data->jenis_lantai ?? ($data['jenis_lantai'] ?? '')),
    q5b_other: '',
    q5b_skor: '3',
    q6: @json($data->fasilitas_sanitasi ?? ($data['fasilitas_sanitasi'] ?? '')),
    q6_other: '',
    q6_skor: '3',
    q7: @json($data->sumber_air ?? ($data['sumber_air'] ?? '')),
    q7_other: '',
    q7_skor: '2',
    q8: @json($data->pendapatan_tambahan ?? ($data['pendapatan_tambahan'] ?? '')),
    q8_other: '',
    q8_skor: '1',
    q9: @json($data->kepemilikan_lahan ?? ($data['kepemilikan_lahan'] ?? '')),
    q9_other: '',
    q9_skor: '1',

    autoDetectScore(field) {
        if (field === 'q2') {
            const text = (this.q2_other || '').toLowerCase();
            if (text.match(/dosen|notaris|dokter|manajer|direktur|pejabat|pns|guru|polri|tni|karyawan tetap|pegawai tetap|wiraswasta/)) this.q2_skor = '1';
            else if (text.match(/nelayan|pengrajin|kebun|ternak|ojek|bengkel|kios|jual|warung|kontrak|honorer|pedagang/)) this.q2_skor = '2';
            else if (text.match(/kuli|pemulung|pengemis|kayu|kasar|serabutan|buruh|tidak bekerja|menganggur|irt|ibu rumah tangga/)) this.q2_skor = '3';
        } else if (field === 'q3') {
            const text = (this.q3_other || '').toLowerCase();
            if (text.match(/sarjana|diploma|s1|s2|s3|universitas|kuliah|akadem|institut|politeknik/)) this.q3_skor = '1';
            else if (text.match(/sma|smk|ma|kejuruan|paket c/)) this.q3_skor = '2';
            else this.q3_skor = '3';
        } else if (field === 'q5a') {
            const text = (this.q5a_other || '').toLowerCase();
            if (text.match(/tembok|batako|batu|hebel|bata/)) this.q5a_skor = '1';
            else if (text.match(/campur|semi/)) this.q5a_skor = '2';
            else this.q5a_skor = '3';
        } else if (field === 'q5b') {
            const text = (this.q5b_other || '').toLowerCase();
            if (text.match(/keramik|ubin|granit|marmer/)) this.q5b_skor = '1';
            else if (text.match(/semen|plester/)) this.q5b_skor = '2';
            else this.q5b_skor = '3';
        } else if (field === 'q6') {
            const text = (this.q6_other || '').toLowerCase();
            if (text.match(/pribadi|sendiri|leher angsa|sehat/)) this.q6_skor = '1';
            else if (text.match(/umum|tetangga|berbagi|bersama/)) this.q6_skor = '2';
            else this.q6_skor = '3';
        } else if (field === 'q7') {
            const text = (this.q7_other || '').toLowerCase();
            if (text.match(/pdam|perpipaan/)) this.q7_skor = '1';
            else if (text.match(/hujan|tadah|pah|profil|tangki|kemasan|galon|bor|pompa/)) this.q7_skor = '2';
            else this.q7_skor = '3';
        } else if (field === 'q8') {
            const text = (this.q8_other || '').toLowerCase();
            if (text.match(/usaha|warung|kios|kebun|ternak|jual|sampingan|dagang|toko/)) this.q8_skor = '1';
            else if (text.match(/bantuan|pemerintah|pkh|blt|bansos|transfer/)) this.q8_skor = '2';
            else this.q8_skor = '3';
        } else if (field === 'q9') {
            const text = (this.q9_other || '').toLowerCase();
            if (text.match(/adat|ulayat|marga|suku|sendiri|shm|warisan/)) this.q9_skor = '1';
            else if (text.match(/sewa|pinjam|garap|kontrak/)) this.q9_skor = '2';
            else this.q9_skor = '3';
        }
    },

    init() {
        // Cek jika mode edit dan data tersimpan di localStorage
        if (this.editId && window.SotaStorage) {
            const saved = window.SotaStorage.getById(this.editId);
            if (saved) {
                if (saved.status === 'terverifikasi' && '{{ $role ?? 'operator' }}' === 'operator') {
                    this.isLocked = true;
                }
                this.namaKK = saved.nama_kk || this.namaKK;
                this.statusPenduduk = saved.status_penduduk || this.statusPenduduk;
                this.jalur = saved.jalur || this.jalur;
                this.rt = saved.rt || this.rt;
                this.rw = saved.rw || this.rw;
                this.noRumah = saved.no_rumah ? saved.no_rumah.replace('No. ', '') : this.noRumah;
                this.patokan = saved.patokan || this.patokan;
                this.lat = saved.latitude ? String(saved.latitude) : this.lat;
                this.lng = saved.longitude ? String(saved.longitude) : this.lng;
                this.fotoPreview = saved.foto_rumah || this.fotoPreview;
                if (saved.raw_form) {
                    this.q1 = String(saved.raw_form.q1 || '');
                    this.q2 = String(saved.raw_form.q2 || '');
                    this.q2_other = String(saved.raw_form.q2_other || '');
                    this.q2_skor = String(saved.raw_form.q2_skor || '2');
                    this.q3 = String(saved.raw_form.q3 || '');
                    this.q3_other = String(saved.raw_form.q3_other || '');
                    this.q3_skor = String(saved.raw_form.q3_skor || '3');
                    this.q4 = String(saved.raw_form.q4 || '');
                    this.q5a = String(saved.raw_form.q5a || '');
                    this.q5a_other = String(saved.raw_form.q5a_other || '');
                    this.q5a_skor = String(saved.raw_form.q5a_skor || '3');
                    this.q5b = String(saved.raw_form.q5b || '');
                    this.q5b_other = String(saved.raw_form.q5b_other || '');
                    this.q5b_skor = String(saved.raw_form.q5b_skor || '3');
                    this.q6 = String(saved.raw_form.q6 || '');
                    this.q6_other = String(saved.raw_form.q6_other || '');
                    this.q6_skor = String(saved.raw_form.q6_skor || '3');
                    this.q7 = String(saved.raw_form.q7 || '');
                    this.q7_other = String(saved.raw_form.q7_other || '');
                    this.q7_skor = String(saved.raw_form.q7_skor || '2');
                    this.q8 = String(saved.raw_form.q8 || '');
                    this.q8_other = String(saved.raw_form.q8_other || '');
                    this.q8_skor = String(saved.raw_form.q8_skor || '1');
                    this.q9 = String(saved.raw_form.q9 || '');
                    this.q9_other = String(saved.raw_form.q9_other || '');
                    this.q9_skor = String(saved.raw_form.q9_skor || '1');
                }
            }
        }

        // Normalisasi data warisan (legacy) jika tersimpan sebagai nilai lama
        const mapLegacyQ1 = { '1': '1|Rp2.500.000 ke atas per bulan', '2': '2|Rp1.000.000 – Rp2.499.999 per bulan', '3': '3|Kurang dari Rp1.000.000 per bulan' };
        const mapLegacyQ2 = { '1': '1|PNS / TNI / POLRI', '2': '2|Petani', '3': '3|Buruh Harian Lepas' };
        const mapLegacyQ3 = { '1': '1|Perguruan Tinggi (Diploma/S1/S2)', '2': '2|Tamat SMA / Sederajat', '3': '3|SD / SMP / Tidak Tamat Sekolah' };
        const mapLegacyQ4 = { '1': '1|1 – 2 orang', '2': '2|3 – 4 orang', '3': '3|> 4 orang' };
        const mapLegacyQ5a = { '1': '1|Tembok', '2': '2|Kayu dan Tembok (Campuran)', '3': '3|Kayu' };
        const mapLegacyQ5b = { '1': '1|Keramik / Ubin', '2': '2|Semen', '3': '3|Tanah / Papan / Kayu' };
        const mapLegacyQ6 = { '1': '1|MCK Pribadi / Jamban Sehat', '2': '2|MCK Umum / Jamban berbagi dengan tetangga', '3': '3|Tidak Punya Sanitasi / Tidak Punya MCK' };
        const mapLegacyQ7 = { '1': '1|PDAM', '2': '2|Sumur Bor / Galon', '3': '3|Sumur Gali' };
        const mapLegacyQ8 = { '1': '1|Usaha kecil / pekerjaan sampingan aktif', '2': '2|Hanya menerima bantuan pemerintah', '3': '3|Tidak ada pendapatan tambahan' };
        const mapLegacyQ9 = { '1': '1|Milik sendiri / Warisan keluarga', '2': '2|Pinjam pakai / Sewa / Garapan', '3': '3|Tidak memiliki lahan' };

        if (this.q1 && mapLegacyQ1[this.q1]) this.q1 = mapLegacyQ1[this.q1];
        if (this.q2 && mapLegacyQ2[this.q2]) this.q2 = mapLegacyQ2[this.q2];
        if (this.q3 && mapLegacyQ3[this.q3]) this.q3 = mapLegacyQ3[this.q3];
        if (this.q4 && mapLegacyQ4[this.q4]) this.q4 = mapLegacyQ4[this.q4];
        if (this.q5a && mapLegacyQ5a[this.q5a]) this.q5a = mapLegacyQ5a[this.q5a];
        if (this.q5b && mapLegacyQ5b[this.q5b]) this.q5b = mapLegacyQ5b[this.q5b];
        if (this.q6 && mapLegacyQ6[this.q6]) this.q6 = mapLegacyQ6[this.q6];
        if (this.q7 && mapLegacyQ7[this.q7]) this.q7 = mapLegacyQ7[this.q7];
        if (this.q8 && mapLegacyQ8[this.q8]) this.q8 = mapLegacyQ8[this.q8];
        if (this.q9 && mapLegacyQ9[this.q9]) this.q9 = mapLegacyQ9[this.q9];

        // Normalisasi teks dari database agar cocok dengan opsi dropdown
        if (this.q1 && !this.q1.includes('|')) {
            const v = this.q1.toLowerCase();
            if (v.includes('2.500.000') || v.includes('5.000.000')) this.q1 = '1|Rp2.500.000 ke atas per bulan';
            else if (v.includes('1.000.000')) this.q1 = '2|Rp1.000.000 – Rp2.499.999 per bulan';
            else this.q1 = '3|Kurang dari Rp1.000.000 per bulan';
        }
        if (this.q2 && !this.q2.includes('|')) {
            const v = this.q2.toLowerCase();
            if (v.includes('pns') || v.includes('tni') || v.includes('polri')) this.q2 = '1|PNS / TNI / POLRI';
            else if (v.includes('guru')) this.q2 = '1|Guru PNS';
            else if (v.includes('wiraswasta')) this.q2 = '1|Wiraswasta Mandiri';
            else if (v.includes('karyawan') || v.includes('pegawai tetap')) this.q2 = '1|Karyawan Tetap';
            else if (v.includes('petani')) this.q2 = '2|Petani';
            else if (v.includes('peternak')) this.q2 = '2|Peternak';
            else if (v.includes('pedagang')) this.q2 = '2|Pedagang Kecil';
            else if (v.includes('honorer')) this.q2 = '2|Pegawai Honorer';
            else if (v.includes('kontrak')) this.q2 = '2|Pegawai Kontrak';
            else if (v.includes('buruh')) this.q2 = '3|Buruh Harian Lepas';
            else if (v.includes('serabutan')) this.q2 = '3|Pekerja Serabutan';
            else if (v.includes('ibu rumah') || v.includes('irt')) this.q2 = '3|Ibu Rumah Tangga';
            else if (v.includes('tidak bekerja') || v.includes('menganggur')) this.q2 = '3|Tidak Bekerja';
            else {
                this.q2_other = this.q2;
                this.q2 = 'lainnya|Lainnya';
            }
        }
        if (this.q3 && !this.q3.includes('|')) {
            const v = this.q3.toLowerCase();
            if (v.includes('perguruan') || v.includes('diploma') || v.includes('sarjana') || v.includes('s1')) this.q3 = '1|Perguruan Tinggi (Diploma/S1/S2)';
            else if (v.includes('sma') || v.includes('smk') || v.includes('sederajat')) this.q3 = '2|Tamat SMA / Sederajat';
            else if (v.includes('smp')) this.q3 = '3|Tamat SMP / Sederajat';
            else if (v.includes('sd')) this.q3 = '3|Tamat SD / Sederajat';
            else this.q3 = '3|Tidak Tamat Sekolah';
        }
        if (this.q4 && !this.q4.includes('|')) {
            const n = parseInt(this.q4) || 2;
            if (n <= 2) this.q4 = '1|1 – 2 orang';
            else if (n <= 4) this.q4 = '2|3 – 4 orang';
            else this.q4 = '3|> 4 orang';
        }
        if (this.q5a && !this.q5a.includes('|')) {
            const v = this.q5a.toLowerCase();
            if (v.includes('permanen') || v.includes('tembok')) this.q5a = '1|Tembok';
            else if (v.includes('campuran') || v.includes('semi')) this.q5a = '2|Kayu dan Tembok (Campuran)';
            else this.q5a = '3|Kayu';
        }
        if (this.q5b && !this.q5b.includes('|')) {
            const v = this.q5b.toLowerCase();
            if (v.includes('keramik') || v.includes('ubin')) this.q5b = '1|Keramik / Ubin';
            else if (v.includes('semen') || v.includes('plester')) this.q5b = '2|Semen';
            else this.q5b = '3|Tanah / Papan / Kayu';
        }
        if (this.q6 && !this.q6.includes('|')) {
            const v = this.q6.toLowerCase();
            if (v.includes('pribadi') || v.includes('leher angsa')) this.q6 = '1|MCK Pribadi / Jamban Sehat';
            else if (v.includes('umum') || v.includes('tetangga') || v.includes('berbagi')) this.q6 = '2|MCK Umum / Jamban berbagi dengan tetangga';
            else this.q6 = '3|Tidak Punya Sanitasi / Tidak Punya MCK';
        }
        if (this.q7 && !this.q7.includes('|')) {
            const v = this.q7.toLowerCase();
            if (v.includes('pdam')) this.q7 = '1|PDAM';
            else if (v.includes('bor')) this.q7 = '2|Sumur Bor';
            else if (v.includes('galon')) this.q7 = '2|Air Galon';
            else this.q7 = '3|Sumur Gali';
        }
        if (this.q8 && !this.q8.includes('|')) {
            const v = this.q8.toLowerCase();
            if (v.includes('sampingan') || v.includes('kebun') || v.includes('usaha')) this.q8 = '1|Usaha kecil / pekerjaan sampingan aktif';
            else if (v.includes('bantuan')) this.q8 = '2|Hanya menerima bantuan pemerintah';
            else this.q8 = '3|Tidak ada pendapatan tambahan';
        }
        if (this.q9 && !this.q9.includes('|')) {
            const v = this.q9.toLowerCase();
            if (v.includes('sendiri') || v.includes('warisan')) this.q9 = '1|Milik sendiri / Warisan keluarga';
            else if (v.includes('adat') || v.includes('pinjam') || v.includes('numpang') || v.includes('sewa')) this.q9 = '2|Pinjam pakai / Sewa / Garapan';
            else this.q9 = '3|Tidak memiliki lahan';
        }

        this.updateProgress();

        // Scroll spy untuk memantau bagian yang sedang aktif dan menyalakan tab stepper yang sesuai
        const handleScroll = () => {
            const s1 = document.getElementById('bagian-1');
            const s2 = document.getElementById('bagian-2');
            const s3 = document.getElementById('bagian-3');
            if (!s1 || !s2 || !s3) return;

            const scrollPos = window.scrollY + 200;
            if (scrollPos >= s3.offsetTop) {
                this.activeSection = 3;
            } else if (scrollPos >= s2.offsetTop) {
                this.activeSection = 2;
            } else {
                this.activeSection = 1;
            }
        };
        window.addEventListener('scroll', handleScroll, { passive: true });

        // Dengarkan pembaruan koordinat langsung dari Leaflet (drag & click pin peta)
        window.addEventListener('sota:coord-updated', (e) => {
            if (this.isLocked) return;
            this.lat = e.detail.lat;
            this.lng = e.detail.lng;
            this.accuracyText = (e.detail.source === 'click')
                ? 'Posisi disesuaikan dari klik peta · WGS 84'
                : (e.detail.source === 'preset' ? 'Posisi diarahkan ke wilayah Sota · WGS 84' : 'Posisi disesuaikan manual · WGS 84');
            this.formDirty = true;
            this.updateProgress();
        });

        // Inisialisasi Peta Pratinjau jika mode edit dan ada koordinat
        if (this.lat && this.lng) {
            setTimeout(() => {
                if (window.sotaInitPreviewMap) {
                    window.sotaInitPreviewMap(parseFloat(this.lat), parseFloat(this.lng));
                }
            }, 300);
        }
    },

    scrollToSection(id, num) {
        this.activeSection = num;
        const el = document.getElementById(id);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    },

    updateProgress() {
        // Cek Kelengkapan Bagian 1
        this.step1Complete = (this.namaKK.trim().length > 0) && (this.jalur.length > 0) && (this.statusPenduduk.length > 0);

        // Cek Kelengkapan Bagian 2
        this.step2Complete = (this.lat !== '' && this.lng !== '') && (this.fotoPreview !== '');

        // Hitung Kelengkapan 9 Variabel Bagian 3
        let count = 0;
        if (this.q1) count++;
        if (this.q2) count++;
        if (this.q3) count++;
        if (this.q4) count++;
        if (this.q5a && this.q5b) count++; // 5a & 5b dihitung sebagai SATU unit
        if (this.q6) count++;
        if (this.q7) count++;
        if (this.q8) count++;
        if (this.q9) count++;
        this.step3Count = count;
    },

    // Aksi Sinkronisasi Koordinat yang Diketik Manual
    handleCoordInput() {
        if (this.isLocked) return;
        const latNum = parseFloat(this.lat);
        const lngNum = parseFloat(this.lng);
        if (!isNaN(latNum) && !isNaN(lngNum) && latNum >= -90 && latNum <= 90 && lngNum >= -180 && lngNum <= 180) {
            if (window.sotaInitPreviewMap) {
                window.sotaInitPreviewMap(latNum, lngNum, { keepView: false });
            }
            this.accuracyText = 'Posisi disesuaikan secara manual · WGS 84';
        }
        this.formDirty = true;
        this.updateProgress();
    },

    // Aksi Ambil GPS
    panggilGPS() {
        if (this.isLocked) return;
        this.gpsState = 'loading';
        this.gpsMessage = 'Sedang mencari sinyal satelit GPS…';

        window.sotaAmbilGPS({
            onStart: () => {
                this.gpsState = 'loading';
            },
            onSuccess: (res) => {
                this.gpsState = 'success';
                this.lat = res.lat.toFixed(5);
                this.lng = res.lng.toFixed(5);
                this.accuracy = res.accuracy;
                this.accuracyText = `Akurasi ±${res.accuracy} meter · diambil ${res.timeStr} · WGS 84`;
                this.formDirty = true;
                this.updateProgress();
            },
            onError: (msg, isDenied) => {
                this.gpsState = isDenied ? 'denied' : 'error';
                this.gpsMessage = msg;
            }
        });
    },

    // Aksi Unggah Foto Rumah
    handleFileChange(event) {
        if (this.isLocked) return;
        const file = event.target.files[0];
        if (!file) return;

        // Validasi Ukuran File Maksimal 5 MB (PRD 10.4)
        if (file.size > 5 * 1024 * 1024) {
            this.fotoError = 'Ukuran berkas melebihi batas maksimal 5 MB. Harap gunakan foto yang lebih kecil.';
            event.target.value = '';
            return;
        }

        this.fotoError = '';
        this.fotoNama = file.name;
        this.fotoUkuran = (file.size / (1024 * 1024)).toFixed(1) + ' MB';

        // Kompresi di sisi klien via Canvas (PRD 10.4)
        window.sotaCompressImage(file, 800, 0.65)
            .then(res => {
                this.fotoPreview = res.dataUrl;
                this.formDirty = true;
                this.updateProgress();
            })
            .catch(() => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.fotoPreview = e.target.result;
                    this.formDirty = true;
                    this.updateProgress();
                };
                reader.readAsDataURL(file);
            });
    },

    hapusFoto() {
        if (this.isLocked) return;
        this.fotoPreview = '';
        this.fotoNama = '';
        this.fotoUkuran = '';
        this.updateProgress();
    },

    // Validasi & Simpan Form
    submitForm(e) {
        if (this.isLocked) return;

        // Validasi Kelengkapan Isian
        if (!this.step1Complete) {
            this.errorMessage = 'Isian Bagian 1 (Nama KK atau Jalur) belum lengkap.';
            document.getElementById('bagian-1').scrollIntoView({ behavior: 'smooth' });
            document.getElementById('nama_kk').focus();
            return;
        }

        if (!this.lat || !this.lng) {
            this.errorMessage = 'Titik koordinat GPS rumah wajib diambil.';
            document.getElementById('bagian-2').scrollIntoView({ behavior: 'smooth' });
            return;
        }

        if (!this.fotoPreview) {
            this.errorMessage = 'Foto rumah tampak depan wajib diunggah.';
            document.getElementById('bagian-2').scrollIntoView({ behavior: 'smooth' });
            return;
        }

        if (this.step3Count < 9) {
            const kurang = 9 - this.step3Count;
            this.errorMessage = `${kurang} pertanyaan kuesioner SVI belum terisi. Harap lengkapi seluruh variabel.`;
            document.getElementById('bagian-3').scrollIntoView({ behavior: 'smooth' });
            return;
        }

        this.errorMessage = '';
        this.isSubmitting = true;

        // Helper ekstraksi skor numerik (1, 2, atau 3) dan label teks spesifik
        const parseScore = (val, customScore = null, defaultScore = 1) => {
            if (!val) return defaultScore;
            if (String(val).startsWith('lainnya')) {
                return customScore ? parseInt(customScore) : defaultScore;
            }
            const parts = String(val).split('|');
            return parseInt(parts[0]) || defaultScore;
        };

        const parseLabel = (val, customVal, fallback = '') => {
            if (!val) return fallback;
            if (String(val).startsWith('lainnya')) {
                return (customVal && customVal.trim()) ? customVal.trim() : 'Lainnya';
            }
            const parts = String(val).split('|');
            return parts.length > 1 ? parts[1] : parts[0];
        };

        // Hitung Skor SVI Otomatis (1..3 per variabel)
        const v1 = parseScore(this.q1, null, 1);
        const v2 = parseScore(this.q2, this.q2_skor, 2);
        const v3 = parseScore(this.q3, this.q3_skor, 3);
        const v4 = parseScore(this.q4, null, 2);
        const v5a = parseScore(this.q5a, this.q5a_skor, 3);
        const v5b = parseScore(this.q5b, this.q5b_skor, 3);
        const v5 = Math.ceil((v5a + v5b) / 2);
        const v6 = parseScore(this.q6, this.q6_skor, 3);
        const v7 = parseScore(this.q7, this.q7_skor, 2);
        const v8 = parseScore(this.q8, this.q8_skor, 1);
        const v9 = parseScore(this.q9, this.q9_skor, 1);

        const totalSkor = v1 + v2 + v3 + v4 + v5 + v6 + v7 + v8 + v9;
        const skorRataRata = (totalSkor / 9).toFixed(2).replace('.', ',');

        let klasifikasi = 'rendah';
        let klasifikasiLabel = 'Kerentanan Rendah';
        if (totalSkor >= 21) {
            klasifikasi = 'tinggi';
            klasifikasiLabel = 'Kerentanan Tinggi';
        } else if (totalSkor >= 15) {
            klasifikasi = 'sedang';
            klasifikasiLabel = 'Kerentanan Sedang';
        }

        const labelQ1 = parseLabel(this.q1, '', 'Rp1.000.000 – Rp2.499.999 per bulan');
        const labelQ2 = parseLabel(this.q2, this.q2_other, 'Petani');
        const labelQ3 = parseLabel(this.q3, this.q3_other, 'Tamat SMA / Sederajat');
        const labelQ4 = parseLabel(this.q4, '', '3 – 4 orang');
        const labelQ5a = parseLabel(this.q5a, this.q5a_other, 'Kayu');
        const labelQ5b = parseLabel(this.q5b, this.q5b_other, 'Semen');
        const labelQ6 = parseLabel(this.q6, this.q6_other, 'MCK Umum / Jamban berbagi dengan tetangga');
        const labelQ7 = parseLabel(this.q7, this.q7_other, 'Sumur Bor / Galon');
        const labelQ8 = parseLabel(this.q8, this.q8_other, 'Tidak ada pendapatan tambahan');
        const labelQ9 = parseLabel(this.q9, this.q9_other, 'Milik sendiri / Warisan keluarga');

        const now = new Date();
        const waktuStr = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ', ' + String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') + ' WIT';
        const tglRingkas = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' + String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

        const dataBaru = {
            id: this.editId || ('warga_' + Date.now()),
            nama_kk: this.namaKK.trim(),
            status_penduduk: this.statusPenduduk,
            jalur: this.jalur,
            rt: this.rt,
            rw: this.rw,
            no_rumah: this.noRumah ? ('No. ' + this.noRumah) : '',
            alamat: `${this.rt} / ${this.rw}${this.noRumah ? ' No. ' + this.noRumah : ''}`,
            patokan: this.patokan || '',
            operator: '{{ $userName ?? 'Maria K.' }}',
            waktu_input: waktuStr,
            waktu_ringkas: 'Baru saja',
            diinput_pada: tglRingkas,
            status: 'menunggu_verifikasi',
            status_verifikasi: 'menunggu_verifikasi',
            latitude: parseFloat(this.lat) || -8.44271,
            longitude: parseFloat(this.lng) || 140.86935,
            lokasi_valid: true,
            foto_rumah: this.fotoPreview,
            skor: totalSkor,
            skor_rata_rata: skorRataRata,
            klasifikasi: klasifikasi,
            klasifikasi_label: klasifikasiLabel,
            pekerjaan: labelQ2,
            pendapatan: labelQ1,
            pendidikan: labelQ3,
            jumlah_anggota: labelQ4,
            dinding: labelQ5a,
            lantai: labelQ5b,
            fasilitas_sanitasi: labelQ6,
            sumber_air: labelQ7,
            pendapatan_tambahan: labelQ8,
            kepemilikan_lahan: labelQ9,
            atap: 'Seng',
            svi_items: [
                { no: 1, label: 'Pendapatan', jawaban: labelQ1, skor: v1 },
                { no: 2, label: 'Pekerjaan', jawaban: labelQ2, skor: v2 },
                { no: 3, label: 'Pendidikan', jawaban: labelQ3, skor: v3 },
                { no: 4, label: 'Anggota keluarga', jawaban: labelQ4, skor: v4 },
                { no: 5, label: 'Fisik bangunan', jawaban: `${labelQ5a} + ${labelQ5b}`, skor: v5 },
                { no: 6, label: 'Fasilitas sanitasi', jawaban: labelQ6, skor: v6 },
                { no: 7, label: 'Sumber air', jawaban: labelQ7, skor: v7 },
                { no: 8, label: 'Pendapatan tambahan', jawaban: labelQ8, skor: v8 },
                { no: 9, label: 'Kepemilikan lahan', jawaban: labelQ9, skor: v9 }
            ],
            raw_form: {
                q1: this.q1,
                q2: this.q2,
                q2_other: this.q2_other,
                q2_skor: this.q2_skor,
                q3: this.q3,
                q3_other: this.q3_other,
                q3_skor: this.q3_skor,
                q4: this.q4,
                q5a: this.q5a,
                q5a_other: this.q5a_other,
                q5a_skor: this.q5a_skor,
                q5b: this.q5b,
                q5b_other: this.q5b_other,
                q5b_skor: this.q5b_skor,
                q6: this.q6,
                q6_other: this.q6_other,
                q6_skor: this.q6_skor,
                q7: this.q7,
                q7_other: this.q7_other,
                q7_skor: this.q7_skor,
                q8: this.q8,
                q8_other: this.q8_other,
                q8_skor: this.q8_skor,
                q9: this.q9,
                q9_other: this.q9_other,
                q9_skor: this.q9_skor
            }
        };

        // Simpan data langsung ke Database Backend melalui Endpoint HTTP
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
        const rolePrefix = '{{ $effectiveRole }}' === 'admin' ? 'admin' : 'operator';
        const targetUrl = this.isEdit ? ('/' + rolePrefix + '/data/' + this.editId) : ('/' + rolePrefix + '/data');

        fetch(targetUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(dataBaru)
        })
        .then(async res => {
            if (res.ok === false) {
                const errJson = await res.json().catch(() => null);
                const msg = errJson?.message || ('HTTP error ' + res.status);
                throw new Error(msg);
            }
            return res.json();
        })
        .then(result => {
            if (window.SotaStorage) {
                window.SotaStorage.save(dataBaru);
            }
            this.isSubmitting = false;
            if (result && result.redirect) {
                window.location.href = result.redirect;
            } else if (rolePrefix === 'admin') {
                window.location.href = this.isEdit ? '/admin/data?notif=ubah' : '/admin/data?notif=simpan';
            } else {
                window.location.href = this.isEdit ? '/operator/data?notif=ubah' : '/operator/data?notif=simpan';
            }
        })
        .catch(err => {
            console.error('Error saat menyimpan ke server:', err);
            this.isSubmitting = false;
            if (!navigator.onLine || err.name === 'TypeError' || (err.message && err.message.toLowerCase().includes('failed to fetch'))) {
                if (window.SotaStorage) {
                    window.SotaStorage.save(dataBaru);
                }
                if (window.sotaToast) {
                    window.sotaToast('Penyimpanan lokal: Data tersimpan di perangkat offline.', 'peringatan');
                }
                setTimeout(() => {
                    if (rolePrefix === 'admin') {
                        window.location.href = this.isEdit ? '/admin/data?notif=ubah' : '/admin/data?notif=simpan';
                    } else {
                        window.location.href = this.isEdit ? '/operator/data?notif=ubah' : '/operator/data?notif=simpan';
                    }
                }, 1000);
                return;
            }
            this.errorMessage = 'Data gagal disimpan. Silakan coba lagi.';
            if (window.sotaToast) {
                window.sotaToast('Data gagal disimpan. Silakan coba lagi.', 'gagal');
            } else {
                alert('Data gagal disimpan. Silakan coba lagi.');
            }
        });
    }
};
}
</script>

<div x-data="sotaFormWarga()" class="max-w-[720px] mx-auto pb-32 space-y-6">

    {{-- Tombol Navigasi Kembali --}}
    <div>
        <a href="{{ $effectiveRole === 'admin' ? '/admin/data' : '/operator/data' }}"
           @click.prevent="formDirty ? (modalKonfirmasiBatal = true) : (window.location.href = $el.href)"
           class="inline-flex items-center gap-2 text-sm font-medium text-muted hover:text-ink transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Kembali ke Daftar Data
        </a>
    </div>

    {{-- Mode Ubah / Lihat: Spanduk Peringatan Khusus --}}
    @if($isEdit)
        @if($isLocked)
            <div class="rounded-xl bg-rendah-bg/50 border border-rendah/40 p-4 flex items-start gap-3 shadow-xs">
                <div class="h-6 w-6 rounded-full bg-rendah text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">
                    ✓
                </div>
                <div class="text-xs">
                    <p class="font-bold text-rendah-tx text-sm">Data Telah Di Verifikasi</p>
                    <p class="text-muted mt-0.5 leading-relaxed">Data survei warga ini telah diverifikasi oleh Admin dan aktif di peta sebaran. Formulir terkunci dalam mode baca (read-only) dan tidak dapat diubah oleh Operator.</p>
                </div>
            </div>
        @else
            <div class="rounded-lg bg-sedang-bg border border-sedang/30 p-4 flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-sedang-tx flex-shrink-0 mt-0.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <div class="text-xs text-sedang-tx">
                    <p class="font-bold">Mode Pengubahan Data</p>
                    <p class="mt-0.5">Perubahan isian survei akan menghitung ulang skor SVI secara otomatis setelah disimpan.</p>
                </div>
            </div>
        @endif
    @endif

    {{-- ============================================================
         INDIKATOR KEMAJUAN LENGKET (STICKY PROGRESS TABS) DENGAN NYALA AKTIF
         ============================================================ --}}
    <div class="sticky top-14 z-20 bg-surface/95 backdrop-blur-md border border-line rounded-xl p-2 shadow-sm flex items-center justify-between text-xs font-semibold gap-1 sm:gap-2">
        {{-- Tab 1: Identitas --}}
        <button type="button"
           @click.prevent="scrollToSection('bagian-1', 1)"
           :class="{
               'bg-rendah-bg text-rendah-tx border border-rendah/50 ring-2 ring-rendah/60 font-bold': isLocked && activeSection === 1,
               'bg-rendah-bg text-rendah-tx border border-rendah/30': isLocked && activeSection !== 1,
               'bg-brand text-white font-bold shadow-md ring-2 ring-brand/30': !isLocked && activeSection === 1,
               'bg-rendah-bg text-rendah-tx border border-rendah/30 hover:bg-rendah-bg/80': !isLocked && activeSection !== 1 && step1Complete,
               'text-muted hover:text-ink hover:bg-paper': !isLocked && activeSection !== 1 && !step1Complete
           }"
           class="flex-1 flex items-center justify-center gap-1.5 py-2 px-2 sm:px-3 rounded-lg transition-all text-center">
            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold"
                  :class="{
                      'bg-rendah text-white': isLocked || (!isLocked && step1Complete && activeSection !== 1),
                      'bg-white text-brand': !isLocked && activeSection === 1,
                      'bg-paper border border-line text-muted': !isLocked && activeSection !== 1 && !step1Complete
                  }">
                <template x-if="isLocked || (step1Complete && activeSection !== 1)"><span>✓</span></template>
                <template x-if="!isLocked && (!step1Complete || activeSection === 1)"><span>1</span></template>
            </span>
            <span class="hidden sm:inline">Identitas</span>
            <span class="sm:hidden">1. ID</span>
        </button>

        <div class="h-0.5 w-3 sm:w-6 bg-line shrink-0"></div>

        {{-- Tab 2: Lokasi & Foto --}}
        <button type="button"
           @click.prevent="scrollToSection('bagian-2', 2)"
           :class="{
               'bg-rendah-bg text-rendah-tx border border-rendah/50 ring-2 ring-rendah/60 font-bold': isLocked && activeSection === 2,
               'bg-rendah-bg text-rendah-tx border border-rendah/30': isLocked && activeSection !== 2,
               'bg-brand text-white font-bold shadow-md ring-2 ring-brand/30': !isLocked && activeSection === 2,
               'bg-rendah-bg text-rendah-tx border border-rendah/30 hover:bg-rendah-bg/80': !isLocked && activeSection !== 2 && step2Complete,
               'text-muted hover:text-ink hover:bg-paper': !isLocked && activeSection !== 2 && !step2Complete
           }"
           class="flex-1 flex items-center justify-center gap-1.5 py-2 px-2 sm:px-3 rounded-lg transition-all text-center">
            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold"
                  :class="{
                      'bg-rendah text-white': isLocked || (!isLocked && step2Complete && activeSection !== 2),
                      'bg-white text-brand': !isLocked && activeSection === 2,
                      'bg-paper border border-line text-muted': !isLocked && activeSection !== 2 && !step2Complete
                  }">
                <template x-if="isLocked || (step2Complete && activeSection !== 2)"><span>✓</span></template>
                <template x-if="!isLocked && (!step2Complete || activeSection === 2)"><span>2</span></template>
            </span>
            <span class="hidden sm:inline">Lokasi &amp; Foto</span>
            <span class="sm:hidden">2. Spasial</span>
        </button>

        <div class="h-0.5 w-3 sm:w-6 bg-line shrink-0"></div>

        {{-- Tab 3: Kuesioner SVI --}}
        <button type="button"
           @click.prevent="scrollToSection('bagian-3', 3)"
           :class="{
               'bg-rendah-bg text-rendah-tx border border-rendah/50 ring-2 ring-rendah/60 font-bold': isLocked && activeSection === 3,
               'bg-rendah-bg text-rendah-tx border border-rendah/30': isLocked && activeSection !== 3,
               'bg-brand text-white font-bold shadow-md ring-2 ring-brand/30': !isLocked && activeSection === 3,
               'bg-rendah-bg text-rendah-tx border border-rendah/30 hover:bg-rendah-bg/80': !isLocked && activeSection !== 3 && step3Count === 9,
               'text-muted hover:text-ink hover:bg-paper': !isLocked && activeSection !== 3 && step3Count < 9
           }"
           class="flex-1 flex items-center justify-center gap-1.5 py-2 px-2 sm:px-3 rounded-lg transition-all text-center">
            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold"
                  :class="{
                      'bg-rendah text-white': isLocked || (!isLocked && step3Count === 9 && activeSection !== 3),
                      'bg-white text-brand': !isLocked && activeSection === 3,
                      'bg-paper border border-line text-muted': !isLocked && activeSection !== 3 && step3Count < 9
                  }">
                <template x-if="isLocked || (step3Count === 9 && activeSection !== 3)"><span>✓</span></template>
                <template x-if="!isLocked && (step3Count < 9 || activeSection === 3)"><span>3</span></template>
            </span>
            <span class="hidden sm:inline">Kuesioner SVI</span>
            <span class="sm:hidden">3. SVI</span>
            <template x-if="!isLocked">
                <span class="text-[10px] tabular-nums font-bold px-1.5 py-0.5 rounded transition-all"
                      :class="step3Count === 9 ? 'bg-emerald-500 text-white shadow-xs ring-1 ring-emerald-400' : (activeSection === 3 ? 'bg-white/25 text-white' : 'bg-line/60 text-ink')"
                      x-text="step3Count + '/9'"></span>
            </template>
        </button>
    </div>

    {{-- FORMULIR SURVEI --}}
    <form @submit.prevent="submitForm" class="space-y-8">
        @csrf

        {{-- ============================================================
             BAGIAN 1 — Identitas & Lokasi Administratif (PRD 10.4)
             ============================================================ --}}
        <section id="bagian-1" class="card space-y-5 scroll-mt-28">
            <div class="border-b border-line pb-3">
                <h3 class="text-section-title text-ink">① Identitas &amp; Lokasi Administratif</h3>
                <p class="text-help mt-0.5">Identitas kepala keluarga sesuai dokumen resmi kependudukan.</p>
            </div>

            <div class="space-y-4">
                {{-- Nama Kepala Keluarga --}}
                <div>
                    <label for="nama_kk" class="block text-label text-ink mb-1.5">
                        Nama kepala keluarga <span class="text-tinggi">*</span>
                    </label>
                    <input type="text"
                           id="nama_kk"
                           name="nama_kk"
                           x-model="namaKK"
                           @input="formDirty = true; updateProgress()"
                           :disabled="isLocked"
                           class="input-text input-text-lg"
                           placeholder=""
                           required>
                    <p class="text-help mt-1">Tulis sesuai kartu keluarga, tanpa gelar.</p>
                </div>

                {{-- Status Penduduk (Radio Pilihan Sesuai Screenshot 2) --}}
                <div>
                    <label class="block text-label text-ink mb-1.5">
                        Status Penduduk <span class="text-tinggi">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-3.5 transition-all select-none"
                               :class="statusPenduduk === 'Tetap' ? 'border-brand border-2 bg-brand/[0.06] shadow-sm' : 'border-line hover:bg-paper'">
                            <input type="radio" name="status_penduduk" value="Tetap" x-model="statusPenduduk" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand focus:ring-brand cursor-pointer">
                            <div>
                                <span class="block font-semibold text-ink text-sm">Tetap</span>
                                <span class="block text-muted text-xs">Warga berdomisili tetap di Kampung Sota</span>
                            </div>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-3.5 transition-all select-none"
                               :class="statusPenduduk === 'Tidak Tetap' ? 'border-brand border-2 bg-brand/[0.06] shadow-sm' : 'border-line hover:bg-paper'">
                            <input type="radio" name="status_penduduk" value="Tidak Tetap" x-model="statusPenduduk" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand focus:ring-brand cursor-pointer">
                            <div>
                                <span class="block font-semibold text-ink text-sm">Tidak Tetap</span>
                                <span class="block text-muted text-xs">Warga musiman / pelintas batas / pendatang</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Jalur / Wilayah Permukiman --}}
                <div>
                    <label for="jalur" class="block text-label text-ink mb-1.5">
                        Jalur / wilayah permukiman <span class="text-tinggi">*</span>
                    </label>
                    <select id="jalur"
                            name="jalur"
                            x-model="jalur"
                            @change="formDirty = true; updateProgress()"
                            :disabled="isLocked"
                            class="input-select"
                            required>
                        <option value="" disabled>-- Pilih Jalur Permukiman --</option>
                        <optgroup label="Wilayah Jalur A">
                            <option value="Jalur 1A">Jalur 1A</option>
                            <option value="Jalur 2A">Jalur 2A</option>
                            <option value="Jalur 3A">Jalur 3A</option>
                            <option value="Jalur 4A">Jalur 4A</option>
                            <option value="Jalur 5A">Jalur 5A</option>
                            <option value="Jalur 6A">Jalur 6A</option>
                        </optgroup>
                        <optgroup label="Wilayah Jalur B">
                            <option value="Jalur 1B">Jalur 1B</option>
                            <option value="Jalur 2B">Jalur 2B</option>
                            <option value="Jalur 3B">Jalur 3B</option>
                            <option value="Jalur 4B">Jalur 4B</option>
                            <option value="Jalur 5B">Jalur 5B</option>
                            <option value="Jalur 6B">Jalur 6B</option>
                            <option value="Jalur 7B">Jalur 7B</option>
                        </optgroup>
                        <optgroup label="Wilayah Poros Trans">
                            <option value="Poros Trans">Poros Trans</option>
                            <option value="Poros Sota PLBN">Poros Sota PLBN</option>
                        </optgroup>
                        <option value="Jalur A" class="hidden">Jalur A</option>
                        <option value="Jalur B" class="hidden">Jalur B</option>
                    </select>
                    <p class="text-help mt-1">Pilihan jalur di Kampung Sota: Jalur 1A–6A, Jalur 1B–7B, Poros Trans, atau Poros Sota PLBN.</p>
                </div>

                {{-- Alamat Detail (RT, RW, No. Rumah) --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div>
                        <label for="rt" class="block text-label text-ink mb-1">RT <span class="text-tinggi">*</span></label>
                        <select id="rt" name="rt" x-model="rt" @change="formDirty = true" :disabled="isLocked" class="input-select" required>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="RT {{ sprintf('%02d', $i) }}">RT {{ sprintf('%02d', $i) }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label for="rw" class="block text-label text-ink mb-1">RW <span class="text-tinggi">*</span></label>
                        <select id="rw" name="rw" x-model="rw" @change="formDirty = true" :disabled="isLocked" class="input-select" required>
                            <option value="RW 01">RW 01</option>
                            <option value="RW 02">RW 02</option>
                            <option value="RW 03">RW 03</option>
                        </select>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="no_rumah" class="block text-label text-ink mb-1">No. Rumah</label>
                        <input type="text"
                               id="no_rumah"
                               name="no_rumah"
                               x-model="noRumah"
                               @input="formDirty = true"
                               :disabled="isLocked"
                               class="input-text"
                               placeholder="">
                    </div>
                </div>

                {{-- Patokan Lokasi --}}
                <div>
                    <label for="patokan" class="block text-label text-ink mb-1.5">Patokan lokasi (opsional)</label>
                    <input type="text"
                           id="patokan"
                           name="patokan"
                           x-model="patokan"
                           @input="formDirty = true"
                           :disabled="isLocked"
                           class="input-text"
                           placeholder="">
                </div>
            </div>
        </section>

        {{-- ============================================================
             BAGIAN 2 — Spasial & Dokumentasi (PRD 10.4)
             ============================================================ --}}
        <section id="bagian-2" class="card space-y-5 scroll-mt-28">
            <div class="border-b border-line pb-3">
                <h3 class="text-section-title text-ink">② Spasial &amp; Dokumentasi</h3>
                <p class="text-help mt-0.5">Penetapan koordinat GPS rumah dan dokumentasi foto bangunan fisik.</p>
            </div>

            <div class="space-y-4">
                {{-- Tombol Ambil GPS Utama --}}
                <div>
                    <label class="block text-label text-ink mb-2">
                        Titik koordinat rumah <span class="text-tinggi">*</span>
                    </label>

                    <button type="button"
                            @click="panggilGPS"
                            :disabled="isLocked || gpsState === 'loading'"
                            :class="isLocked ? 'opacity-60 cursor-not-allowed bg-paper border border-line !text-muted shadow-none' : 'btn-primary shadow-sm'"
                            class="w-full !min-h-[52px] !text-base flex items-center justify-center gap-2 rounded-lg">
                        <template x-if="isLocked">
                            <span class="flex items-center gap-2 font-bold text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <span>Titik Koordinat GPS Terverifikasi</span>
                            </span>
                        </template>
                        <template x-if="!isLocked && gpsState !== 'loading'">
                            <span class="flex items-center gap-2 font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
                                <span x-text="lat && lng ? 'Ambil Ulang Koordinat (GPS)' : 'Dapatkan Titik Koordinat (GPS)'"></span>
                            </span>
                        </template>
                        <template x-if="!isLocked && gpsState === 'loading'">
                            <span class="flex items-center gap-2 font-bold">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Mengambil lokasi…</span>
                            </span>
                        </template>
                    </button>
                    <p class="text-help mt-1.5 text-center">Berdirilah di depan rumah responden sebelum menekan tombol.</p>

                    {{-- Feedback Pesan GPS Error / Denied --}}
                    <template x-if="gpsState === 'denied'">
                        <div class="mt-2.5 p-3 rounded-lg bg-tinggi-bg border border-tinggi/30 text-xs text-tinggi-tx flex items-start justify-between gap-2">
                            <span>Akses lokasi ditolak. Aktifkan izin lokasi di pengaturan peramban, lalu coba lagi.</span>
                            <button type="button" @click="manualCoords = !manualCoords" class="underline font-bold whitespace-nowrap">
                                Isi koordinat manual
                            </button>
                        </div>
                    </template>

                    <template x-if="gpsState === 'error'">
                        <div class="mt-2.5 p-3 rounded-lg bg-tinggi-bg border border-tinggi/30 text-xs text-tinggi-tx flex items-center justify-between gap-2">
                            <span x-text="gpsMessage"></span>
                            <button type="button" @click="panggilGPS" class="underline font-bold whitespace-nowrap">Coba lagi</button>
                        </div>
                    </template>

                    {{-- Input Lat / Long dengan Pilihan Koreksi Manual --}}
                    <div class="space-y-2 mt-3">
                        <div class="flex items-center justify-end">
                            <button type="button"
                                    @click="manualCoords = !manualCoords"
                                    x-show="!isLocked"
                                    class="text-xs font-bold text-brand hover:underline flex items-center gap-1 transition-colors">
                                <span x-text="manualCoords ? '✓ Selesai Koreksi Manual' : '✏️ Ketik / Koreksi Koordinat Manual'"></span>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="latitude" class="block text-xs font-semibold text-muted mb-1">Latitude</label>
                                <input type="text"
                                       id="latitude"
                                       name="latitude"
                                       x-model="lat"
                                       @input="handleCoordInput()"
                                       :readonly="!manualCoords || isLocked"
                                       class="input-text tabular-nums text-sm font-medium"
                                       :class="manualCoords ? 'border-brand ring-1 ring-brand/40 bg-white' : ''"
                                       placeholder=""
                                       required>
                            </div>

                            <div>
                                <label for="longitude" class="block text-xs font-semibold text-muted mb-1">Longitude</label>
                                <input type="text"
                                       id="longitude"
                                       name="longitude"
                                       x-model="lng"
                                       @input="handleCoordInput()"
                                       :readonly="!manualCoords || isLocked"
                                       class="input-text tabular-nums text-sm font-medium"
                                       :class="manualCoords ? 'border-brand ring-1 ring-brand/40 bg-white' : ''"
                                       placeholder=""
                                       required>
                            </div>
                        </div>
                    </div>

                    <p id="gps-accuracy-info" class="text-xs text-muted mt-1.5 font-medium tabular-nums" x-text="accuracyText"></p>
                </div>

                {{-- Peta Pratinjau (Leaflet) Terisolasi Agar Bebas Digeser / Diklik Tanpa Efek Magnet --}}
                <div id="map-preview-container" :class="lat && lng ? '' : 'hidden'" class="space-y-2 pt-2 relative z-0 isolate">
                    <div id="map-preview" class="h-[270px] w-full rounded-lg border border-line bg-paper overflow-hidden shadow-inner relative z-0 isolate cursor-crosshair" style="isolation: isolate; z-index: 0; overflow: hidden;"></div>

                    {{-- Peringatan Akurasi Rendah (> 50 meter) Sesuai PRD Bagian 10.4 (Dipindahkan ke Bawah Peta) --}}
                    <div id="gps-accuracy-warning"
                         :class="(accuracy && accuracy > 50) ? '' : 'hidden'"
                         class="p-3 rounded-lg bg-amber-50 border-2 border-amber-300 text-amber-900 text-xs flex items-start gap-2.5 shadow-xs transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <div class="space-y-0.5">
                            <p id="gps-accuracy-warning-text" class="font-bold text-amber-900 leading-snug"
                               x-text="'Akurasi ±' + accuracy + ' meter (estimasi jaringan Wi-Fi/IP). Geser penanda pin pada peta atau klik \'Ketik / Koreksi Koordinat Manual\' untuk memasukkan titik presisi.'">
                                Akurasi ±50 meter. Tunggu beberapa detik lalu ambil ulang agar titik lebih tepat.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Unggah Foto Rumah --}}
                <div class="pt-4 border-t border-line space-y-3">
                    <label class="block text-label text-ink mb-1">
                        Foto rumah tampak depan <span class="text-tinggi">*</span>
                    </label>

                    {{-- Tombol Ambil Foto Kamera & Pilih Galeri --}}
                    <div class="flex flex-wrap gap-3" x-show="!isLocked">
                        <label class="btn-primary !min-h-[44px] !py-2 !px-4 !text-xs cursor-pointer shadow-sm flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            <span>📷 Ambil Foto Kamera</span>
                            <input type="file" accept="image/*" capture="environment" @change="handleFileChange" :disabled="isLocked" class="sr-only">
                        </label>

                        <label class="btn-secondary !min-h-[44px] !py-2 !px-4 !text-xs cursor-pointer shadow-sm flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span>📁 Pilih Dari Galeri</span>
                            <input type="file" accept="image/*" @change="handleFileChange" :disabled="isLocked" class="sr-only">
                        </label>
                    </div>

                    <p class="text-help text-xs">Tampak depan rumah, seluruh bangunan terlihat. JPG/PNG, maksimal 5 MB.</p>

                    <template x-if="fotoError">
                        <div class="p-2.5 rounded bg-tinggi-bg border border-tinggi/30 text-xs text-tinggi-tx" x-text="fotoError"></div>
                    </template>

                    {{-- Pratinjau Foto 4:3 --}}
                    <template x-if="fotoPreview">
                        <div class="relative max-w-sm rounded-lg border border-line overflow-hidden shadow-sm bg-paper flex items-center gap-3 p-2">
                            <div class="w-24 h-18 aspect-[4/3] rounded overflow-hidden bg-surface flex-shrink-0 border border-line">
                                <img :src="fotoPreview" alt="Pratinjau Foto Rumah" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0 text-xs">
                                <p class="font-bold text-ink truncate" x-text="fotoNama"></p>
                                <p class="text-muted text-[11px] mt-0.5" x-text="fotoUkuran"></p>
                            </div>
                            <button type="button"
                                    @click="hapusFoto"
                                    x-show="!isLocked"
                                    :disabled="isLocked"
                                    class="h-8 w-8 rounded-full text-muted hover:text-tinggi hover:bg-tinggi-bg flex items-center justify-center transition-colors flex-shrink-0"
                                    aria-label="Hapus foto">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        {{-- ============================================================
             BAGIAN 3 — Kuesioner SVI (9 Variabel) — PRD 10.4
             ============================================================ --}}
        <section id="bagian-3" class="card space-y-6 scroll-mt-28">
            <div class="border-b border-line pb-3 flex items-start sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-section-title text-ink">③ Kuesioner SVI (9 Variabel)</h3>
                    <p class="text-help mt-0.5">Pilih kategori utama melalui dropdown atau ketik jenis spesifik pada kolom "Lainnya".</p>
                </div>
                <span class="text-xs font-bold px-3 py-1 rounded-full tabular-nums border shadow-xs whitespace-nowrap shrink-0 transition-all"
                      :class="step3Count === 9 ? 'bg-emerald-500/15 text-emerald-700 border-emerald-500/30 font-bold' : 'text-brand bg-brand/[0.08] border-brand/20'"
                      x-text="step3Count + ' dari 9 terisi'"></span>
            </div>

            <div class="space-y-4">
                {{-- 1. Pendapatan Bulanan --}}
                <div class="p-4 sm:p-5 rounded-xl border border-line bg-surface hover:border-brand/40 transition-all space-y-3 shadow-xs">
                    <div class="flex items-center justify-between gap-3 border-b border-line pb-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand/10 text-brand border border-brand/20 shrink-0">
                                {{-- Lucide Icon: banknote --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
                            </span>
                            <h4 class="text-xs sm:text-sm font-bold text-ink">
                                1. Rata-Rata Total Pendapatan Keluarga Per Bulan <span class="text-tinggi">*</span>
                            </h4>
                        </div>
                        <span class="text-[11px] font-semibold text-muted bg-paper px-2.5 py-0.5 rounded-md border border-line shrink-0">1/9</span>
                    </div>

                    <div class="pt-1">
                        <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center">
                            Pilihan Rentang Pendapatan
                        </label>
                        <select x-model="q1"
                                @change="formDirty = true; updateProgress()"
                                :disabled="isLocked"
                                class="input-select w-full !h-[44px] text-xs sm:text-sm font-normal"
                                required>
                            <option value="" disabled>-- Pilih Rentang Pendapatan --</option>
                            <option value="1|Rp2.500.000 ke atas per bulan">Rp2.500.000 ke atas per bulan</option>
                            <option value="2|Rp1.000.000 – Rp2.499.999 per bulan">Rp1.000.000 – Rp2.499.999 per bulan</option>
                            <option value="3|Kurang dari Rp1.000.000 per bulan">Kurang dari Rp1.000.000 per bulan</option>
                        </select>
                    </div>
                </div>

                {{-- 2. Pekerjaan Utama --}}
                <div class="p-4 sm:p-5 rounded-xl border border-line bg-surface hover:border-brand/40 transition-all space-y-3 shadow-xs">
                    <div class="flex items-center justify-between gap-3 border-b border-line pb-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand/10 text-brand border border-brand/20 shrink-0">
                                {{-- Lucide Icon: briefcase --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>
                            </span>
                            <h4 class="text-xs sm:text-sm font-bold text-ink">
                                2. Pekerjaan / Mata Pencaharian Utama Kepala Keluarga <span class="text-tinggi">*</span>
                            </h4>
                        </div>
                        <span class="text-[11px] font-semibold text-muted bg-paper px-2.5 py-0.5 rounded-md border border-line shrink-0">2/9</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center">
                                Pilihan Pekerjaan Utama
                            </label>
                            <select x-model="q2"
                                    @change="formDirty = true; updateProgress()"
                                    :disabled="isLocked"
                                    class="input-select w-full !h-[44px] text-xs sm:text-sm font-normal"
                                    required>
                                <option value="" disabled>-- Pilih Pekerjaan Utama --</option>
                                <optgroup label="Sektor Formal / Bergaji Tetap">
                                    <option value="1|PNS / TNI / POLRI">PNS / TNI / POLRI</option>
                                    <option value="1|Guru PNS">Guru PNS</option>
                                    <option value="1|Pegawai Bergaji Tetap">Pegawai Bergaji Tetap</option>
                                    <option value="1|Karyawan Tetap">Karyawan Tetap</option>
                                    <option value="1|Wiraswasta Mandiri">Wiraswasta Mandiri</option>
                                </optgroup>
                                <optgroup label="Sektor Informal & Pertanian">
                                    <option value="2|Petani">Petani</option>
                                    <option value="2|Peternak">Peternak</option>
                                    <option value="2|Pedagang Kecil">Pedagang Kecil</option>
                                    <option value="2|Pegawai Honorer">Pegawai Honorer</option>
                                    <option value="2|Pegawai Kontrak">Pegawai Kontrak</option>
                                </optgroup>
                                <optgroup label="Sektor Harian / Rentan">
                                    <option value="3|Buruh Harian Lepas">Buruh Harian Lepas</option>
                                    <option value="3|Pekerja Serabutan">Pekerja Serabutan</option>
                                    <option value="3|Ibu Rumah Tangga">Ibu Rumah Tangga</option>
                                    <option value="3|Tidak Bekerja">Tidak Bekerja</option>
                                </optgroup>
                                <optgroup label="Lainnya">
                                    <option value="lainnya|Lainnya">✍️ Lainnya... (Ketik Manual di Samping)</option>
                                </optgroup>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center justify-between">
                                <span>Lainnya (Ketik jika tidak ada di opsi)</span>
                                <template x-if="q2.startsWith('lainnya')">
                                    <span class="text-[10px] leading-none text-tinggi font-bold bg-tinggi-bg px-1.5 py-0.5 rounded border border-tinggi/30">Wajib Diisi</span>
                                </template>
                            </label>
                            <input type="text"
                                   x-model="q2_other"
                                   @input="formDirty = true; autoDetectScore('q2'); updateProgress()"
                                   :disabled="isLocked || !q2.startsWith('lainnya')"
                                   :required="q2.startsWith('lainnya')"
                                   class="input-text w-full !h-[44px] text-xs sm:text-sm font-normal transition-all"
                                   :class="q2.startsWith('lainnya') ? 'border-brand bg-white ring-2 ring-brand/20 shadow-xs' : 'bg-paper/70 text-muted cursor-not-allowed opacity-80'"
                                   :placeholder="q2.startsWith('lainnya') ? '' : 'Otomatis aktif jika memilih \'Lainnya\''">

                            {{-- Normalisasi Skor Berdasarkan Tabel 3.2 --}}
                            <div x-show="q2 && q2.startsWith('lainnya')" class="mt-2 p-2.5 rounded-lg bg-paper/80 border border-line flex flex-wrap items-center justify-between gap-2 text-xs transition-all">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] font-normal text-muted">Pendekatan Skor (Tabel 3.2):</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-mono font-bold"
                                          :class="q2_skor === '1' ? 'bg-rendah/15 text-rendah border border-rendah/30' : (q2_skor === '2' ? 'bg-sedang/15 text-sedang border border-sedang/30' : 'bg-tinggi/15 text-tinggi border border-tinggi/30')"
                                          x-text="'Skor ' + q2_skor"></span>
                                </div>
                                <div class="inline-flex gap-1">
                                    <button type="button" @click="q2_skor = '1'" :class="q2_skor === '1' ? 'bg-rendah text-white font-bold ring-1 ring-rendah shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 1</button>
                                    <button type="button" @click="q2_skor = '2'" :class="q2_skor === '2' ? 'bg-sedang text-white font-bold ring-1 ring-sedang shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 2</button>
                                    <button type="button" @click="q2_skor = '3'" :class="q2_skor === '3' ? 'bg-tinggi text-white font-bold ring-1 ring-tinggi shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 3</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Pendidikan Tertinggi --}}
                <div class="p-4 sm:p-5 rounded-xl border border-line bg-surface hover:border-brand/40 transition-all space-y-3 shadow-xs">
                    <div class="flex items-center justify-between gap-3 border-b border-line pb-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand/10 text-brand border border-brand/20 shrink-0">
                                {{-- Lucide Icon: graduation-cap --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/><path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/></svg>
                            </span>
                            <h4 class="text-xs sm:text-sm font-bold text-ink">
                                3. Jenjang Pendidikan Formal Tertinggi dalam Rumah Tangga <span class="text-tinggi">*</span>
                            </h4>
                        </div>
                        <span class="text-[11px] font-semibold text-muted bg-paper px-2.5 py-0.5 rounded-md border border-line shrink-0">3/9</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center">
                                Pilihan Jenjang Pendidikan
                            </label>
                            <select x-model="q3"
                                    @change="formDirty = true; updateProgress()"
                                    :disabled="isLocked"
                                    class="input-select w-full !h-[44px] text-xs sm:text-sm font-normal"
                                    required>
                                <option value="" disabled>-- Pilih Jenjang Pendidikan --</option>
                                <option value="1|Perguruan Tinggi (Diploma/S1/S2)">Perguruan Tinggi (Diploma/S1/S2)</option>
                                <option value="2|Tamat SMA / Sederajat">Tamat SMA / Sederajat</option>
                                <option value="3|Tamat SMP / Sederajat">Tamat SMP / Sederajat</option>
                                <option value="3|Tamat SD / Sederajat">Tamat SD / Sederajat</option>
                                <option value="3|Tidak Tamat Sekolah">Tidak Tamat Sekolah</option>
                                <option value="lainnya|Lainnya">✍️ Lainnya... (Ketik Manual di Samping)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center justify-between">
                                <span>Lainnya (Ketik jenjang jika ada catatan khusus)</span>
                                <template x-if="q3.startsWith('lainnya')">
                                    <span class="text-[10px] leading-none text-tinggi font-bold bg-tinggi-bg px-1.5 py-0.5 rounded border border-tinggi/30">Wajib Diisi</span>
                                </template>
                            </label>
                            <input type="text"
                                   x-model="q3_other"
                                   @input="formDirty = true; autoDetectScore('q3'); updateProgress()"
                                   :disabled="isLocked || !q3.startsWith('lainnya')"
                                   :required="q3.startsWith('lainnya')"
                                   class="input-text w-full !h-[44px] text-xs sm:text-sm font-normal transition-all"
                                   :class="q3.startsWith('lainnya') ? 'border-brand bg-white ring-2 ring-brand/20 shadow-xs' : 'bg-paper/70 text-muted cursor-not-allowed opacity-80'"
                                   :placeholder="q3.startsWith('lainnya') ? '' : 'Otomatis aktif jika memilih \'Lainnya\''">

                            {{-- Normalisasi Skor Berdasarkan Tabel 3.2 --}}
                            <div x-show="q3 && q3.startsWith('lainnya')" class="mt-2 p-2.5 rounded-lg bg-paper/80 border border-line flex flex-wrap items-center justify-between gap-2 text-xs transition-all">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] font-normal text-muted">Pendekatan Skor (Tabel 3.2):</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-mono font-bold"
                                          :class="q3_skor === '1' ? 'bg-rendah/15 text-rendah border border-rendah/30' : (q3_skor === '2' ? 'bg-sedang/15 text-sedang border border-sedang/30' : 'bg-tinggi/15 text-tinggi border border-tinggi/30')"
                                          x-text="'Skor ' + q3_skor"></span>
                                </div>
                                <div class="inline-flex gap-1">
                                    <button type="button" @click="q3_skor = '1'" :class="q3_skor === '1' ? 'bg-rendah text-white font-bold ring-1 ring-rendah shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 1</button>
                                    <button type="button" @click="q3_skor = '2'" :class="q3_skor === '2' ? 'bg-sedang text-white font-bold ring-1 ring-sedang shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 2</button>
                                    <button type="button" @click="q3_skor = '3'" :class="q3_skor === '3' ? 'bg-tinggi text-white font-bold ring-1 ring-tinggi shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 3</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. Jumlah Anggota Keluarga --}}
                <div class="p-4 sm:p-5 rounded-xl border border-line bg-surface hover:border-brand/40 transition-all space-y-3 shadow-xs">
                    <div class="flex items-center justify-between gap-3 border-b border-line pb-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand/10 text-brand border border-brand/20 shrink-0">
                                {{-- Lucide Icon: users --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </span>
                            <h4 class="text-xs sm:text-sm font-bold text-ink">
                                4. Jumlah Jiwa / Anggota Keluarga yang Tinggal Serumah <span class="text-tinggi">*</span>
                            </h4>
                        </div>
                        <span class="text-[11px] font-semibold text-muted bg-paper px-2.5 py-0.5 rounded-md border border-line shrink-0">4/9</span>
                    </div>

                    <div class="pt-1">
                        <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center">
                            Pilihan Jumlah Anggota Keluarga
                        </label>
                        <select x-model="q4"
                                @change="formDirty = true; updateProgress()"
                                :disabled="isLocked"
                                class="input-select w-full !h-[44px] text-xs sm:text-sm font-normal"
                                required>
                            <option value="" disabled>-- Pilih Jumlah Anggota Keluarga --</option>
                            <option value="1|1 – 2 orang">1 – 2 orang</option>
                            <option value="2|3 – 4 orang">3 – 4 orang</option>
                            <option value="3|> 4 orang">&gt; 4 orang</option>
                        </select>
                    </div>
                </div>

                {{-- 5. Kondisi Fisik Bangunan (Dinding & Lantai) --}}
                <div class="p-4 sm:p-5 rounded-xl border border-line bg-surface hover:border-brand/40 transition-all space-y-4 shadow-xs">
                    <div class="flex items-center justify-between gap-3 border-b border-line pb-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand/10 text-brand border border-brand/20 shrink-0">
                                {{-- Lucide Icon: home --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            </span>
                            <h4 class="text-xs sm:text-sm font-bold text-ink">
                                5. Kondisi Fisik Bangunan (Material Dinding &amp; Lantai) <span class="text-tinggi">*</span>
                            </h4>
                        </div>
                        <span class="text-[11px] font-semibold text-muted bg-paper px-2.5 py-0.5 rounded-md border border-line shrink-0">5/9</span>
                    </div>

                    {{-- 5a. Dinding --}}
                    <div class="space-y-2 pt-1">
                        <div class="text-xs font-normal text-ink">5a. Material Dinding Utama</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center">
                                    Pilihan Material Dinding
                                </label>
                                <select x-model="q5a"
                                        @change="formDirty = true; updateProgress()"
                                        :disabled="isLocked"
                                        class="input-select w-full !h-[44px] text-xs sm:text-sm font-normal"
                                        required>
                                    <option value="" disabled>-- Pilih Material Dinding --</option>
                                    <option value="1|Tembok">Tembok</option>
                                    <option value="2|Kayu dan Tembok (Campuran)">Kayu dan Tembok (Campuran)</option>
                                    <option value="3|Kayu">Kayu</option>
                                    <option value="lainnya|Lainnya">✍️ Lainnya... (Ketik di Samping)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center justify-between">
                                    <span>Lainnya (Ketik jika tidak ada di opsi)</span>
                                    <template x-if="q5a.startsWith('lainnya')">
                                        <span class="text-[10px] leading-none text-tinggi font-bold bg-tinggi-bg px-1.5 py-0.5 rounded border border-tinggi/30">Wajib Diisi</span>
                                    </template>
                                </label>
                                <input type="text"
                                       x-model="q5a_other"
                                       @input="formDirty = true; autoDetectScore('q5a'); updateProgress()"
                                       :disabled="isLocked || !q5a.startsWith('lainnya')"
                                       :required="q5a.startsWith('lainnya')"
                                       class="input-text w-full !h-[44px] text-xs sm:text-sm font-normal transition-all"
                                       :class="q5a.startsWith('lainnya') ? 'border-brand bg-white ring-2 ring-brand/20 shadow-xs' : 'bg-paper/70 text-muted cursor-not-allowed opacity-80'"
                                       :placeholder="q5a.startsWith('lainnya') ? '' : 'Otomatis aktif jika memilih \'Lainnya\''">

                                {{-- Normalisasi Skor Berdasarkan Tabel 3.2 --}}
                                <div x-show="q5a && q5a.startsWith('lainnya')" class="mt-2 p-2.5 rounded-lg bg-paper/80 border border-line flex flex-wrap items-center justify-between gap-2 text-xs transition-all">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[11px] font-normal text-muted">Pendekatan Skor (Tabel 3.2):</span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-mono font-bold"
                                              :class="q5a_skor === '1' ? 'bg-rendah/15 text-rendah border border-rendah/30' : (q5a_skor === '2' ? 'bg-sedang/15 text-sedang border border-sedang/30' : 'bg-tinggi/15 text-tinggi border border-tinggi/30')"
                                              x-text="'Skor ' + q5a_skor"></span>
                                    </div>
                                    <div class="inline-flex gap-1">
                                        <button type="button" @click="q5a_skor = '1'" :class="q5a_skor === '1' ? 'bg-rendah text-white font-bold ring-1 ring-rendah shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 1</button>
                                        <button type="button" @click="q5a_skor = '2'" :class="q5a_skor === '2' ? 'bg-sedang text-white font-bold ring-1 ring-sedang shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 2</button>
                                        <button type="button" @click="q5a_skor = '3'" :class="q5a_skor === '3' ? 'bg-tinggi text-white font-bold ring-1 ring-tinggi shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 3</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5b. Lantai --}}
                    <div class="space-y-2 pt-2 border-t border-line/60">
                        <div class="text-xs font-normal text-ink">5b. Material Lantai Utama</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center">
                                    Pilihan Material Lantai
                                </label>
                                <select x-model="q5b"
                                        @change="formDirty = true; updateProgress()"
                                        :disabled="isLocked"
                                        class="input-select w-full !h-[44px] text-xs sm:text-sm font-normal"
                                        required>
                                    <option value="" disabled>-- Pilih Material Lantai --</option>
                                    <option value="1|Keramik / Ubin">Keramik / Ubin</option>
                                    <option value="2|Semen">Semen</option>
                                    <option value="3|Papan / Kayu">Papan / Kayu</option>
                                    <option value="3|Tanah">Tanah</option>
                                    <option value="lainnya|Lainnya">✍️ Lainnya... (Ketik di Samping)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center justify-between">
                                    <span>Lainnya (Ketik jika tidak ada di opsi)</span>
                                    <template x-if="q5b.startsWith('lainnya')">
                                        <span class="text-[10px] leading-none text-tinggi font-bold bg-tinggi-bg px-1.5 py-0.5 rounded border border-tinggi/30">Wajib Diisi</span>
                                    </template>
                                </label>
                                <input type="text"
                                       x-model="q5b_other"
                                       @input="formDirty = true; autoDetectScore('q5b'); updateProgress()"
                                       :disabled="isLocked || !q5b.startsWith('lainnya')"
                                       :required="q5b.startsWith('lainnya')"
                                       class="input-text w-full !h-[44px] text-xs sm:text-sm font-normal transition-all"
                                       :class="q5b.startsWith('lainnya') ? 'border-brand bg-white ring-2 ring-brand/20 shadow-xs' : 'bg-paper/70 text-muted cursor-not-allowed opacity-80'"
                                       :placeholder="q5b.startsWith('lainnya') ? '' : 'Otomatis aktif jika memilih \'Lainnya\''">

                                {{-- Normalisasi Skor Berdasarkan Tabel 3.2 --}}
                                <div x-show="q5b && q5b.startsWith('lainnya')" class="mt-2 p-2.5 rounded-lg bg-paper/80 border border-line flex flex-wrap items-center justify-between gap-2 text-xs transition-all">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[11px] font-normal text-muted">Pendekatan Skor (Tabel 3.2):</span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-mono font-bold"
                                              :class="q5b_skor === '1' ? 'bg-rendah/15 text-rendah border border-rendah/30' : (q5b_skor === '2' ? 'bg-sedang/15 text-sedang border border-sedang/30' : 'bg-tinggi/15 text-tinggi border border-tinggi/30')"
                                              x-text="'Skor ' + q5b_skor"></span>
                                    </div>
                                    <div class="inline-flex gap-1">
                                        <button type="button" @click="q5b_skor = '1'" :class="q5b_skor === '1' ? 'bg-rendah text-white font-bold ring-1 ring-rendah shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 1</button>
                                        <button type="button" @click="q5b_skor = '2'" :class="q5b_skor === '2' ? 'bg-sedang text-white font-bold ring-1 ring-sedang shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 2</button>
                                        <button type="button" @click="q5b_skor = '3'" :class="q5b_skor === '3' ? 'bg-tinggi text-white font-bold ring-1 ring-tinggi shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 3</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 6. Fasilitas Sanitasi (MCK) --}}
                <div class="p-4 sm:p-5 rounded-xl border border-line bg-surface hover:border-brand/40 transition-all space-y-3 shadow-xs">
                    <div class="flex items-center justify-between gap-3 border-b border-line pb-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand/10 text-brand border border-brand/20 shrink-0">
                                {{-- Lucide Icon: sparkles --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                            </span>
                            <h4 class="text-xs sm:text-sm font-bold text-ink">
                                6. Fasilitas Sanitasi &amp; Akses Tempat Buang Air Besar (BAB) <span class="text-tinggi">*</span>
                            </h4>
                        </div>
                        <span class="text-[11px] font-semibold text-muted bg-paper px-2.5 py-0.5 rounded-md border border-line shrink-0">6/9</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center">
                                Pilihan Sanitasi / MCK
                            </label>
                            <select x-model="q6"
                                    @change="formDirty = true; updateProgress()"
                                    :disabled="isLocked"
                                    class="input-select w-full !h-[44px] text-xs sm:text-sm font-normal"
                                    required>
                                <option value="" disabled>-- Pilih Fasilitas Sanitasi --</option>
                                <option value="1|MCK Pribadi / Jamban Sehat">MCK Pribadi / Jamban Sehat</option>
                                <option value="2|MCK Umum / Jamban berbagi dengan tetangga">MCK Umum / Jamban berbagi dengan tetangga</option>
                                <option value="3|Tidak Punya Sanitasi / Tidak Punya MCK">Tidak Punya Sanitasi / Tidak Punya MCK</option>
                                <option value="lainnya|Lainnya">✍️ Lainnya... (Ketik di Samping)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center justify-between">
                                <span>Lainnya (Ketik jika ada kondisi sanitasi khusus)</span>
                                <template x-if="q6.startsWith('lainnya')">
                                    <span class="text-[10px] leading-none text-tinggi font-bold bg-tinggi-bg px-1.5 py-0.5 rounded border border-tinggi/30">Wajib Diisi</span>
                                </template>
                            </label>
                            <input type="text"
                                   x-model="q6_other"
                                   @input="formDirty = true; autoDetectScore('q6'); updateProgress()"
                                   :disabled="isLocked || !q6.startsWith('lainnya')"
                                   :required="q6.startsWith('lainnya')"
                                   class="input-text w-full !h-[44px] text-xs sm:text-sm font-normal transition-all"
                                   :class="q6.startsWith('lainnya') ? 'border-brand bg-white ring-2 ring-brand/20 shadow-xs' : 'bg-paper/70 text-muted cursor-not-allowed opacity-80'"
                                   :placeholder="q6.startsWith('lainnya') ? '' : 'Otomatis aktif jika memilih \'Lainnya\''">

                            {{-- Normalisasi Skor Berdasarkan Tabel 3.2 --}}
                            <div x-show="q6 && q6.startsWith('lainnya')" class="mt-2 p-2.5 rounded-lg bg-paper/80 border border-line flex flex-wrap items-center justify-between gap-2 text-xs transition-all">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] font-normal text-muted">Pendekatan Skor (Tabel 3.2):</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-mono font-bold"
                                          :class="q6_skor === '1' ? 'bg-rendah/15 text-rendah border border-rendah/30' : (q6_skor === '2' ? 'bg-sedang/15 text-sedang border border-sedang/30' : 'bg-tinggi/15 text-tinggi border border-tinggi/30')"
                                          x-text="'Skor ' + q6_skor"></span>
                                </div>
                                <div class="inline-flex gap-1">
                                    <button type="button" @click="q6_skor = '1'" :class="q6_skor === '1' ? 'bg-rendah text-white font-bold ring-1 ring-rendah shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 1</button>
                                    <button type="button" @click="q6_skor = '2'" :class="q6_skor === '2' ? 'bg-sedang text-white font-bold ring-1 ring-sedang shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 2</button>
                                    <button type="button" @click="q6_skor = '3'" :class="q6_skor === '3' ? 'bg-tinggi text-white font-bold ring-1 ring-tinggi shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 3</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 7. Sumber Air Bersih --}}
                <div class="p-4 sm:p-5 rounded-xl border border-line bg-surface hover:border-brand/40 transition-all space-y-3 shadow-xs">
                    <div class="flex items-center justify-between gap-3 border-b border-line pb-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand/10 text-brand border border-brand/20 shrink-0">
                                {{-- Lucide Icon: droplet --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>
                            </span>
                            <h4 class="text-xs sm:text-sm font-bold text-ink">
                                7. Sumber Air Bersih Utama untuk Minum, Memasak, &amp; Mandi <span class="text-tinggi">*</span>
                            </h4>
                        </div>
                        <span class="text-[11px] font-semibold text-muted bg-paper px-2.5 py-0.5 rounded-md border border-line shrink-0">7/9</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center">
                                Pilihan Sumber Air
                            </label>
                            <select x-model="q7"
                                    @change="formDirty = true; updateProgress()"
                                    :disabled="isLocked"
                                    class="input-select w-full !h-[44px] text-xs sm:text-sm font-normal"
                                    required>
                                <option value="" disabled>-- Pilih Sumber Air Utama --</option>
                                <option value="1|PDAM">PDAM</option>
                                <option value="2|Sumur Bor">Sumur Bor</option>
                                <option value="2|Air Galon">Air Galon</option>
                                <option value="3|Sumur Gali">Sumur Gali</option>
                                <option value="lainnya|Lainnya">✍️ Lainnya... (Ketik di Samping)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center justify-between">
                                <span>Lainnya (Ketik sumber air spesifik)</span>
                                <template x-if="q7.startsWith('lainnya')">
                                    <span class="text-[10px] leading-none text-tinggi font-bold bg-tinggi-bg px-1.5 py-0.5 rounded border border-tinggi/30">Wajib Diisi</span>
                                </template>
                            </label>
                            <input type="text"
                                   x-model="q7_other"
                                   @input="formDirty = true; autoDetectScore('q7'); updateProgress()"
                                   :disabled="isLocked || !q7.startsWith('lainnya')"
                                   :required="q7.startsWith('lainnya')"
                                   class="input-text w-full !h-[44px] text-xs sm:text-sm font-normal transition-all"
                                   :class="q7.startsWith('lainnya') ? 'border-brand bg-white ring-2 ring-brand/20 shadow-xs' : 'bg-paper/70 text-muted cursor-not-allowed opacity-80'"
                                   :placeholder="q7.startsWith('lainnya') ? '' : 'Otomatis aktif jika memilih \'Lainnya\''">

                            {{-- Normalisasi Skor Berdasarkan Tabel 3.2 --}}
                            <div x-show="q7 && q7.startsWith('lainnya')" class="mt-2 p-2.5 rounded-lg bg-paper/80 border border-line flex flex-wrap items-center justify-between gap-2 text-xs transition-all">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] font-normal text-muted">Pendekatan Skor (Tabel 3.2):</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-mono font-bold"
                                          :class="q7_skor === '1' ? 'bg-rendah/15 text-rendah border border-rendah/30' : (q7_skor === '2' ? 'bg-sedang/15 text-sedang border border-sedang/30' : 'bg-tinggi/15 text-tinggi border border-tinggi/30')"
                                          x-text="'Skor ' + q7_skor"></span>
                                </div>
                                <div class="inline-flex gap-1">
                                    <button type="button" @click="q7_skor = '1'" :class="q7_skor === '1' ? 'bg-rendah text-white font-bold ring-1 ring-rendah shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 1</button>
                                    <button type="button" @click="q7_skor = '2'" :class="q7_skor === '2' ? 'bg-sedang text-white font-bold ring-1 ring-sedang shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 2</button>
                                    <button type="button" @click="q7_skor = '3'" :class="q7_skor === '3' ? 'bg-tinggi text-white font-bold ring-1 ring-tinggi shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 3</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 8. Pendapatan Tambahan --}}
                <div class="p-4 sm:p-5 rounded-xl border border-line bg-surface hover:border-brand/40 transition-all space-y-3 shadow-xs">
                    <div class="flex items-center justify-between gap-3 border-b border-line pb-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand/10 text-brand border border-brand/20 shrink-0">
                                {{-- Lucide Icon: coins --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="m7 6 4 4"/><path d="m17 14 4 4"/></svg>
                            </span>
                            <h4 class="text-xs sm:text-sm font-bold text-ink">
                                8. Sumber Pendapatan / Pemasukan Tambahan Rumah Tangga <span class="text-tinggi">*</span>
                            </h4>
                        </div>
                        <span class="text-[11px] font-semibold text-muted bg-paper px-2.5 py-0.5 rounded-md border border-line shrink-0">8/9</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center">
                                Pilihan Pemasukan Tambahan
                            </label>
                            <select x-model="q8"
                                    @change="formDirty = true; updateProgress()"
                                    :disabled="isLocked"
                                    class="input-select w-full !h-[44px] text-xs sm:text-sm font-normal"
                                    required>
                                <option value="" disabled>-- Pilih Pendapatan Tambahan --</option>
                                <option value="1|Usaha kecil / pekerjaan sampingan aktif">Usaha kecil / pekerjaan sampingan aktif (warung, jual hasil kebun, peternakan, dll.)</option>
                                <option value="2|Hanya menerima bantuan pemerintah">Hanya menerima bantuan pemerintah (transfer sosial, bukan sumber penghidupan produktif)</option>
                                <option value="3|Tidak ada pendapatan tambahan">Tidak ada pendapatan tambahan</option>
                                <option value="lainnya|Lainnya">✍️ Lainnya... (Ketik di Samping)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center justify-between">
                                <span>Lainnya (Ketik jenis sampingan jika ada)</span>
                                <template x-if="q8.startsWith('lainnya')">
                                    <span class="text-[10px] leading-none text-tinggi font-bold bg-tinggi-bg px-1.5 py-0.5 rounded border border-tinggi/30">Wajib Diisi</span>
                                </template>
                            </label>
                            <input type="text"
                                   x-model="q8_other"
                                   @input="formDirty = true; autoDetectScore('q8'); updateProgress()"
                                   :disabled="isLocked || !q8.startsWith('lainnya')"
                                   :required="q8.startsWith('lainnya')"
                                   class="input-text w-full !h-[44px] text-xs sm:text-sm font-normal transition-all"
                                   :class="q8.startsWith('lainnya') ? 'border-brand bg-white ring-2 ring-brand/20 shadow-xs' : 'bg-paper/70 text-muted cursor-not-allowed opacity-80'"
                                   :placeholder="q8.startsWith('lainnya') ? '' : 'Otomatis aktif jika memilih \'Lainnya\''">

                            {{-- Normalisasi Skor Berdasarkan Tabel 3.2 --}}
                            <div x-show="q8 && q8.startsWith('lainnya')" class="mt-2 p-2.5 rounded-lg bg-paper/80 border border-line flex flex-wrap items-center justify-between gap-2 text-xs transition-all">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] font-normal text-muted">Pendekatan Skor (Tabel 3.2):</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-mono font-bold"
                                          :class="q8_skor === '1' ? 'bg-rendah/15 text-rendah border border-rendah/30' : (q8_skor === '2' ? 'bg-sedang/15 text-sedang border border-sedang/30' : 'bg-tinggi/15 text-tinggi border border-tinggi/30')"
                                          x-text="'Skor ' + q8_skor"></span>
                                </div>
                                <div class="inline-flex gap-1">
                                    <button type="button" @click="q8_skor = '1'" :class="q8_skor === '1' ? 'bg-rendah text-white font-bold ring-1 ring-rendah shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 1</button>
                                    <button type="button" @click="q8_skor = '2'" :class="q8_skor === '2' ? 'bg-sedang text-white font-bold ring-1 ring-sedang shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 2</button>
                                    <button type="button" @click="q8_skor = '3'" :class="q8_skor === '3' ? 'bg-tinggi text-white font-bold ring-1 ring-tinggi shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 3</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 9. Status Kepemilikan Lahan & Rumah --}}
                <div class="p-4 sm:p-5 rounded-xl border border-line bg-surface hover:border-brand/40 transition-all space-y-3 shadow-xs">
                    <div class="flex items-center justify-between gap-3 border-b border-line pb-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand/10 text-brand border border-brand/20 shrink-0">
                                {{-- Lucide Icon: file-text --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                            </span>
                            <h4 class="text-xs sm:text-sm font-bold text-ink">
                                9. Status Legalitas Kepemilikan Rumah &amp; Lahan yang Ditempati <span class="text-tinggi">*</span>
                            </h4>
                        </div>
                        <span class="text-[11px] font-semibold text-muted bg-paper px-2.5 py-0.5 rounded-md border border-line shrink-0">9/9</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center">
                                Pilihan Legalitas Lahan
                            </label>
                            <select x-model="q9"
                                    @change="formDirty = true; updateProgress()"
                                    :disabled="isLocked"
                                    class="input-select w-full !h-[44px] text-xs sm:text-sm font-normal"
                                    required>
                                <option value="" disabled>-- Pilih Status Kepemilikan --</option>
                                <option value="1|Milik sendiri">Milik Sendiri</option>
                                <option value="1|Warisan keluarga">Warisan Keluarga</option>
                                <option value="2|Pinjam pakai / Sewa / Garapan">Pinjam Pakai / Sewa / Garapan</option>
                                <option value="3|Tidak memiliki lahan">Tidak Memiliki Lahan (Menumpang)</option>
                                <option value="lainnya|Lainnya">✍️ Lainnya... (Ketik di Samping)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-normal text-muted mb-1.5 min-h-[22px] flex items-center justify-between">
                                <span>Lainnya (Ketik status legalitas lahan spesifik)</span>
                                <template x-if="q9.startsWith('lainnya')">
                                    <span class="text-[10px] leading-none text-tinggi font-bold bg-tinggi-bg px-1.5 py-0.5 rounded border border-tinggi/30">Wajib Diisi</span>
                                </template>
                            </label>
                            <input type="text"
                                   x-model="q9_other"
                                   @input="formDirty = true; autoDetectScore('q9'); updateProgress()"
                                   :disabled="isLocked || !q9.startsWith('lainnya')"
                                   :required="q9.startsWith('lainnya')"
                                   class="input-text w-full !h-[44px] text-xs sm:text-sm font-normal transition-all"
                                   :class="q9.startsWith('lainnya') ? 'border-brand bg-white ring-2 ring-brand/20 shadow-xs' : 'bg-paper/70 text-muted cursor-not-allowed opacity-80'"
                                   :placeholder="q9.startsWith('lainnya') ? '' : 'Otomatis aktif jika memilih \'Lainnya\''">

                            {{-- Normalisasi Skor Berdasarkan Tabel 3.2 --}}
                            <div x-show="q9 && q9.startsWith('lainnya')" class="mt-2 p-2.5 rounded-lg bg-paper/80 border border-line flex flex-wrap items-center justify-between gap-2 text-xs transition-all">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] font-normal text-muted">Pendekatan Skor (Tabel 3.2):</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-mono font-bold"
                                          :class="q9_skor === '1' ? 'bg-rendah/15 text-rendah border border-rendah/30' : (q9_skor === '2' ? 'bg-sedang/15 text-sedang border border-sedang/30' : 'bg-tinggi/15 text-tinggi border border-tinggi/30')"
                                          x-text="'Skor ' + q9_skor"></span>
                                </div>
                                <div class="inline-flex gap-1">
                                    <button type="button" @click="q9_skor = '1'" :class="q9_skor === '1' ? 'bg-rendah text-white font-bold ring-1 ring-rendah shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 1</button>
                                    <button type="button" @click="q9_skor = '2'" :class="q9_skor === '2' ? 'bg-sedang text-white font-bold ring-1 ring-sedang shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 2</button>
                                    <button type="button" @click="q9_skor = '3'" :class="q9_skor === '3' ? 'bg-tinggi text-white font-bold ring-1 ring-tinggi shadow-xs' : 'bg-surface text-ink hover:bg-paper'" class="px-2.5 py-0.5 rounded text-[11px] border border-line transition-all">Skor 3</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================
             BILAH LENGKET BAWAH (STICKY BOTTOM ACTION BAR) — PRD 10.4
             ============================================================ --}}
        <div class="fixed inset-x-0 bottom-0 z-50 bg-surface/98 backdrop-blur border-t border-line p-4 shadow-2xl">
            <div class="max-w-[720px] mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
                {{-- Status Kemajuan & Pesan Error / Status Terverifikasi --}}
                <div class="text-xs w-full sm:w-auto">
                    <template x-if="isLocked">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rendah-bg text-rendah-tx border border-rendah/30 font-bold text-xs shadow-xs">
                            <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-rendah text-white text-[10px] font-bold">✓</span>
                            <span>Data Telah Di Verifikasi</span>
                        </div>
                    </template>
                    <template x-if="!isLocked">
                        <div>
                            <template x-if="errorMessage">
                                <span class="text-tinggi-tx font-bold flex items-center gap-1.5" x-text="errorMessage"></span>
                            </template>
                            <template x-if="!errorMessage">
                                <span class="text-muted">
                                    <strong class="text-ink font-bold" x-text="step3Count + ' dari 9'"></strong> pertanyaan variabel SVI terisi
                                </span>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                    <template x-if="isLocked">
                        <a href="{{ $effectiveRole === 'admin' ? '/admin/data' : '/operator/data' }}"
                           class="btn-secondary !min-h-[42px] !px-5 text-center text-xs font-bold w-full sm:w-auto shadow-xs">
                            Kembali ke Daftar Data
                        </a>
                    </template>
                    <template x-if="!isLocked">
                        <div class="flex items-center gap-2.5 w-full sm:w-auto">
                            <a href="{{ $effectiveRole === 'admin' ? '/admin/data' : '/operator/data' }}"
                               @click.prevent="formDirty ? (modalKonfirmasiBatal = true) : (window.location.href = $el.href)"
                               class="btn-secondary !min-h-[44px] flex-1 sm:flex-initial text-center">
                                Batal
                            </a>
                            <button type="submit"
                                    :disabled="isSubmitting"
                                    class="btn-primary !min-h-[44px] flex-1 sm:flex-initial shadow-md font-bold">
                                <template x-if="!isSubmitting">
                                    <span>Simpan Data</span>
                                </template>
                                <template x-if="isSubmitting">
                                    <span class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        <span>Menyimpan…</span>
                                    </span>
                                </template>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </form>

    {{-- MODAL KONFIRMASI BATAL (PRD 10.4) --}}
    <div x-show="modalKonfirmasiBatal"
         x-transition
         class="fixed inset-0 z-50 bg-ink/50 flex items-center justify-center p-4"
         style="display: none;"
         x-cloak>
        <div @click.outside="modalKonfirmasiBatal = false"
             class="bg-surface rounded-xl shadow-2xl max-w-sm w-full p-6 space-y-4">
            <h3 class="text-base font-bold text-ink">Tinggalkan Halaman?</h3>
            <p class="text-xs text-muted leading-relaxed">
                Data survei yang sudah diisi belum disimpan dan akan hilang jika Anda keluar. Tetap ingin kembali?
            </p>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-line">
                <button type="button" @click="modalKonfirmasiBatal = false" class="btn-secondary !min-h-[36px] !text-xs font-semibold">
                    Lanjutkan Mengisi
                </button>
                <a href="{{ $effectiveRole === 'admin' ? '/admin/data' : '/operator/data' }}" class="btn-danger-solid !min-h-[36px] !text-xs font-semibold">
                    Ya, Keluar
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
