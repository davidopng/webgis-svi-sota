@extends('layouts.app', [
    'role' => 'admin',
    'userName' => 'Administrator',
    'verifikasiCount' => 7
])

@section('title', 'Antrean Verifikasi — SOTA-GIS')
@section('page-title', 'Antrean Verifikasi Data')

@section('content')
<div x-data="{
    // State Filter & Search
    statusFilter: 'menunggu_verifikasi', // 'semua' | 'menunggu_verifikasi' | 'terverifikasi'
    jalurFilter: 'semua',
    klasifikasiFilter: 'semua',
    searchQuery: '',

    // State Modal Validasi Berdampingan
    modalValidasi: false,
    currentIndex: 0,
    fotoZoom: false,
    fotoRotation: 0,

    // State Modal Konfirmasi Hapus Kedua
    modalHapus: false,
    hapusTarget: null,
    konfirmasiNamaInput: '',

    // Toast Notifikasi
    toastVisible: false,
    toastType: 'berhasil',
    toastMessage: '',

    showToast(type, msg) {
        this.toastType = type;
        this.toastMessage = msg;
        this.toastVisible = true;
        setTimeout(() => { this.toastVisible = false; }, 5000);
    },

    // Basis Data 10 Warga Antrean & Terverifikasi
    antreanList: [
        {
            id: 1,
            nama_kk: 'Yohanis Kaize',
            jalur: 'Jalur B',
            alamat: 'RT 03 / RW 01 No. 12',
            operator: 'Maria K.',
            waktu_input: '4 Sep 2026, 14:32 WIT',
            waktu_ringkas: '1 jam lalu',
            status: 'menunggu_verifikasi',
            latitude: -8.44271,
            longitude: 140.86935,
            lokasi_valid: true,
            skor: 20,
            skor_rata_rata: '2,22',
            klasifikasi: 'sedang',
            klasifikasi_label: 'Kerentanan Sedang',
            // Variabel Fisik untuk Cek Foto
            dinding: 'Kayu',
            lantai: 'Semen',
            atap: 'Seng',
            // 9 Variabel SVI
            svi_items: [
                { no: 1, label: 'Pendapatan', jawaban: 'Kurang dari Rp1.000.000 per bulan', skor: 3 },
                { no: 2, label: 'Pekerjaan', jawaban: 'Petani', skor: 2 },
                { no: 3, label: 'Pendidikan', jawaban: 'Tidak tamat sekolah', skor: 3 },
                { no: 4, label: 'Anggota keluarga', jawaban: '1 orang', skor: 1 },
                { no: 5, label: 'Fisik bangunan', jawaban: 'Kayu + Semen', skor: 2 },
                { no: 6, label: 'Fasilitas sanitasi', jawaban: 'Jamban berbagi dengan tetangga', skor: 2 },
                { no: 7, label: 'Sumber air', jawaban: 'Sumur gali', skor: 3 },
                { no: 8, label: 'Pendapatan tambahan', jawaban: 'Tidak ada', skor: 3 },
                { no: 9, label: 'Kepemilikan lahan', jawaban: 'Milik sendiri', skor: 1 }
            ]
        },
        {
            id: 2,
            nama_kk: 'Siti Rahayu',
            jalur: 'Poros Trans',
            alamat: 'RT 07 / RW 02 No. 05',
            operator: 'Maria K.',
            waktu_input: '4 Sep 2026, 12:15 WIT',
            waktu_ringkas: '2 jam lalu',
            status: 'menunggu_verifikasi',
            latitude: -8.44850,
            longitude: 140.87120,
            lokasi_valid: true,
            skor: 13,
            skor_rata_rata: '1,44',
            klasifikasi: 'rendah',
            klasifikasi_label: 'Kerentanan Rendah',
            dinding: 'Tembok',
            lantai: 'Keramik / ubin',
            atap: 'Seng',
            svi_items: [
                { no: 1, label: 'Pendapatan', jawaban: 'Rp1.000.000 – Rp2.499.999 per bulan', skor: 2 },
                { no: 2, label: 'Pekerjaan', jawaban: 'Pedagang kecil', skor: 2 },
                { no: 3, label: 'Pendidikan', jawaban: 'Tamat SMA / sederajat', skor: 2 },
                { no: 4, label: 'Anggota keluarga', jawaban: '3 orang', skor: 2 },
                { no: 5, label: 'Fisik bangunan', jawaban: 'Tembok + Keramik', skor: 1 },
                { no: 6, label: 'Fasilitas sanitasi', jawaban: 'MCK pribadi', skor: 1 },
                { no: 7, label: 'Sumber air', jawaban: 'Sumur bor', skor: 2 },
                { no: 8, label: 'Pendapatan tambahan', jawaban: 'Hanya bantuan pemerintah', skor: 2 },
                { no: 9, label: 'Kepemilikan lahan', jawaban: 'Milik sendiri', skor: 1 }
            ]
        },
        {
            id: 3,
            nama_kk: 'Kornelis Ndiken',
            jalur: 'Poros Trans',
            alamat: 'RT 09 / RW 03 No. 27',
            operator: 'Maria K.',
            waktu_input: '4 Sep 2026, 11:20 WIT',
            waktu_ringkas: '3 jam lalu',
            status: 'menunggu_verifikasi',
            latitude: -8.45120,
            longitude: 140.87350,
            lokasi_valid: true,
            skor: 18,
            skor_rata_rata: '2,00',
            klasifikasi: 'sedang',
            klasifikasi_label: 'Kerentanan Sedang',
            dinding: 'Kayu dan tembok (campuran)',
            lantai: 'Semen',
            atap: 'Seng',
            svi_items: [
                { no: 1, label: 'Pendapatan', jawaban: 'Rp1.000.000 – Rp2.499.999 per bulan', skor: 2 },
                { no: 2, label: 'Pekerjaan', jawaban: 'Petani', skor: 2 },
                { no: 3, label: 'Pendidikan', jawaban: 'Tamat SMA', skor: 2 },
                { no: 4, label: 'Anggota keluarga', jawaban: '4 orang', skor: 2 },
                { no: 5, label: 'Fisik bangunan', jawaban: 'Campuran + Semen', skor: 2 },
                { no: 6, label: 'Fasilitas sanitasi', jawaban: 'MCK umum', skor: 2 },
                { no: 7, label: 'Sumber air', jawaban: 'Sumur gali', skor: 3 },
                { no: 8, label: 'Pendapatan tambahan', jawaban: 'Tidak ada', skor: 3 },
                { no: 9, label: 'Kepemilikan lahan', jawaban: 'Milik sendiri', skor: 1 }
            ]
        },
        {
            id: 4,
            nama_kk: 'Dominikus Mejai',
            jalur: 'Jalur A',
            alamat: 'RT 01 / RW 01 No. 07',
            operator: 'Yosep M.',
            waktu_input: '4 Sep 2026, 09:30 WIT',
            waktu_ringkas: '5 jam lalu',
            status: 'menunggu_verifikasi',
            latitude: -8.44030,
            longitude: 140.86280,
            lokasi_valid: true,
            skor: 24,
            skor_rata_rata: '2,67',
            klasifikasi: 'tinggi',
            klasifikasi_label: 'Kerentanan Tinggi',
            dinding: 'Kayu',
            lantai: 'Tanah / papan',
            atap: 'Daun / terpal',
            svi_items: [
                { no: 1, label: 'Pendapatan', jawaban: 'Kurang dari Rp1.000.000 per bulan', skor: 3 },
                { no: 2, label: 'Pekerjaan', jawaban: 'Tidak bekerja', skor: 3 },
                { no: 3, label: 'Pendidikan', jawaban: 'Tidak tamat SD', skor: 3 },
                { no: 4, label: 'Anggota keluarga', jawaban: '6 orang', skor: 3 },
                { no: 5, label: 'Fisik bangunan', jawaban: 'Kayu + Tanah', skor: 3 },
                { no: 6, label: 'Fasilitas sanitasi', jawaban: 'Tidak punya MCK', skor: 3 },
                { no: 7, label: 'Sumber air', jawaban: 'Sumur gali', skor: 3 },
                { no: 8, label: 'Pendapatan tambahan', jawaban: 'Tidak ada', skor: 3 },
                { no: 9, label: 'Kepemilikan lahan', jawaban: 'Pinjam pakai', skor: 2 }
            ]
        },
        {
            id: 5,
            nama_kk: 'Albertina Kwipalo',
            jalur: 'Poros Trans',
            alamat: 'RT 08 / RW 02 No. 11',
            operator: 'Maria K.',
            waktu_input: '4 Sep 2026, 08:45 WIT',
            waktu_ringkas: '6 jam lalu',
            status: 'menunggu_verifikasi',
            latitude: -8.44960,
            longitude: 140.87240,
            lokasi_valid: true,
            skor: 19,
            skor_rata_rata: '2,11',
            klasifikasi: 'sedang',
            klasifikasi_label: 'Kerentanan Sedang',
            dinding: 'Kayu',
            lantai: 'Semen',
            atap: 'Seng',
            svi_items: [
                { no: 1, label: 'Pendapatan', jawaban: 'Rp1.000.000 – Rp2.499.999 per bulan', skor: 2 },
                { no: 2, label: 'Pekerjaan', jawaban: 'Petani', skor: 2 },
                { no: 3, label: 'Pendidikan', jawaban: 'Tamat SMP', skor: 3 },
                { no: 4, label: 'Anggota keluarga', jawaban: '4 orang', skor: 2 },
                { no: 5, label: 'Fisik bangunan', jawaban: 'Kayu + Semen', skor: 2 },
                { no: 6, label: 'Fasilitas sanitasi', jawaban: 'MCK umum', skor: 2 },
                { no: 7, label: 'Sumber air', jawaban: 'Sumur bor', skor: 2 },
                { no: 8, label: 'Pendapatan tambahan', jawaban: 'Tidak ada', skor: 3 },
                { no: 9, label: 'Kepemilikan lahan', jawaban: 'Milik sendiri', skor: 1 }
            ]
        },
        {
            id: 6,
            nama_kk: 'Theresia Yolmen',
            jalur: 'Poros Trans',
            alamat: 'RT 11 / RW 03 No. 02',
            operator: 'Yosep M.',
            waktu_input: '3 Sep 2026, 17:10 WIT',
            waktu_ringkas: '1 hari lalu',
            status: 'menunggu_verifikasi',
            latitude: -8.45310,
            longitude: 140.87510,
            lokasi_valid: true,
            skor: 21,
            skor_rata_rata: '2,33',
            klasifikasi: 'tinggi',
            klasifikasi_label: 'Kerentanan Tinggi',
            dinding: 'Kayu',
            lantai: 'Semen',
            atap: 'Seng bekas',
            svi_items: [
                { no: 1, label: 'Pendapatan', jawaban: 'Kurang dari Rp1.000.000 per bulan', skor: 3 },
                { no: 2, label: 'Pekerjaan', jawaban: 'Buruh serabutan', skor: 3 },
                { no: 3, label: 'Pendidikan', jawaban: 'SD', skor: 3 },
                { no: 4, label: 'Anggota keluarga', jawaban: '5 orang', skor: 3 },
                { no: 5, label: 'Fisik bangunan', jawaban: 'Kayu + Semen', skor: 2 },
                { no: 6, label: 'Fasilitas sanitasi', jawaban: 'MCK umum', skor: 2 },
                { no: 7, label: 'Sumber air', jawaban: 'Sumur gali', skor: 3 },
                { no: 8, label: 'Pendapatan tambahan', jawaban: 'Tidak ada', skor: 3 },
                { no: 9, label: 'Kepemilikan lahan', jawaban: 'Milik sendiri', skor: 1 }
            ]
        },
        {
            id: 7,
            nama_kk: 'Fransiskus Samkakai',
            jalur: 'Jalur A',
            alamat: 'RT 02 / RW 01 No. 09',
            operator: 'Yosep M.',
            waktu_input: '3 Sep 2026, 15:20 WIT',
            waktu_ringkas: '1 hari lalu',
            status: 'menunggu_verifikasi',
            latitude: -8.43810,
            longitude: 140.86330,
            lokasi_valid: true,
            skor: 25,
            skor_rata_rata: '2,78',
            klasifikasi: 'tinggi',
            klasifikasi_label: 'Kerentanan Tinggi',
            dinding: 'Kayu',
            lantai: 'Tanah / kayu',
            atap: 'Daun / terpal',
            svi_items: [
                { no: 1, label: 'Pendapatan', jawaban: 'Kurang dari Rp1.000.000 per bulan', skor: 3 },
                { no: 2, label: 'Pekerjaan', jawaban: 'Pekerja serabutan', skor: 3 },
                { no: 3, label: 'Pendidikan', jawaban: 'Tidak tamat sekolah', skor: 3 },
                { no: 4, label: 'Anggota keluarga', jawaban: '5 orang', skor: 3 },
                { no: 5, label: 'Fisik bangunan', jawaban: 'Kayu + Tanah', skor: 3 },
                { no: 6, label: 'Fasilitas sanitasi', jawaban: 'Tidak punya sanitasi', skor: 3 },
                { no: 7, label: 'Sumber air', jawaban: 'Sumur gali', skor: 3 },
                { no: 8, label: 'Pendapatan tambahan', jawaban: 'Tidak ada', skor: 3 },
                { no: 9, label: 'Kepemilikan lahan', jawaban: 'Tidak memiliki lahan', skor: 3 }
            ]
        },
        // Contoh Data Yang Sudah Terverifikasi
        {
            id: 8,
            nama_kk: 'Petrus Gebze',
            jalur: 'Jalur A',
            alamat: 'RT 01 / RW 01 No. 04',
            operator: 'Yosep M.',
            waktu_input: '3 Sep 2026, 10:00 WIT',
            waktu_ringkas: '1 hari lalu',
            status: 'terverifikasi',
            latitude: -8.43920,
            longitude: 140.86410,
            lokasi_valid: true,
            skor: 23,
            skor_rata_rata: '2,56',
            klasifikasi: 'tinggi',
            klasifikasi_label: 'Kerentanan Tinggi',
            dinding: 'Kayu',
            lantai: 'Semen',
            atap: 'Seng',
            svi_items: [
                { no: 1, label: 'Pendapatan', jawaban: 'Kurang dari Rp1.000.000 per bulan', skor: 3 },
                { no: 2, label: 'Pekerjaan', jawaban: 'Buruh harian lepas', skor: 3 },
                { no: 3, label: 'Pendidikan', jawaban: 'Tidak tamat sekolah', skor: 3 },
                { no: 4, label: 'Anggota keluarga', jawaban: '5 orang', skor: 3 },
                { no: 5, label: 'Fisik bangunan', jawaban: 'Kayu + Semen', skor: 2 },
                { no: 6, label: 'Fasilitas sanitasi', jawaban: 'MCK umum', skor: 2 },
                { no: 7, label: 'Sumber air', jawaban: 'Sumur gali', skor: 3 },
                { no: 8, label: 'Pendapatan tambahan', jawaban: 'Tidak ada', skor: 3 },
                { no: 9, label: 'Kepemilikan lahan', jawaban: 'Milik sendiri', skor: 1 }
            ]
        }
    ],

    get filteredAntrean() {
        return this.antreanList.filter(item => {
            const matchStatus = this.statusFilter === 'semua' || item.status === this.statusFilter;
            const matchJalur = this.jalurFilter === 'semua' || item.jalur === this.jalurFilter;
            const matchKlas = this.klasifikasiFilter === 'semua' || item.klasifikasi === this.klasifikasiFilter;
            const matchQuery = item.nama_kk.toLowerCase().includes(this.searchQuery.toLowerCase().trim()) ||
                               item.alamat.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
            return matchStatus && matchJalur && matchKlas && matchQuery;
        });
    },

    get currentItem() {
        return this.filteredAntrean[this.currentIndex] || null;
    },

    bukaModalPeriksa(index) {
        this.currentIndex = index;
        this.fotoZoom = false;
        this.fotoRotation = 0;
        this.modalValidasi = true;
    },

    navSebelumnya() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.fotoZoom = false;
            this.fotoRotation = 0;
        }
    },

    navBerikutnya() {
        if (this.currentIndex < this.filteredAntrean.length - 1) {
            this.currentIndex++;
            this.fotoZoom = false;
            this.fotoRotation = 0;
        }
    },

    // Aksi Verifikasi & Tampilkan di Peta
    verifikasiItem() {
        if (!this.currentItem) return;
        const target = this.currentItem;
        target.status = 'terverifikasi';
        this.modalValidasi = false;
        this.showToast('berhasil', `Data ${target.nama_kk} terverifikasi dan kini tampil di peta publik.`);
    },

    // Pemicu Modal Hapus Kedua
    bukaModalHapus(item) {
        this.hapusTarget = item;
        this.konfirmasiNamaInput = '';
        this.modalHapus = true;
    },

    konfirmasiHapusPermanen() {
        if (!this.hapusTarget) return;
        if (this.konfirmasiNamaInput.trim() !== this.hapusTarget.nama_kk) return;

        const nama = this.hapusTarget.nama_kk;
        this.antreanList = this.antreanList.filter(x => x.id !== this.hapusTarget.id);
        this.modalHapus = false;
        this.modalValidasi = false;
        this.showToast('gagal', `Data ${nama} dihapus permanen.`);
    }
}" class="space-y-6">

    {{-- Toast Notification Melayang --}}
    <div x-show="toastVisible"
         x-transition
         class="fixed top-4 right-4 z-[100] max-w-sm"
         style="display: none;"
         x-cloak>
        <x-toast ::type="toastType" ::message="toastMessage" />
    </div>

    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-ink">Antrean Verifikasi Data</h2>
            <p class="text-help mt-0.5">Pemeriksaan kelengkapan 9 variabel SVI, kesesuaian fisik bangunan dengan foto, dan keabsahan titik koordinat.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sedang-bg text-sedang-tx border border-sedang/30">
                <span class="h-2 w-2 rounded-full bg-sedang animate-pulse"></span>
                <span x-text="antreanList.filter(x => x.status === 'menunggu_verifikasi').length + ' data menunggu'"></span>
            </span>
        </div>
    </div>

    {{-- Filter Bar Lengkap (PRD 11.2 Wireframe) --}}
    <div class="card p-4 space-y-3">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            {{-- Tabs Filter Status --}}
            <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                        @click="statusFilter = 'semua'"
                        :class="statusFilter === 'semua' ? 'bg-brand text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                        class="rounded-md px-3 py-1.5 text-xs transition-colors">
                    Semua (<span x-text="antreanList.length"></span>)
                </button>
                <button type="button"
                        @click="statusFilter = 'menunggu_verifikasi'"
                        :class="statusFilter === 'menunggu_verifikasi' ? 'bg-sedang text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                        class="rounded-md px-3 py-1.5 text-xs transition-colors">
                    Menunggu Verifikasi (<span x-text="antreanList.filter(x => x.status === 'menunggu_verifikasi').length"></span>)
                </button>
                <button type="button"
                        @click="statusFilter = 'terverifikasi'"
                        :class="statusFilter === 'terverifikasi' ? 'bg-rendah text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                        class="rounded-md px-3 py-1.5 text-xs transition-colors">
                    Terverifikasi (<span x-text="antreanList.filter(x => x.status === 'terverifikasi').length"></span>)
                </button>
            </div>

            {{-- Input Cari Nama KK --}}
            <div class="w-full md:w-72 relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text"
                       x-model="searchQuery"
                       class="input-text pl-9 !min-h-[38px] !py-1 text-xs"
                       placeholder="Cari nama kepala keluarga…">
            </div>
        </div>

        {{-- Dropdown Filter Jalur & Klasifikasi --}}
        <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-line text-xs">
            <div class="flex items-center gap-2">
                <span class="text-muted font-medium">Jalur:</span>
                <select x-model="jalurFilter" class="input-select !min-h-[32px] !py-1 !text-xs !w-auto">
                    <option value="semua">Semua Wilayah</option>
                    <option value="Jalur A">Jalur A</option>
                    <option value="Jalur B">Jalur B</option>
                    <option value="Poros Trans">Poros Trans</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-muted font-medium">Klasifikasi:</span>
                <select x-model="klasifikasiFilter" class="input-select !min-h-[32px] !py-1 !text-xs !w-auto">
                    <option value="semua">Semua Tingkat</option>
                    <option value="rendah">Kerentanan Rendah</option>
                    <option value="sedang">Kerentanan Sedang</option>
                    <option value="tinggi">Kerentanan Tinggi</option>
                </select>
            </div>

            <button type="button"
                    @click="statusFilter = 'semua'; jalurFilter = 'semua'; klasifikasiFilter = 'semua'; searchQuery = ''"
                    class="text-brand font-semibold hover:underline ml-auto">
                Atur ulang filter
            </button>
        </div>
    </div>

    {{-- ============================================================
         TABEL ANTREAN VERIFIKASI (PRD 11.2)
         ============================================================ --}}
    <div class="card overflow-hidden !p-0 shadow-sm">
        <template x-if="filteredAntrean.length > 0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-paper border-b border-line text-xs font-semibold uppercase tracking-wider text-muted">
                        <tr>
                            <th class="py-3.5 px-4 w-16">Foto</th>
                            <th class="py-3.5 px-4">Nama KK &amp; Penginput</th>
                            <th class="py-3.5 px-4">Jalur</th>
                            <th class="py-3.5 px-4 text-center">Skor</th>
                            <th class="py-3.5 px-4">Klasifikasi</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-ink">
                        <template x-for="(item, index) in filteredAntrean" :key="item.id">
                            <tr class="hover:bg-paper/40 transition-colors"
                                :class="item.status === 'menunggu_verifikasi' ? 'border-l-[3px] border-l-sedang bg-sedang/[0.01]' : ''">
                                {{-- Thumbnail Foto 48px Persegi (PRD 11.2) --}}
                                <td class="py-3 px-4">
                                    <button type="button"
                                            @click="bukaModalPeriksa(index)"
                                            title="Klik untuk melihat foto dan periksa data"
                                            class="h-12 w-12 rounded-lg bg-paper border border-line flex items-center justify-center text-muted font-bold text-base hover:border-brand transition-colors cursor-pointer overflow-hidden shadow-inner">
                                        🏠
                                    </button>
                                </td>

                                {{-- Nama KK & Operator Penginput --}}
                                <td class="py-3 px-4">
                                    <p class="font-bold text-ink" x-text="item.nama_kk"></p>
                                    <p class="text-xs text-muted" x-text="item.alamat + ' · ' + item.operator"></p>
                                </td>

                                {{-- Jalur --}}
                                <td class="py-3 px-4 text-xs font-medium" x-text="item.jalur"></td>

                                {{-- Skor SVI (Tabular) --}}
                                <td class="py-3 px-4 text-center font-bold tabular-nums" x-text="item.skor"></td>

                                {{-- Klasifikasi Badge --}}
                                <td class="py-3 px-4">
                                    <template x-if="item.klasifikasi === 'tinggi'">
                                        <x-badge-klasifikasi tingkat="tinggi" />
                                    </template>
                                    <template x-if="item.klasifikasi === 'sedang'">
                                        <x-badge-klasifikasi tingkat="sedang" />
                                    </template>
                                    <template x-if="item.klasifikasi === 'rendah'">
                                        <x-badge-klasifikasi tingkat="rendah" />
                                    </template>
                                </td>

                                {{-- Status Verifikasi --}}
                                <td class="py-3 px-4">
                                    <template x-if="item.status === 'menunggu_verifikasi'">
                                        <x-badge-status status="menunggu_verifikasi" />
                                    </template>
                                    <template x-if="item.status === 'terverifikasi'">
                                        <x-badge-status status="terverifikasi" />
                                    </template>
                                </td>

                                {{-- Aksi: Periksa / Lihat + Menu ⋮ --}}
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                @click="bukaModalPeriksa(index)"
                                                class="btn-primary !min-h-[34px] !py-1 !px-3.5 !text-xs font-bold shadow-sm"
                                                :class="item.status === 'terverifikasi' ? '!bg-paper !text-ink hover:!bg-line/60 border border-line shadow-none' : ''">
                                            <span x-text="item.status === 'menunggu_verifikasi' ? 'Periksa' : 'Lihat'"></span>
                                        </button>

                                        {{-- Menu Dropdown ⋮ --}}
                                        <x-tabel-aksi>
                                            <a :href="'/admin/data/' + item.id + '/edit'"
                                               class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors text-ink hover:bg-paper">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/></svg>
                                                <span>Revisi data</span>
                                            </a>
                                            <button type="button"
                                                    @click="bukaModalHapus(item)"
                                                    class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors text-tinggi hover:bg-tinggi-bg">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                <span>Hapus data</span>
                                            </button>
                                        </x-tabel-aksi>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        {{-- Empty State Jika Tidak Ada Antrean --}}
        <template x-if="filteredAntrean.length === 0">
            <x-empty-state
                title="Tidak ada antrean verifikasi"
                description="Semua data warga telah diperiksa dan berstatus terverifikasi, atau tidak sesuai dengan penyaring saat ini."
                action-text="Lihat Semua Data"
                action-url="/admin/data"
                icon="shield"
            />
        </template>

        {{-- Paginasi Footer Sesuai PRD 11.2 --}}
        <div class="p-4 border-t border-line flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-muted">
            <div>
                Menampilkan <strong class="text-ink" x-text="filteredAntrean.length"></strong> dari 91 data
            </div>

            <div class="flex items-center gap-1.5">
                <button type="button" class="px-2.5 py-1 rounded bg-paper border border-line text-muted cursor-not-allowed">‹</button>
                <button type="button" class="px-2.5 py-1 rounded bg-brand text-white font-bold">1</button>
                <button type="button" class="px-2.5 py-1 rounded bg-paper border border-line hover:bg-paper/80">2</button>
                <button type="button" class="px-2.5 py-1 rounded bg-paper border border-line hover:bg-paper/80">3</button>
                <button type="button" class="px-2.5 py-1 rounded bg-paper border border-line hover:bg-paper/80">›</button>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL VALIDASI BERDAMPINGAN (PRD 11.3) — LEBAR 1100px (50/50)
         ============================================================ --}}
    <div x-show="modalValidasi"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-98"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-98"
         class="fixed inset-0 z-50 overflow-y-auto bg-ink/70 flex items-center justify-center p-3 sm:p-5"
         style="display: none;"
         x-cloak>

        <div @click.outside="modalValidasi = false"
             class="bg-surface rounded-2xl shadow-2xl max-w-[1100px] w-full max-h-[92vh] flex flex-col overflow-hidden border border-line">

            {{-- Header Modal: Nama KK & Status --}}
            <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-paper/60 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg font-bold text-ink flex items-center gap-2">
                        <span>Periksa Data —</span>
                        <span x-text="currentItem ? currentItem.nama_kk : ''" class="text-brand"></span>
                    </h3>
                    <template x-if="currentItem && currentItem.status === 'menunggu_verifikasi'">
                        <x-badge-status status="menunggu_verifikasi" />
                    </template>
                    <template x-if="currentItem && currentItem.status === 'terverifikasi'">
                        <x-badge-status status="terverifikasi" />
                    </template>
                </div>

                <button type="button" @click="modalValidasi = false"
                        class="h-8 w-8 rounded-lg text-muted hover:text-ink hover:bg-paper flex items-center justify-center transition-colors"
                        aria-label="Tutup modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Badan Modal: Dua Kolom 50/50 (PRD 11.3) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-line overflow-y-auto p-6 gap-6 flex-1">
                {{-- ============================================================
                     KOLOM KIRI: BUKTI VISUAL (Foto Rumah & Peta Titik Koordinat)
                     ============================================================ --}}
                <div class="space-y-5">
                    {{-- Foto Rumah Tampak Depan --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-muted">Foto Bangunan Rumah</p>
                            <div class="flex items-center gap-1">
                                <button type="button"
                                        @click="fotoZoom = !fotoZoom"
                                        class="btn-secondary !min-h-[28px] !py-0.5 !px-2.5 !text-[11px]">
                                    <span x-text="fotoZoom ? 'Normal' : '🔍 Perbesar'"></span>
                                </button>
                                <button type="button"
                                        @click="fotoRotation = (fotoRotation + 90) % 360"
                                        class="btn-secondary !min-h-[28px] !py-0.5 !px-2.5 !text-[11px]">
                                    ↻ Putar
                                </button>
                            </div>
                        </div>

                        <div class="aspect-video bg-paper rounded-xl border border-line flex flex-col items-center justify-center text-muted p-4 overflow-hidden relative shadow-inner">
                            <div class="w-full h-full flex flex-col items-center justify-center transition-transform duration-300"
                                 :style="`transform: rotate(${fotoRotation}deg) scale(${fotoZoom ? 1.4 : 1});`">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="mb-2 text-muted/60"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                <p class="text-xs font-bold text-ink">Foto Rumah Responden</p>
                                <p class="text-[11px] text-muted mt-0.5">Tampak depan seluruh bangunan terlihat jelas</p>
                            </div>
                        </div>
                    </div>

                    {{-- Peta Titik Koordinat Rumah --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-muted">Peta Titik Koordinat</p>
                            <span class="text-xs font-bold text-ink tabular-nums"
                                  x-text="currentItem ? currentItem.latitude + ', ' + currentItem.longitude : ''"></span>
                        </div>

                        {{-- Kotak Peta Pratinjau --}}
                        <div class="h-48 bg-paper rounded-xl border border-line flex flex-col items-center justify-center p-4 text-center relative shadow-inner">
                            <div class="h-8 w-8 rounded-full bg-brand text-white flex items-center justify-center shadow-lg border-2 border-white mb-2">
                                ⌖
                            </div>
                            <p class="text-xs font-bold text-ink">Lokasi Rumah di Kampung Sota</p>
                            <p class="text-[11px] text-rendah-tx font-bold mt-1 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Titik berada di dalam wilayah permukiman Kampung Sota</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ============================================================
                     KOLOM KANAN: DATA SURVEI & 9 VARIABEL SVI (PRD 11.3)
                     ============================================================ --}}
                <div class="space-y-4 text-xs">
                    {{-- 1. Identitas Rumah Tangga --}}
                    <div class="space-y-1 pb-3 border-b border-line">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted mb-2">Identitas Kepala Keluarga</p>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-muted block">Nama KK:</span>
                                <strong class="text-ink text-sm" x-text="currentItem ? currentItem.nama_kk : ''"></strong>
                            </div>
                            <div>
                                <span class="text-muted block">Jalur / Wilayah:</span>
                                <strong class="text-ink" x-text="currentItem ? currentItem.jalur : ''"></strong>
                            </div>
                            <div>
                                <span class="text-muted block">Alamat Detail:</span>
                                <span class="text-ink" x-text="currentItem ? currentItem.alamat : ''"></span>
                            </div>
                            <div>
                                <span class="text-muted block">Penginput:</span>
                                <span class="text-ink" x-text="currentItem ? currentItem.operator + ' · ' + currentItem.waktu_ringkas : ''"></span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Blok Khusus: Periksa Terhadap Foto (PRD 11.3) --}}
                    <div class="bg-paper p-3.5 rounded-xl border border-line space-y-2">
                        <div class="flex items-center gap-1.5 text-sedang-tx font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>⚠️ Periksa Kesesuaian Terhadap Foto Rumah</span>
                        </div>
                        <p class="text-muted text-[11px]">Pastikan jawaban kader di bawah cocok dengan kondisi fisik di foto:</p>

                        <div class="grid grid-cols-3 gap-2 pt-1 font-medium">
                            <div class="bg-surface p-2 rounded-lg border border-line">
                                <span class="text-muted block text-[10px] uppercase">Dinding:</span>
                                <strong class="text-ink text-xs" x-text="currentItem ? currentItem.dinding : ''"></strong>
                            </div>
                            <div class="bg-surface p-2 rounded-lg border border-line">
                                <span class="text-muted block text-[10px] uppercase">Lantai:</span>
                                <strong class="text-ink text-xs" x-text="currentItem ? currentItem.lantai : ''"></strong>
                            </div>
                            <div class="bg-surface p-2 rounded-lg border border-line">
                                <span class="text-muted block text-[10px] uppercase">Atap:</span>
                                <strong class="text-ink text-xs" x-text="currentItem ? currentItem.atap : ''"></strong>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Jawaban 9 Variabel SVI --}}
                    <div class="space-y-1.5">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Jawaban 9 Variabel SVI</p>
                        <div class="border border-line rounded-xl divide-y divide-line overflow-hidden bg-surface">
                            <template x-if="currentItem">
                                <template x-for="varItem in currentItem.svi_items" :key="varItem.no">
                                    <div class="p-2.5 flex items-center justify-between gap-3 hover:bg-paper/40">
                                        <div class="min-w-0">
                                            <span class="font-bold text-ink" x-text="varItem.no + '. ' + varItem.label"></span>
                                            <p class="text-muted truncate text-[11px]" x-text="varItem.jawaban"></p>
                                        </div>
                                        <span class="flex-shrink-0 font-bold text-ink bg-paper border border-line px-2 py-0.5 rounded text-[11px] tabular-nums"
                                              x-text="'Skor ' + varItem.skor"></span>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>

                    {{-- 4. Blok Skor Read-Only (PRD 11.3) --}}
                    <div class="bg-paper p-3.5 rounded-xl border border-line space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-muted font-medium">Total Skor SVI:</span>
                            <span class="font-bold text-ink text-base tabular-nums" x-text="currentItem ? currentItem.skor + ' (skala 9–27)' : ''"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted font-medium">Skor Rata-Rata:</span>
                            <span class="font-bold text-ink tabular-nums" x-text="currentItem ? currentItem.skor_rata_rata : ''"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted font-medium">Klasifikasi:</span>
                            <template x-if="currentItem && currentItem.klasifikasi === 'tinggi'">
                                <x-badge-klasifikasi tingkat="tinggi" />
                            </template>
                            <template x-if="currentItem && currentItem.klasifikasi === 'sedang'">
                                <x-badge-klasifikasi tingkat="sedang" />
                            </template>
                            <template x-if="currentItem && currentItem.klasifikasi === 'rendah'">
                                <x-badge-klasifikasi tingkat="rendah" />
                            </template>
                        </div>
                        <p class="text-[11px] text-muted italic pt-1 border-t border-line">
                            * Skor dihitung otomatis oleh sistem berdasarkan rumus additive scoring dan tidak dapat diubah manual.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Footer Modal: Navigasi Antrean & Aksi Validasi (PRD 11.3) --}}
            <div class="px-6 py-3.5 border-t border-line bg-paper/60 flex flex-col sm:flex-row items-center justify-between gap-3 flex-shrink-0">
                {{-- Kiri: Hapus Data & Navigasi Sebelum/Berikut --}}
                <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-start">
                    <button type="button"
                            @click="bukaModalHapus(currentItem)"
                            class="btn-danger !min-h-[38px] !py-1 !px-3 !text-xs font-semibold">
                        Hapus data
                    </button>

                    <div class="flex items-center gap-1">
                        <button type="button"
                                @click="navSebelumnya"
                                :disabled="currentIndex === 0"
                                class="btn-secondary !min-h-[38px] !py-1 !px-2.5 !text-xs disabled:opacity-40 disabled:cursor-not-allowed"
                                title="Data Sebelumnya">
                            ‹ Sebelumnya
                        </button>
                        <span class="text-[11px] text-muted tabular-nums px-2"
                              x-text="(currentIndex + 1) + ' dari ' + filteredAntrean.length"></span>
                        <button type="button"
                                @click="navBerikutnya"
                                :disabled="currentIndex >= filteredAntrean.length - 1"
                                class="btn-secondary !min-h-[38px] !py-1 !px-2.5 !text-xs disabled:opacity-40 disabled:cursor-not-allowed"
                                title="Data Berikutnya">
                            Berikutnya ›
                        </button>
                    </div>
                </div>

                {{-- Kanan: Revisi & Verifikasi --}}
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <a :href="'/admin/data/' + (currentItem ? currentItem.id : 1) + '/edit'"
                       class="btn-secondary !min-h-[38px] !py-1 !px-4 !text-xs font-semibold">
                        Revisi data
                    </a>

                    <button type="button"
                            @click="verifikasiItem"
                            class="btn-primary !min-h-[38px] !py-1 !px-5 !text-xs font-bold shadow-md">
                        ✓ Verifikasi &amp; Tampilkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL KONFIRMASI HAPUS PERMANEN KEDUA (PRD 11.3)
         Mengharuskan mengetik nama KK sebelum tombol hapus aktif
         ============================================================ --}}
    <div x-show="modalHapus"
         x-transition
         class="fixed inset-0 z-[60] bg-ink/75 flex items-center justify-center p-4"
         style="display: none;"
         x-cloak>

        <div @click.outside="modalHapus = false"
             class="bg-surface rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-tinggi/30">
            <div class="flex items-center gap-3 text-tinggi">
                <div class="h-10 w-10 rounded-full bg-tinggi-bg flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-ink">Hapus Data Permanen?</h3>
                    <p class="text-xs text-muted">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            <p class="text-xs text-ink leading-relaxed">
                Anda akan menghapus data survei kepala keluarga: <strong class="text-tinggi-tx" x-text="hapusTarget ? hapusTarget.nama_kk : ''"></strong>.
            </p>

            <div class="space-y-1.5 pt-1">
                <label class="block text-xs font-medium text-muted">
                    Ketik nama kepala keluarga <strong class="text-ink" x-text="hapusTarget ? hapusTarget.nama_kk : ''"></strong> untuk mengonfirmasi:
                </label>
                <input type="text"
                       x-model="konfirmasiNamaInput"
                       class="input-text text-sm"
                       placeholder="Ketik nama persis di sini…">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <button type="button" @click="modalHapus = false" class="btn-secondary !min-h-[38px] !text-xs font-semibold">
                    Batal
                </button>
                <button type="button"
                        @click="konfirmasiHapusPermanen"
                        :disabled="!hapusTarget || konfirmasiNamaInput.trim() !== hapusTarget.nama_kk"
                        class="btn-danger-solid !min-h-[38px] !text-xs font-bold disabled:opacity-40 disabled:cursor-not-allowed">
                    Hapus Permanen
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
