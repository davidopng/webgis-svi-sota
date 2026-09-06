@extends('layouts.app', [
    'role' => 'kepala_kampung',
    'userName' => 'Kepala Kampung Sota'
])

@section('title', 'Panel Laporan Rekapitulasi — SOTA-GIS')
@section('page-title', 'Panel Laporan Rekapitulasi')

@section('content')
<div x-data="{
    // Filter State
    wilayah: 'semua',
    tingkatRendah: true,
    tingkatSedang: true,
    tingkatTinggi: true,

    // Kolom State
    kolomIdentitas: true,
    kolomKoordinat: true,
    kolomSvi9: true,
    kolomSkor: true,

    // Unduh State
    loadingPdf: false,
    loadingExcel: false,

    // Toast State
    toastVisible: false,
    toastType: 'berhasil',
    toastMessage: '',

    showToast(type, msg) {
        this.toastType = type;
        this.toastMessage = msg;
        this.toastVisible = true;
        setTimeout(() => { this.toastVisible = false; }, 5000);
    },

    // Aksi Unduh PDF dengan simulasi state 'menyiapkan berkas'
    unduhPdf() {
        if (this.loadingPdf || this.loadingExcel) return;
        this.loadingPdf = true;
        setTimeout(() => {
            this.loadingPdf = false;
            this.showToast('berhasil', 'Laporan rekapitulasi format PDF berhasil disiapkan dan siap dicetak.');
        }, 2000);
    },

    // Aksi Unduh Excel dengan simulasi state 'menyiapkan berkas'
    unduhExcel() {
        if (this.loadingPdf || this.loadingExcel) return;
        this.loadingExcel = true;
        setTimeout(() => {
            this.loadingExcel = false;
            this.showToast('berhasil', 'Laporan rekapitulasi format Excel (.xlsx) berhasil disiapkan.');
        }, 2000);
    },

    // Data Responden Terverifikasi untuk Pratinjau
    dataWarga: [
        {
            nama: 'Fransiskus Samkakai',
            jalur: 'Jalur A',
            alamat: 'RT 02 / RW 01 No. 09',
            lat: -8.43810,
            lng: 140.86330,
            svi: '3, 3, 3, 3, 3, 3, 3, 3, 1',
            skor: 25,
            klas: 'tinggi',
            klasLabel: 'Kerentanan Tinggi'
        },
        {
            nama: 'Dominikus Mejai',
            jalur: 'Jalur A',
            alamat: 'RT 01 / RW 01 No. 07',
            lat: -8.44030,
            lng: 140.86280,
            svi: '3, 3, 3, 3, 3, 3, 3, 2, 1',
            skor: 24,
            klas: 'tinggi',
            klasLabel: 'Kerentanan Tinggi'
        },
        {
            nama: 'Petrus Gebze',
            jalur: 'Jalur A',
            alamat: 'RT 01 / RW 01 No. 04',
            lat: -8.43920,
            lng: 140.86410,
            svi: '3, 3, 3, 3, 2, 2, 3, 3, 1',
            skor: 23,
            klas: 'tinggi',
            klasLabel: 'Kerentanan Tinggi'
        },
        {
            nama: 'Markus Balagaize',
            jalur: 'Jalur B',
            alamat: 'RT 04 / RW 02 No. 18',
            lat: -8.44390,
            lng: 140.86750,
            svi: '3, 2, 3, 3, 2, 2, 3, 3, 1',
            skor: 22,
            klas: 'tinggi',
            klasLabel: 'Kerentanan Tinggi'
        },
        {
            nama: 'Theresia Yolmen',
            jalur: 'Poros Trans',
            alamat: 'RT 11 / RW 03 No. 02',
            lat: -8.45310,
            lng: 140.87510,
            svi: '3, 3, 3, 3, 2, 2, 3, 1, 1',
            skor: 21,
            klas: 'tinggi',
            klasLabel: 'Kerentanan Tinggi'
        },
        {
            nama: 'Yohanis Kaize',
            jalur: 'Jalur B',
            alamat: 'RT 03 / RW 01 No. 12',
            lat: -8.44271,
            lng: 140.86935,
            svi: '3, 2, 3, 1, 2, 2, 3, 3, 1',
            skor: 20,
            klas: 'sedang',
            klasLabel: 'Kerentanan Sedang'
        },
        {
            nama: 'Albertina Kwipalo',
            jalur: 'Poros Trans',
            alamat: 'RT 08 / RW 02 No. 11',
            lat: -8.44960,
            lng: 140.87240,
            svi: '2, 2, 3, 2, 2, 2, 2, 3, 1',
            skor: 19,
            klas: 'sedang',
            klasLabel: 'Kerentanan Sedang'
        },
        {
            nama: 'Kornelis Ndiken',
            jalur: 'Poros Trans',
            alamat: 'RT 09 / RW 03 No. 27',
            lat: -8.45120,
            lng: 140.87350,
            svi: '2, 2, 2, 2, 2, 2, 3, 2, 1',
            skor: 18,
            klas: 'sedang',
            klasLabel: 'Kerentanan Sedang'
        },
        {
            nama: 'Agustina Mahuze',
            jalur: 'Jalur B',
            alamat: 'RT 05 / RW 02 No. 08',
            lat: -8.44450,
            lng: 140.86820,
            svi: '2, 2, 2, 2, 2, 1, 2, 2, 1',
            skor: 16,
            klas: 'sedang',
            klasLabel: 'Kerentanan Sedang'
        },
        {
            nama: 'Bonaventura Ndiken',
            jalur: 'Jalur B',
            alamat: 'RT 06 / RW 02 No. 03',
            lat: -8.44520,
            lng: 140.87010,
            svi: '2, 2, 2, 1, 2, 1, 2, 1, 1',
            skor: 14,
            klas: 'rendah',
            klasLabel: 'Kerentanan Rendah'
        },
        {
            nama: 'Siti Rahayu',
            jalur: 'Poros Trans',
            alamat: 'RT 07 / RW 02 No. 05',
            lat: -8.44850,
            lng: 140.87120,
            svi: '2, 2, 2, 2, 1, 1, 2, 1, 1',
            skor: 13,
            klas: 'rendah',
            klasLabel: 'Kerentanan Rendah'
        },
        {
            nama: 'Simon Kaize',
            jalur: 'Jalur B',
            alamat: 'RT 06 / RW 02 No. 14',
            lat: -8.44610,
            lng: 140.86900,
            svi: '2, 2, 2, 1, 1, 1, 2, 1, 1',
            skor: 13,
            klas: 'rendah',
            klasLabel: 'Kerentanan Rendah'
        }
    ],

    // Perhitungan Dinamis Jumlah Responden
    get estimasiJumlah() {
        let total = 0;
        // Jalur A: 0 Rendah, 7 Sedang, 8 Tinggi
        if (this.wilayah === 'semua' || this.wilayah === 'Jalur A') {
            if (this.tingkatRendah) total += 0;
            if (this.tingkatSedang) total += 7;
            if (this.tingkatTinggi) total += 8;
        }
        // Jalur B: 4 Rendah, 21 Sedang, 8 Tinggi
        if (this.wilayah === 'semua' || this.wilayah === 'Jalur B') {
            if (this.tingkatRendah) total += 4;
            if (this.tingkatSedang) total += 21;
            if (this.tingkatTinggi) total += 8;
        }
        // Poros Trans: 3 Rendah, 32 Sedang, 8 Tinggi
        if (this.wilayah === 'semua' || this.wilayah === 'Poros Trans') {
            if (this.tingkatRendah) total += 3;
            if (this.tingkatSedang) total += 32;
            if (this.tingkatTinggi) total += 8;
        }
        return total;
    },

    get filteredData() {
        return this.dataWarga.filter(item => {
            const matchWilayah = this.wilayah === 'semua' || item.jalur === this.wilayah;
            const matchKlas = (this.tingkatRendah && item.klas === 'rendah') ||
                              (this.tingkatSedang && item.klas === 'sedang') ||
                              (this.tingkatTinggi && item.klas === 'tinggi');
            return matchWilayah && matchKlas;
        });
    }
}" class="space-y-6">

    {{-- Toast Notifikasi Melayang --}}
    <div x-show="toastVisible"
         x-transition
         class="fixed top-4 right-4 z-[100] max-w-sm"
         style="display: none;"
         x-cloak>
        <x-toast ::type="toastType" ::message="toastMessage" />
    </div>

    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-line">
        <div>
            <h2 class="text-xl font-bold text-ink">Unduh Laporan Rekapitulasi</h2>
            <p class="text-help text-xs mt-0.5">Penyusunan berkas cetak dan digital rekapitulasi kerentanan warga Kampung Sota untuk keperluan arsip kampung, pertanggungjawaban, dan koordinasi dengan Dinas Sosial.</p>
        </div>
        <a href="/pimpinan" class="btn-secondary !min-h-[38px] !text-xs font-semibold self-start sm:self-auto">
            ← Kembali ke Dashboard
        </a>
    </div>

    {{-- ============================================================
         KARTU PENGATURAN FILTER & KOLOM LAPORAN (PRD 12.3)
         ============================================================ --}}
    <div class="card space-y-6">
        <h3 class="text-section-title text-ink border-b border-line pb-3">Konfigurasi Berkas Laporan</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- 1. Filter Wilayah & Tingkat Kerentanan --}}
            <div class="space-y-4">
                {{-- Dropdown Wilayah --}}
                <div>
                    <label class="block text-label text-ink mb-1.5">Wilayah Permukiman</label>
                    <select x-model="wilayah" class="input-select text-sm font-medium">
                        <option value="semua">Semua Wilayah (91 Rumah Tangga)</option>
                        <option value="Jalur A">Jalur A Saja (15 Rumah Tangga)</option>
                        <option value="Jalur B">Jalur B Saja (33 Rumah Tangga)</option>
                        <option value="Poros Trans">Poros Trans Saja (43 Rumah Tangga)</option>
                    </select>
                </div>

                {{-- Checkbox Tingkat Kerentanan --}}
                <div>
                    <label class="block text-label text-ink mb-2">Tingkat Kerentanan yang Disertakan</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:bg-paper cursor-pointer transition-colors"
                               :class="tingkatRendah ? 'border-rendah/40 bg-rendah-bg/20' : ''">
                            <input type="checkbox" x-model="tingkatRendah" class="rounded border-line text-rendah focus:ring-rendah">
                            <span class="text-xs font-bold text-ink flex items-center gap-1.5">
                                <span class="text-rendah font-bold">●</span> Kerentanan Rendah <span class="text-muted font-normal">(7 KK)</span>
                            </span>
                        </label>

                        <label class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:bg-paper cursor-pointer transition-colors"
                               :class="tingkatSedang ? 'border-sedang/40 bg-sedang-bg/20' : ''">
                            <input type="checkbox" x-model="tingkatSedang" class="rounded border-line text-sedang focus:ring-sedang">
                            <span class="text-xs font-bold text-ink flex items-center gap-1.5">
                                <span class="text-sedang font-bold">▲</span> Kerentanan Sedang <span class="text-muted font-normal">(60 KK)</span>
                            </span>
                        </label>

                        <label class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:bg-paper cursor-pointer transition-colors"
                               :class="tingkatTinggi ? 'border-tinggi/40 bg-tinggi-bg/20' : ''">
                            <input type="checkbox" x-model="tingkatTinggi" class="rounded border-line text-tinggi focus:ring-tinggi">
                            <span class="text-xs font-bold text-ink flex items-center gap-1.5">
                                <span class="text-tinggi font-bold">◆</span> Kerentanan Tinggi <span class="text-muted font-normal">(24 KK · Prioritas)</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- 2. Pilihan Kolom yang Disertakan (PRD 12.3) --}}
            <div class="space-y-3">
                <label class="block text-label text-ink">Kolom yang Disertakan dalam Berkas</label>
                <div class="bg-paper p-4 rounded-xl border border-line space-y-2.5 text-xs">
                    <label class="flex items-center gap-2.5 cursor-not-allowed text-muted">
                        <input type="checkbox" checked disabled class="rounded border-line text-brand">
                        <span class="font-bold text-ink">Identitas &amp; Alamat Lengkap (Wajib)</span>
                        <span class="text-[10px] text-muted ml-auto">(Nama KK, Jalur, RT/RW, No. Rumah)</span>
                    </label>

                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" x-model="kolomKoordinat" class="rounded border-line text-brand">
                        <span class="font-medium text-ink">Titik Koordinat (Latitude &amp; Longitude WGS84)</span>
                    </label>

                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" x-model="kolomSvi9" class="rounded border-line text-brand">
                        <span class="font-medium text-ink">Rincian Skor 9 Variabel Sosial Ekonomi SVI</span>
                    </label>

                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" x-model="kolomSkor" class="rounded border-line text-brand">
                        <span class="font-medium text-ink">Total Skor SVI &amp; Lencana Klasifikasi Kerentanan</span>
                    </label>
                </div>

                {{-- Kalimat Estimasi Dinamis (PRD 12.3) --}}
                <div class="p-3 bg-brand/5 border border-brand/20 rounded-lg text-xs flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span class="text-ink">
                        Laporan akan berisi <strong class="text-brand font-bold" x-text="estimasiJumlah"></strong> rumah tangga terverifikasi.
                    </span>
                </div>
            </div>
        </div>

        {{-- ============================================================
             DUA TOMBOL UNDUH DENGAN STATE 'MENYIAPKAN BERKAS' (PRD 12.3)
             ============================================================ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-line">
            {{-- Tombol PDF --}}
            <button type="button"
                    @click="unduhPdf"
                    :disabled="loadingPdf || loadingExcel || estimasiJumlah === 0"
                    class="card !p-4 flex flex-col items-center justify-center text-center group border-line hover:border-brand transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                <div class="h-10 w-10 rounded-full bg-brand/10 text-brand flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                    <template x-if="!loadingPdf">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </template>
                    <template x-if="loadingPdf">
                        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </template>
                </div>
                <span class="font-bold text-ink text-sm">
                    <span x-show="!loadingPdf">⤓ Unduh Format PDF</span>
                    <span x-show="loadingPdf">Menyiapkan berkas PDF...</span>
                </span>
                <span class="text-muted text-xs mt-1">Untuk dicetak dan diarsipkan sebagai lampiran keputusan kampung</span>
            </button>

            {{-- Tombol Excel --}}
            <button type="button"
                    @click="unduhExcel"
                    :disabled="loadingPdf || loadingExcel || estimasiJumlah === 0"
                    class="card !p-4 flex flex-col items-center justify-center text-center group border-line hover:border-rendah transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                <div class="h-10 w-10 rounded-full bg-rendah-bg text-rendah-tx flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                    <template x-if="!loadingExcel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h2"/><path d="M8 17h2"/><path d="M14 13h2"/><path d="M14 17h2"/></svg>
                    </template>
                    <template x-if="loadingExcel">
                        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </template>
                </div>
                <span class="font-bold text-ink text-sm">
                    <span x-show="!loadingExcel">⤓ Unduh Format Excel (.xlsx)</span>
                    <span x-show="loadingExcel">Menyiapkan berkas Excel...</span>
                </span>
                <span class="text-muted text-xs mt-1">Untuk diolah kembali dalam spreadsheet kependudukan atau dinas sosial</span>
            </button>
        </div>
    </div>

    {{-- ============================================================
         PRATINJAU TABEL DATA SESUAI KOLOM YANG DIPILIH
         ============================================================ --}}
    <div class="card overflow-hidden !p-0 shadow-sm">
        <div class="p-4 border-b border-line flex items-center justify-between">
            <div>
                <h3 class="text-section-title text-ink">Pratinjau Data Laporan</h3>
                <p class="text-help text-xs">Menampilkan contoh baris yang akan diekspor sesuai filter dan kolom aktif</p>
            </div>
            <span class="text-xs text-muted">
                Pratinjau <strong class="text-ink" x-text="filteredData.length"></strong> data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-paper border-b border-line text-xs font-semibold uppercase tracking-wider text-muted">
                    <tr>
                        <th class="py-3 px-4">Nama KK &amp; Alamat</th>
                        <th class="py-3 px-4">Wilayah</th>
                        <th class="py-3 px-4" x-show="kolomKoordinat">Koordinat (Lat, Lng)</th>
                        <th class="py-3 px-4" x-show="kolomSvi9">Skor 9 Variabel</th>
                        <th class="py-3 px-4 text-center" x-show="kolomSkor">Total Skor</th>
                        <th class="py-3 px-4" x-show="kolomSkor">Klasifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    <template x-for="item in filteredData" :key="item.nama">
                        <tr class="hover:bg-paper/40 transition-colors">
                            <td class="py-3 px-4">
                                <p class="font-bold text-ink" x-text="item.nama"></p>
                                <p class="text-xs text-muted" x-text="item.alamat"></p>
                            </td>
                            <td class="py-3 px-4 text-xs font-medium" x-text="item.jalur"></td>
                            <td class="py-3 px-4 text-xs tabular-nums text-muted font-mono" x-show="kolomKoordinat"
                                x-text="item.lat + ', ' + item.lng"></td>
                            <td class="py-3 px-4 text-xs tabular-nums text-muted font-mono" x-show="kolomSvi9"
                                x-text="item.svi"></td>
                            <td class="py-3 px-4 text-center font-bold tabular-nums" x-show="kolomSkor"
                                x-text="item.skor"></td>
                            <td class="py-3 px-4" x-show="kolomSkor">
                                <template x-if="item.klas === 'tinggi'">
                                    <x-badge-klasifikasi tingkat="tinggi" />
                                </template>
                                <template x-if="item.klas === 'sedang'">
                                    <x-badge-klasifikasi tingkat="sedang" />
                                </template>
                                <template x-if="item.klas === 'rendah'">
                                    <x-badge-klasifikasi tingkat="rendah" />
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================================
         RIWAYAT UNDUHAN BERKAS (PRD 12.3)
         ============================================================ --}}
    <div class="card space-y-3">
        <h3 class="text-section-title text-ink">Riwayat Berkas yang Diunduh</h3>
        <p class="text-help text-xs">Daftar arsip berkas rekapitulasi kerentanan yang pernah dihasilkan oleh sistem:</p>

        <div class="divide-y divide-line text-xs">
            <div class="py-2.5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="px-2 py-0.5 rounded font-bold text-xs bg-tinggi-bg text-tinggi-tx border border-tinggi/20">
                        PDF
                    </span>
                    <div>
                        <span class="font-bold text-ink">Rekap_Kerentanan_Sota_2026-09-04.pdf</span>
                        <span class="text-muted block text-[11px]">Seluruh Wilayah · 91 KK · 1,4 MB</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-muted block">4 Sep 2026, 09:12 WIT</span>
                    <button type="button" @click="unduhPdf" class="text-brand font-bold hover:underline text-[11px]">
                        Unduh Ulang
                    </button>
                </div>
            </div>

            <div class="py-2.5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="px-2 py-0.5 rounded font-bold text-xs bg-rendah-bg text-rendah-tx border border-rendah/20">
                        XLSX
                    </span>
                    <div>
                        <span class="font-bold text-ink">Data_SVI_Kampung_Sota_Agustus_2026.xlsx</span>
                        <span class="text-muted block text-[11px]">Seluruh Wilayah · 88 KK · 420 KB</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-muted block">30 Agu 2026, 14:05 WIT</span>
                    <button type="button" @click="unduhExcel" class="text-brand font-bold hover:underline text-[11px]">
                        Unduh Ulang
                    </button>
                </div>
            </div>

            <div class="py-2.5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="px-2 py-0.5 rounded font-bold text-xs bg-tinggi-bg text-tinggi-tx border border-tinggi/20">
                        PDF
                    </span>
                    <div>
                        <span class="font-bold text-ink">Daftar_Prioritas_Bansos_Jalur_A.pdf</span>
                        <span class="text-muted block text-[11px]">Jalur A (Kerentanan Tinggi) · 8 KK · 380 KB</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-muted block">15 Agu 2026, 11:40 WIT</span>
                    <button type="button" @click="unduhPdf" class="text-brand font-bold hover:underline text-[11px]">
                        Unduh Ulang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
