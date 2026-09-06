@extends('layouts.app', [
    'role' => $role ?? 'operator',
    'userName' => $userName ?? 'Maria K.'
])

@php
    $isEdit = isset($id);
    $pageTitle = $isEdit ? 'Ubah Data Warga' : 'Input Data Warga Baru';
    $statusVerifikasi = $data['status_verifikasi'] ?? 'menunggu_verifikasi';
    $isLocked = $isEdit && ($role ?? 'operator') === 'operator' && $statusVerifikasi === 'terverifikasi';
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
    </style>
@endpush

@section('content')
<div x-data="{
    // State Formulir & Progres
    isEdit: {{ $isEdit ? 'true' : 'false' }},
    isLocked: {{ $isLocked ? 'true' : 'false' }},
    isSubmitting: false,
    errorMessage: '',
    modalKonfirmasiBatal: false,
    formDirty: false,

    // Step Progress Status
    step1Complete: false,
    step2Complete: false,
    step3Count: 0,

    // Nilai Form
    namaKK: '{{ $data['nama_kk'] ?? '' }}',
    jalur: '{{ $data['jalur'] ?? 'Jalur B' }}',
    rt: '{{ $data['rt'] ?? 'RT 03' }}',
    rw: '{{ $data['rw'] ?? 'RW 01' }}',
    noRumah: '{{ $data['no_rumah'] ?? '' }}',
    patokan: '{{ $data['patokan'] ?? '' }}',

    // GPS State
    gpsState: '{{ isset($data['latitude']) ? 'success' : 'idle' }}', // idle | loading | success | error | denied
    gpsMessage: '',
    manualCoords: false,
    lat: '{{ $data['latitude'] ?? '' }}',
    lng: '{{ $data['longitude'] ?? '' }}',
    accuracyText: '{{ isset($data['latitude']) ? 'Akurasi ±6 meter · WGS 84' : '' }}',

    // Foto State
    fotoPreview: '{{ $data['foto_rumah'] ?? '' }}',
    fotoNama: '{{ isset($data['foto_rumah']) ? 'foto-rumah-terverifikasi.jpg' : '' }}',
    fotoUkuran: '{{ isset($data['foto_rumah']) ? '1,4 MB' : '' }}',
    fotoError: '',

    // 9 Variabel Kuesioner SVI
    q1: '{{ $data['pendapatan'] ?? '' }}',
    q2: '{{ $data['pekerjaan'] ?? '' }}',
    q3: '{{ $data['pendidikan'] ?? '' }}',
    q4: '{{ $data['jumlah_anggota'] ?? '' }}',
    q5a: '{{ $data['jenis_dinding'] ?? '' }}',
    q5b: '{{ $data['jenis_lantai'] ?? '' }}',
    q5c: '{{ $data['jenis_atap'] ?? 'seng' }}',
    q6: '{{ $data['fasilitas_sanitasi'] ?? '' }}',
    q7: '{{ $data['sumber_air'] ?? '' }}',
    q8: '{{ $data['pendapatan_tambahan'] ?? '' }}',
    q9: '{{ $data['kepemilikan_lahan'] ?? '' }}',

    init() {
        this.updateProgress();

        // Inisialisasi Peta Pratinjau jika mode edit dan ada koordinat
        if (this.lat && this.lng) {
            setTimeout(() => {
                if (window.sotaInitPreviewMap) {
                    window.sotaInitPreviewMap(parseFloat(this.lat), parseFloat(this.lng));
                }
            }, 300);
        }
    },

    updateProgress() {
        // Cek Kelengkapan Bagian 1
        this.step1Complete = (this.namaKK.trim().length > 0) && (this.jalur.length > 0);

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
        window.sotaCompressImage(file, 1600, 0.8)
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

        // Simulasi Penyimpanan Berhasil Sesuai PRD 10.4
        setTimeout(() => {
            this.isSubmitting = false;
            window.location.href = '{{ ($role ?? '') === 'admin' ? '/admin/data' : '/operator/data' }}';
        }, 1200);
    }
}" class="max-w-[720px] mx-auto pb-32 space-y-6">

    {{-- Tombol Navigasi Kembali --}}
    <div>
        <a href="{{ ($role ?? '') === 'admin' ? '/admin/data' : '/operator/data' }}"
           @click.prevent="formDirty ? (modalKonfirmasiBatal = true) : (window.location.href = $el.href)"
           class="inline-flex items-center gap-2 text-sm font-medium text-muted hover:text-ink transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Kembali ke Daftar Data
        </a>
    </div>

    {{-- Mode Ubah: Spanduk Peringatan Khusus (PRD 10.4 & 11.3) --}}
    @if($isEdit)
        @if($isLocked)
            <div class="rounded-lg bg-paper border border-line p-4 flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rendah flex-shrink-0 mt-0.5"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <div class="text-xs">
                    <p class="font-bold text-ink">Formulir Terkunci (Hanya Baca)</p>
                    <p class="text-muted mt-0.5">Data ini telah berstatus <strong>Terverifikasi</strong> oleh Admin dan tampil di peta publik. Operator tidak dapat melakukan perubahan. Hubungi Administrator bila perlu koreksi data.</p>
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
         INDIKATOR KEMAJUAN LENGKET (STICKY PROGRESS TABS) — PRD 10.4
         ============================================================ --}}
    <div class="sticky top-14 z-20 bg-surface/95 backdrop-blur-sm border border-line rounded-lg p-2 shadow-sm flex items-center justify-between text-xs font-semibold">
        <a href="#bagian-1"
           :class="step1Complete ? 'bg-rendah-bg text-rendah-tx border border-rendah/30' : 'bg-brand/10 text-brand'"
           class="flex items-center gap-1.5 py-1.5 px-3 rounded-md transition-colors">
            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-white text-[10px]"
                  :class="step1Complete ? 'bg-rendah' : 'bg-brand'">
                <template x-if="step1Complete"><span>✓</span></template>
                <template x-if="!step1Complete"><span>1</span></template>
            </span>
            <span class="hidden sm:inline">① Identitas</span>
            <span class="sm:hidden">Identitas</span>
        </a>

        <div class="h-0.5 flex-1 max-w-[40px] bg-line mx-1"></div>

        <a href="#bagian-2"
           :class="step2Complete ? 'bg-rendah-bg text-rendah-tx border border-rendah/30' : (step1Complete ? 'bg-brand/10 text-brand' : 'text-muted hover:text-ink')"
           class="flex items-center gap-1.5 py-1.5 px-3 rounded-md transition-colors">
            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-[10px]"
                  :class="step2Complete ? 'bg-rendah text-white' : 'bg-paper border border-line text-muted'">
                <template x-if="step2Complete"><span>✓</span></template>
                <template x-if="!step2Complete"><span>2</span></template>
            </span>
            <span class="hidden sm:inline">② Lokasi &amp; Foto</span>
            <span class="sm:hidden">Spasial</span>
        </a>

        <div class="h-0.5 flex-1 max-w-[40px] bg-line mx-1"></div>

        <a href="#bagian-3"
           :class="step3Count === 9 ? 'bg-rendah-bg text-rendah-tx border border-rendah/30' : (step2Complete ? 'bg-brand/10 text-brand' : 'text-muted hover:text-ink')"
           class="flex items-center gap-1.5 py-1.5 px-3 rounded-md transition-colors">
            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-[10px]"
                  :class="step3Count === 9 ? 'bg-rendah text-white' : 'bg-paper border border-line text-muted'">
                <template x-if="step3Count === 9"><span>✓</span></template>
                <template x-if="step3Count < 9"><span>3</span></template>
            </span>
            <span class="hidden sm:inline">③ Kuesioner SVI</span>
            <span class="sm:hidden">Kuesioner</span>
            <span class="text-[10px] tabular-nums font-bold px-1.5 py-0.5 rounded bg-white/70" x-text="step3Count + '/9'"></span>
        </a>
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
                           placeholder="Contoh: Yohanis Kaize"
                           required>
                    <p class="text-help mt-1">Tulis sesuai kartu keluarga, tanpa gelar.</p>
                </div>

                {{-- Jalur / Wilayah Permukiman --}}
                <div>
                    <label class="block text-label text-ink mb-2">
                        Jalur / wilayah permukiman <span class="text-tinggi">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="cursor-pointer border border-line rounded-lg p-3.5 text-center hover:bg-paper transition-all"
                               :class="jalur === 'Jalur A' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line'">
                            <input type="radio" name="jalur" value="Jalur A" x-model="jalur" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="sr-only">
                            <span class="block font-semibold text-ink text-sm">Jalur A</span>
                        </label>
                        <label class="cursor-pointer border border-line rounded-lg p-3.5 text-center hover:bg-paper transition-all"
                               :class="jalur === 'Jalur B' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line'">
                            <input type="radio" name="jalur" value="Jalur B" x-model="jalur" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="sr-only">
                            <span class="block font-semibold text-ink text-sm">Jalur B</span>
                        </label>
                        <label class="cursor-pointer border border-line rounded-lg p-3.5 text-center hover:bg-paper transition-all"
                               :class="jalur === 'Poros Trans' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line'">
                            <input type="radio" name="jalur" value="Poros Trans" x-model="jalur" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="sr-only">
                            <span class="block font-semibold text-ink text-sm">Poros Trans</span>
                        </label>
                    </div>
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
                               placeholder="No. 12">
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
                           placeholder="Contoh: dekat gereja, sebelah kios Pak Amir">
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
                            class="btn-primary w-full !min-h-[52px] !text-base shadow-sm flex items-center justify-center gap-2">
                        <template x-if="gpsState !== 'loading'">
                            <span class="flex items-center gap-2 font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
                                <span x-text="lat && lng ? '⌖ Ambil Ulang Koordinat (GPS)' : '⌖ Dapatkan Titik Koordinat (GPS)'"></span>
                            </span>
                        </template>
                        <template x-if="gpsState === 'loading'">
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

                    {{-- Input Lat / Long --}}
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <div>
                            <label for="latitude" class="block text-xs font-semibold text-muted mb-1">Latitude</label>
                            <input type="text"
                                   id="latitude"
                                   name="latitude"
                                   x-model="lat"
                                   :readonly="!manualCoords || isLocked"
                                   :disabled="!lat && !manualCoords"
                                   class="input-text tabular-nums text-sm font-medium"
                                   placeholder="-8.44271"
                                   required>
                        </div>

                        <div>
                            <label for="longitude" class="block text-xs font-semibold text-muted mb-1">Longitude</label>
                            <input type="text"
                                   id="longitude"
                                   name="longitude"
                                   x-model="lng"
                                   :readonly="!manualCoords || isLocked"
                                   :disabled="!lng && !manualCoords"
                                   class="input-text tabular-nums text-sm font-medium"
                                   placeholder="140.86935"
                                   required>
                        </div>
                    </div>

                    <p id="gps-accuracy-info" class="text-xs text-muted mt-1.5 font-medium tabular-nums" x-text="accuracyText"></p>
                </div>

                {{-- Peta Pratinjau 240px (Leaflet) --}}
                <div id="map-preview-container" :class="lat && lng ? '' : 'hidden'" class="space-y-1 pt-2">
                    <div id="map-preview" class="h-[240px] w-full rounded-lg border border-line bg-paper overflow-hidden shadow-inner"></div>
                    <p class="text-help text-xs text-center">Geser penanda pada peta bila posisinya meleset dari lokasi rumah.</p>
                </div>

                {{-- Unggah Foto Rumah --}}
                <div class="pt-4 border-t border-line space-y-3">
                    <label class="block text-label text-ink mb-1">
                        Foto rumah tampak depan <span class="text-tinggi">*</span>
                    </label>

                    {{-- Tombol Ambil Foto Kamera & Pilih Galeri --}}
                    <div class="flex flex-wrap gap-3">
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
            <div class="border-b border-line pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-section-title text-ink">③ Kuesioner SVI (9 Variabel)</h3>
                    <p class="text-help mt-0.5">Satu variabel = satu ketukan radio kartu. Tanpa angka skor penilaian.</p>
                </div>
                <span class="text-xs font-bold text-brand bg-brand/[0.08] px-2.5 py-1 rounded-full tabular-nums"
                      x-text="step3Count + ' dari 9 terisi'"></span>
            </div>

            <div class="space-y-6">
                {{-- 1. Pendapatan --}}
                <fieldset class="space-y-2">
                    <legend class="flex items-start justify-between gap-4 w-full">
                        <span class="text-body font-medium text-ink">
                            1. Berapa rata-rata total pendapatan keluarga dalam satu bulan? <span class="text-tinggi">*</span>
                        </span>
                        <span class="text-xs font-medium text-muted bg-paper px-2 py-0.5 rounded">1/9</span>
                    </legend>
                    <div class="space-y-2 pt-1">
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q1 === '1' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="pendapatan" value="1" x-model="q1" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Rp2.500.000 ke atas per bulan</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q1 === '2' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="pendapatan" value="2" x-model="q1" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Rp1.000.000 – Rp2.499.999 per bulan</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q1 === '3' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="pendapatan" value="3" x-model="q1" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Kurang dari Rp1.000.000 per bulan</span>
                        </label>
                    </div>
                </fieldset>

                {{-- 2. Pekerjaan --}}
                <fieldset class="space-y-2">
                    <legend class="flex items-start justify-between gap-4 w-full">
                        <span class="text-body font-medium text-ink">
                            2. Apa pekerjaan atau mata pencaharian utama kepala keluarga? <span class="text-tinggi">*</span>
                        </span>
                        <span class="text-xs font-medium text-muted bg-paper px-2 py-0.5 rounded">2/9</span>
                    </legend>
                    <div class="space-y-2 pt-1">
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q2 === '1' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="pekerjaan" value="1" x-model="q2" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">PNS / TNI / POLRI / Guru PNS / Pegawai bergaji tetap / Karyawan tetap / Wiraswasta mandiri</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q2 === '2' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="pekerjaan" value="2" x-model="q2" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Petani / Peternak / Pedagang kecil / Pegawai honorer / Pegawai kontrak</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q2 === '3' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="pekerjaan" value="3" x-model="q2" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Tidak bekerja / Ibu rumah tangga / Buruh harian lepas / Pekerja serabutan</span>
                        </label>
                    </div>
                </fieldset>

                {{-- 3. Pendidikan --}}
                <fieldset class="space-y-2">
                    <legend class="flex items-start justify-between gap-4 w-full">
                        <span class="text-body font-medium text-ink">
                            3. Apa jenjang pendidikan formal tertinggi di dalam rumah tangga ini? <span class="text-tinggi">*</span>
                        </span>
                        <span class="text-xs font-medium text-muted bg-paper px-2 py-0.5 rounded">3/9</span>
                    </legend>
                    <div class="space-y-2 pt-1">
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q3 === '1' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="pendidikan" value="1" x-model="q3" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Perguruan tinggi (Diploma / S1 / S2)</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q3 === '2' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="pendidikan" value="2" x-model="q3" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Tamat SMA / sederajat</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q3 === '3' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="pendidikan" value="3" x-model="q3" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">SD / SMP / tidak tamat sekolah</span>
                        </label>
                    </div>
                </fieldset>

                {{-- 4. Jumlah Anggota Keluarga --}}
                <fieldset class="space-y-2">
                    <legend class="flex items-start justify-between gap-4 w-full">
                        <span class="text-body font-medium text-ink">
                            4. Berapa jumlah orang yang tinggal dan menjadi tanggungan di rumah ini? <span class="text-tinggi">*</span>
                        </span>
                        <span class="text-xs font-medium text-muted bg-paper px-2 py-0.5 rounded">4/9</span>
                    </legend>
                    <div class="space-y-2 pt-1">
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q4 === '1' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="jumlah_anggota" value="1" x-model="q4" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">1 – 2 orang</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q4 === '2' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="jumlah_anggota" value="2" x-model="q4" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">3 – 4 orang</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q4 === '3' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="jumlah_anggota" value="3" x-model="q4" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Lebih dari 4 orang</span>
                        </label>
                    </div>
                </fieldset>

                {{-- 5. Kondisi Fisik Bangunan (5a Dinding & 5b Lantai berdampingan, 5c Atap data pendukung) --}}
                <div class="border border-line rounded-xl p-5 space-y-4 bg-paper/40">
                    <div class="flex items-start justify-between border-b border-line pb-2">
                        <div>
                            <h4 class="font-bold text-ink text-sm">5. Kondisi Fisik Bangunan</h4>
                            <p class="text-help text-xs">Variabel komposit material dinding dan lantai rumah responden.</p>
                        </div>
                        <span class="text-xs font-medium text-muted bg-paper px-2 py-0.5 rounded">5/9</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- 5a Dinding --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink">5a. Material Dinding <span class="text-tinggi">*</span></label>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border bg-surface cursor-pointer text-xs transition-all"
                                       :class="q5a === '1' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line'">
                                    <input type="radio" name="jenis_dinding" value="1" x-model="q5a" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="text-brand">
                                    <span>Tembok</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border bg-surface cursor-pointer text-xs transition-all"
                                       :class="q5a === '2' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line'">
                                    <input type="radio" name="jenis_dinding" value="2" x-model="q5a" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="text-brand">
                                    <span>Kayu dan tembok (campuran)</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border bg-surface cursor-pointer text-xs transition-all"
                                       :class="q5a === '3' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line'">
                                    <input type="radio" name="jenis_dinding" value="3" x-model="q5a" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="text-brand">
                                    <span>Kayu</span>
                                </label>
                            </div>
                        </div>

                        {{-- 5b Lantai --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-ink">5b. Material Lantai <span class="text-tinggi">*</span></label>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border bg-surface cursor-pointer text-xs transition-all"
                                       :class="q5b === '1' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line'">
                                    <input type="radio" name="jenis_lantai" value="1" x-model="q5b" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="text-brand">
                                    <span>Keramik / ubin</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border bg-surface cursor-pointer text-xs transition-all"
                                       :class="q5b === '2' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line'">
                                    <input type="radio" name="jenis_lantai" value="2" x-model="q5b" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="text-brand">
                                    <span>Semen</span>
                                </label>
                                <label class="flex items-center gap-2 p-2.5 rounded-lg border bg-surface cursor-pointer text-xs transition-all"
                                       :class="q5b === '3' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line'">
                                    <input type="radio" name="jenis_lantai" value="3" x-model="q5b" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="text-brand">
                                    <span>Tanah / papan / kayu</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- 5c Atap (Data pendukung) --}}
                    <div class="border-t border-line pt-3">
                        <label class="block text-xs font-semibold text-muted mb-1.5">
                            5c. Material Atap <span class="text-[11px] font-normal">(Data pendukung — tidak masuk perhitungan skor SVI)</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <label class="flex items-center gap-2 p-2 rounded border bg-surface cursor-pointer"
                                   :class="q5c === 'seng' ? 'border-brand font-semibold' : 'border-line'">
                                <input type="radio" name="jenis_atap" value="seng" x-model="q5c" :disabled="isLocked" class="text-brand">
                                <span>Seng / genteng</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded border bg-surface cursor-pointer"
                                   :class="q5c === 'seng_bekas' ? 'border-brand font-semibold' : 'border-line'">
                                <input type="radio" name="jenis_atap" value="seng_bekas" x-model="q5c" :disabled="isLocked" class="text-brand">
                                <span>Seng campuran</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded border bg-surface cursor-pointer"
                                   :class="q5c === 'daun' ? 'border-brand font-semibold' : 'border-line'">
                                <input type="radio" name="jenis_atap" value="daun" x-model="q5c" :disabled="isLocked" class="text-brand">
                                <span>Daun / terpal</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- 6. Fasilitas Sanitasi --}}
                <fieldset class="space-y-2">
                    <legend class="flex items-start justify-between gap-4 w-full">
                        <span class="text-body font-medium text-ink">
                            6. Di mana anggota keluarga biasanya buang air besar? <span class="text-tinggi">*</span>
                        </span>
                        <span class="text-xs font-medium text-muted bg-paper px-2 py-0.5 rounded">6/9</span>
                    </legend>
                    <div class="space-y-2 pt-1">
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q6 === '1' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="fasilitas_sanitasi" value="1" x-model="q6" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">MCK pribadi / jamban sehat</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q6 === '2' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="fasilitas_sanitasi" value="2" x-model="q6" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">MCK umum / jamban berbagi dengan tetangga</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q6 === '3' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="fasilitas_sanitasi" value="3" x-model="q6" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Tidak punya sanitasi / tidak punya MCK</span>
                        </label>
                    </div>
                </fieldset>

                {{-- 7. Sumber Air --}}
                <fieldset class="space-y-2">
                    <legend class="flex items-start justify-between gap-4 w-full">
                        <span class="text-body font-medium text-ink">
                            7. Dari mana sumber air utama untuk minum, memasak, dan mencuci? <span class="text-tinggi">*</span>
                        </span>
                        <span class="text-xs font-medium text-muted bg-paper px-2 py-0.5 rounded">7/9</span>
                    </legend>
                    <div class="space-y-2 pt-1">
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q7 === '1' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="sumber_air" value="1" x-model="q7" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">PDAM</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q7 === '2' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="sumber_air" value="2" x-model="q7" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Sumur bor / galon</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q7 === '3' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="sumber_air" value="3" x-model="q7" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Sumur gali</span>
                        </label>
                    </div>
                </fieldset>

                {{-- 8. Pendapatan Tambahan --}}
                <fieldset class="space-y-2">
                    <legend class="flex items-start justify-between gap-4 w-full">
                        <span class="text-body font-medium text-ink">
                            8. Apakah keluarga memiliki sumber pemasukan lain di luar pekerjaan utama? <span class="text-tinggi">*</span>
                        </span>
                        <span class="text-xs font-medium text-muted bg-paper px-2 py-0.5 rounded">8/9</span>
                    </legend>
                    <div class="space-y-2 pt-1">
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q8 === '1' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="pendapatan_tambahan" value="1" x-model="q8" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Usaha kecil atau pekerjaan sampingan aktif (warung, jual hasil kebun, peternakan)</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q8 === '2' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="pendapatan_tambahan" value="2" x-model="q8" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Hanya menerima bantuan pemerintah</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q8 === '3' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="pendapatan_tambahan" value="3" x-model="q8" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Tidak ada pendapatan tambahan</span>
                        </label>
                    </div>
                </fieldset>

                {{-- 9. Kepemilikan Lahan --}}
                <fieldset class="space-y-2">
                    <legend class="flex items-start justify-between gap-4 w-full">
                        <span class="text-body font-medium text-ink">
                            9. Apa status kepemilikan rumah dan lahan yang ditempati saat ini? <span class="text-tinggi">*</span>
                        </span>
                        <span class="text-xs font-medium text-muted bg-paper px-2 py-0.5 rounded">9/9</span>
                    </legend>
                    <div class="space-y-2 pt-1">
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q9 === '1' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="kepemilikan_lahan" value="1" x-model="q9" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Milik sendiri / warisan keluarga</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q9 === '2' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="kepemilikan_lahan" value="2" x-model="q9" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Pinjam pakai / sewa / garapan</span>
                        </label>
                        <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all"
                               :class="q9 === '3' ? 'border-brand border-2 bg-brand/[0.06]' : 'border-line hover:bg-paper'">
                            <input type="radio" name="kepemilikan_lahan" value="3" x-model="q9" @change="formDirty = true; updateProgress()" :disabled="isLocked" class="h-4 w-4 text-brand">
                            <span class="text-body text-ink">Tidak memiliki lahan</span>
                        </label>
                    </div>
                </fieldset>
            </div>
        </section>

        {{-- ============================================================
             BILAH LENGKET BAWAH (STICKY BOTTOM ACTION BAR) — PRD 10.4
             ============================================================ --}}
        <div class="fixed inset-x-0 bottom-0 z-30 bg-surface/98 backdrop-blur border-t border-line p-4 shadow-2xl">
            <div class="max-w-[720px] mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
                {{-- Status Kemajuan & Pesan Error --}}
                <div class="text-xs">
                    <template x-if="errorMessage">
                        <span class="text-tinggi-tx font-bold flex items-center gap-1.5" x-text="errorMessage"></span>
                    </template>
                    <template x-if="!errorMessage">
                        <span class="text-muted">
                            <strong class="text-ink font-bold" x-text="step3Count + ' dari 9'"></strong> pertanyaan variabel SVI terisi
                        </span>
                    </template>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                    <a href="{{ ($role ?? '') === 'admin' ? '/admin/data' : '/operator/data' }}"
                       @click.prevent="formDirty ? (modalKonfirmasiBatal = true) : (window.location.href = $el.href)"
                       class="btn-secondary !min-h-[44px] flex-1 sm:flex-initial text-center">
                        Batal
                    </a>

                    @unless($isLocked)
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
                    @endunless
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
                <a href="{{ ($role ?? '') === 'admin' ? '/admin/data' : '/operator/data' }}" class="btn-danger-solid !min-h-[36px] !text-xs font-semibold">
                    Ya, Keluar
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
