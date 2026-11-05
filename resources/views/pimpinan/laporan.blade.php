@extends('layouts.app', [
    'role' => 'kepala_kampung',
    'userName' => Auth::user()?->name ?? 'Conrada Murnan'
])

@section('title', 'Panel Laporan Rekapitulasi - SOTA-GIS')
@section('page-title', 'Panel Laporan Rekapitulasi')

@push('head')
<style>
/* ==========================================================================
   CSS PRINTING KHUSUS LEMBAR LAPORAN RESMI KAMPUNG SOTA
   - Mendukung multi-page otomatis (Page 1, 2, dst)
   - Otomatis adaptif ke berbagai ukuran kertas printer (A4, F4/Folio, Letter)
   - Menghilangkan scrollbar terpotong pada dialog browser print preview
   ========================================================================== */
@media print {
    @page {
        size: auto; /* Otomatis adaptif terhadap ukuran kertas di dialog printer: A4, F4 Folio, Letter */
        margin: 12mm 10mm 15mm 10mm;
    }

    html, body {
        height: auto !important;
        min-height: 0 !important;
        overflow: visible !important;
        background: #ffffff !important;
        color: #000000 !important;
        font-family: 'Times New Roman', Times, serif !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Sembunyikan semua elemen navigasi, sidebar, topbar header, dan kontrol non-cetak */
    aside,
    header,
    nav,
    footer,
    .skip-link,
    #toast-container,
    .no-print,
    .no-print *,
    .print-modal-header,
    [x-cloak] {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Sembunyikan semua elemen halaman kecuali modal lembar resmi */
    [x-data="panelLaporan()"] > *:not(.print-modal-backdrop) {
        display: none !important;
    }

    /* Buka container ancestor khusus dari print-modal-backdrop agar multi-page tidak terpotong */
    body > div,
    body > div > div,
    main,
    main > div,
    [x-data="panelLaporan()"],
    .print-modal-backdrop,
    .print-modal-dialog,
    .print-modal-scroll {
        position: static !important;
        display: block !important;
        width: 100% !important;
        height: auto !important;
        min-height: 0 !important;
        max-height: none !important;
        overflow: visible !important;
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        background: transparent !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    #lembar-laporan-resmi {
        display: block !important;
        position: static !important;
        width: 100% !important;
        max-width: 100% !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border: none !important;
        background: #ffffff !important;
        overflow: visible !important;
    }

    /* Pengaturan tabel agar pecah rapi antar halaman (multi-page) */
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        page-break-inside: auto !important;
        break-inside: auto !important;
    }

    tr {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        page-break-after: auto !important;
        break-after: auto !important;
    }

    thead {
        display: table-header-group !important; /* Header kolom otomatis berulang di halaman selanjutnya */
    }

    tfoot {
        display: table-footer-group !important;
    }

    .kop-surat-section,
    .ringkasan-box-section,
    .lembar-ttd-section {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    .lembar-ttd-section {
        margin-top: 28px !important;
    }
}
</style>
@endpush

@php
    $dataWargaJson = json_encode(($dataWarga ?? collect([]))->map(fn($w) => [
        'id' => $w->id,
        'nama' => $w->nama_kk,
        'jalur' => $w->jalur,
        'status_penduduk' => $w->status_penduduk ?? 'Tetap',
        'rt_rw' => 'RT ' . ($w->rt ?? '-') . ' / RW ' . ($w->rw ?? '-'),
        'alamat' => 'RT ' . ($w->rt ?? '-') . ' / RW ' . ($w->rw ?? '-') . ($w->no_rumah ? ' No. ' . $w->no_rumah : ''),
        'lat' => $w->latitude,
        'lng' => $w->longitude,
        'skor' => $w->skor_svi,
        'klas' => strtolower($w->klasifikasi),
        'klasLabel' => 'Kerentanan ' . ucfirst($w->klasifikasi)
    ])->values());
@endphp

@section('content')
<div x-data="panelLaporan()" class="space-y-6">

    {{-- Toast Notifikasi Melayang --}}
    <div x-show="toastVisible"
         x-transition
         class="fixed top-4 right-4 z-[100] max-w-sm no-print"
         style="display: none;"
         x-cloak>
        <x-toast ::type="toastType" ::message="toastMessage" />
    </div>

    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-line no-print">
        <div>
            <h2 class="text-xl font-bold text-ink">Unduh Laporan Rekapitulasi</h2>
            <p class="text-help text-xs mt-0.5">Penyusunan berkas cetak resmi kedinasan dan spreadsheet digital rekapitulasi kerentanan warga Kampung Sota untuk keperluan arsip kampung, penetapan sasaran bansos, dan koordinasi dengan Dinas Sosial.</p>
        </div>
    </div>

    {{-- ============================================================
         KARTU PENGATURAN FILTER & KOLOM LAPORAN (PRD 12.3)
         ============================================================ --}}
    <div class="card space-y-6 no-print">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-line pb-3">
            <div>
                <h3 class="text-section-title text-ink">Konfigurasi Berkas Laporan</h3>
                <p class="text-xs text-muted mt-0.5">Pilih wilayah, klasifikasi, dan kolom data yang ingin disertakan dalam berkas Excel maupun PDF resmi</p>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200/90 shadow-xs self-start sm:self-auto">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span><strong class="font-extrabold text-emerald-900" x-text="filteredData.length"></strong> Data Siap Cetak</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">
            {{-- 1. Filter Wilayah, Status Kependudukan & Tingkat Kerentanan --}}
            <div class="flex flex-col justify-between space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Dropdown Wilayah --}}
                    <div>
                        <label class="block text-label text-ink mb-1.5 font-semibold text-xs">Wilayah Permukiman</label>
                        <select x-model="wilayah" class="input-select text-xs font-medium">
                            <option value="semua">Semua Wilayah</option>
                            <option value="Jalur A">Jalur A Saja (Jalur 1A - 6A)</option>
                            <option value="Jalur B">Jalur B Saja (Jalur 1B - 7B)</option>
                            <option value="Poros Trans">Jalan Poros Trans Papua</option>
                        </select>
                    </div>

                    {{-- Dropdown Status Kependudukan --}}
                    <div>
                        <label class="block text-label text-ink mb-1.5 font-semibold text-xs">Status Kependudukan</label>
                        <select x-model="statusPenduduk" class="input-select text-xs font-medium">
                            <option value="semua">Semua Status</option>
                            <option value="Tetap">Warga Tetap</option>
                            <option value="Tidak Tetap">Warga Tidak Tetap</option>
                        </select>
                    </div>
                </div>

                {{-- Checkbox Tingkat Kerentanan dengan Warna Tegas (Tanpa icon di judul & tanpa pill di kanan) --}}
                <div class="flex-1 flex flex-col justify-end">
                    <label class="block text-label text-ink mb-2 font-semibold">Tingkat Kerentanan yang Disertakan</label>
                    <div class="space-y-2.5">
                        {{-- 1. Kerentanan Rendah --}}
                        <label class="flex items-center gap-3 p-2.5 rounded-xl border transition-all cursor-pointer select-none"
                               :class="tingkatRendah ? 'border-emerald-400 bg-emerald-50/80 shadow-xs' : 'border-line/70 bg-paper/40 hover:bg-paper opacity-70'">
                            <input type="checkbox" x-model="tingkatRendah" class="rounded border-emerald-400 text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                            <span class="text-xs font-bold text-ink flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-xs"></span>
                                Kerentanan Rendah
                            </span>
                        </label>

                        {{-- 2. Kerentanan Sedang --}}
                        <label class="flex items-center gap-3 p-2.5 rounded-xl border transition-all cursor-pointer select-none"
                               :class="tingkatSedang ? 'border-amber-400 bg-amber-50/80 shadow-xs' : 'border-line/70 bg-paper/40 hover:bg-paper opacity-70'">
                            <input type="checkbox" x-model="tingkatSedang" class="rounded border-amber-400 text-amber-600 focus:ring-amber-500 h-4 w-4">
                            <span class="text-xs font-bold text-ink flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-amber-500 shadow-xs"></span>
                                Kerentanan Sedang
                            </span>
                        </label>

                        {{-- 3. Kerentanan Tinggi --}}
                        <label class="flex items-center gap-3 p-2.5 rounded-xl border transition-all cursor-pointer select-none"
                               :class="tingkatTinggi ? 'border-rose-400 bg-rose-50/80 shadow-xs' : 'border-line/70 bg-paper/40 hover:bg-paper opacity-70'">
                            <input type="checkbox" x-model="tingkatTinggi" class="rounded border-rose-400 text-rose-600 focus:ring-rose-500 h-4 w-4">
                            <span class="text-xs font-bold text-ink flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-600 shadow-xs"></span>
                                Kerentanan Tinggi <span class="text-tinggi-tx font-bold">(Prioritas Bansos)</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- 2. Pilihan Kolom yang Disertakan & Ringkasan Seimbang --}}
            <div class="flex flex-col justify-between space-y-4">
                <div>
                    <label class="block text-label text-ink mb-1.5 font-semibold">Kolom yang Ditampilkan dalam Tabel</label>
                    <div class="bg-paper p-3 rounded-xl border border-line space-y-2 text-xs">
                        <label class="flex items-center gap-2.5 cursor-not-allowed text-muted select-none">
                            <input type="checkbox" checked disabled class="rounded border-line text-brand h-4 w-4">
                            <span class="font-bold text-ink">Identitas &amp; Alamat Lengkap (Wajib)</span>
                            <span class="text-[10px] text-muted ml-auto">(Nama KK, Jalur, RT/RW, Alamat)</span>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer select-none hover:text-brand transition-colors">
                            <input type="checkbox" x-model="kolomKoordinat" class="rounded border-line text-brand h-4 w-4">
                            <span class="font-medium text-ink">Titik Koordinat (Latitude &amp; Longitude WGS84)</span>
                        </label>


                        <label class="flex items-center gap-2.5 cursor-pointer select-none hover:text-brand transition-colors">
                            <input type="checkbox" x-model="kolomSkor" class="rounded border-line text-brand h-4 w-4">
                            <span class="font-medium text-ink">Total Skor SVI &amp; Lencana Klasifikasi Kerentanan</span>
                        </label>
                    </div>
                </div>

                {{-- Box Ringkasan Cepat Total Data ("28 Kepala Keluarga") --}}
                <div class="p-3 bg-gradient-to-br from-brand/5 via-surface to-brand/10 border border-brand/20 rounded-xl text-xs space-y-2 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-brand/10 text-brand flex items-center justify-center font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div>
                                <p class="text-[11px] text-muted font-medium">Laporan Terpilih</p>
                                <h4 class="text-sm font-extrabold text-brand" x-text="ringkasanTotal.totalKK + ' Kepala Keluarga'"></h4>
                            </div>
                        </div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-muted px-2 py-0.5 rounded bg-surface border border-line">
                            Rekap Terkini
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 pt-2 border-t border-brand/10 text-center">
                        <div class="bg-rose-50 border border-rose-200/80 rounded-lg p-1.5">
                            <span class="text-[10px] text-rose-700 font-semibold block">Tinggi</span>
                            <span class="text-xs font-extrabold text-rose-900" x-text="ringkasanTotal.tinggi + ' KK'"></span>
                        </div>
                        <div class="bg-amber-50 border border-amber-200/80 rounded-lg p-1.5">
                            <span class="text-[10px] text-amber-700 font-semibold block">Sedang</span>
                            <span class="text-xs font-extrabold text-amber-900" x-text="ringkasanTotal.sedang + ' KK'"></span>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-200/80 rounded-lg p-1.5">
                            <span class="text-[10px] text-emerald-700 font-semibold block">Rendah</span>
                            <span class="text-xs font-extrabold text-emerald-900" x-text="ringkasanTotal.rendah + ' KK'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================
             DUA TOMBOL AKSI UTAMA (UNDUH EXCEL & UNDUH PDF RESMI)
             ============================================================ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-line">
            {{-- Tombol PDF --}}
            <button type="button"
                    @click="unduhPdf"
                    :disabled="loadingPdf || loadingExcel || filteredData.length === 0"
                    class="card !p-4 flex flex-col items-center justify-center text-center group border-line hover:border-brand hover:shadow-md transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                <div class="h-11 w-11 rounded-full bg-brand/10 text-brand flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                    <template x-if="!loadingPdf">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/><path d="M6 9V2h12v7"/></svg>
                    </template>
                    <template x-if="loadingPdf">
                        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </template>
                </div>
                <span class="font-bold text-ink text-sm">
                    <span x-show="!loadingPdf">Unduh Format PDF (Siap Cetak Resmi)</span>
                    <span x-show="loadingPdf">Menyiapkan Lembar Cetak Resmi...</span>
                </span>
                <span class="text-muted text-xs mt-1">Dokumen dinas resmi Kampung Sota lengkap dengan KOP surat ganda &amp; lembar tanda tangan</span>
            </button>

            {{-- Tombol Excel --}}
            <button type="button"
                    @click="unduhExcel"
                    :disabled="loadingPdf || loadingExcel || filteredData.length === 0"
                    class="card !p-4 flex flex-col items-center justify-center text-center group border-line hover:border-rendah hover:shadow-md transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                <div class="h-11 w-11 rounded-full bg-rendah-bg text-rendah-tx flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                    <template x-if="!loadingExcel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h2"/><path d="M8 17h2"/><path d="M14 13h2"/><path d="M14 17h2"/></svg>
                    </template>
                    <template x-if="loadingExcel">
                        <svg class="animate-spin" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    </template>
                </div>
                <span class="font-bold text-ink text-sm">
                    <span x-show="!loadingExcel">Unduh Format Excel (.xls)</span>
                    <span x-show="loadingExcel">Mengunduh Berkas Spreadsheet...</span>
                </span>
                <span class="text-muted text-xs mt-1">Langsung unduh Rekapitulasi_SVI_Kampung_Sota_2026.xls dengan rincian total di bagian atas</span>
            </button>
        </div>
    </div>

    {{-- ============================================================
         PRATINJAU TABEL DATA SESUAI KOLOM YANG DIPILIH
         ============================================================ --}}
    <div class="card overflow-hidden !p-0 shadow-sm no-print">
        <div class="p-4 border-b border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-section-title text-ink">Pratinjau Data Laporan</h3>
                <p class="text-help text-xs">Menampilkan data responden yang akan disertakan dalam berkas Excel dan PDF resmi</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-muted">
                    Menampilkan <strong class="text-ink" x-text="filteredData.length === 0 ? 0 : pagedData.length"></strong> dari <strong class="text-ink" x-text="filteredData.length"></strong> baris data
                </span>
                <button type="button" @click="showPrintModal = true" class="btn-secondary !min-h-[32px] !py-1 !px-3 !text-xs font-semibold">Lihat Lembar Cetak Resmi</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-paper border-b border-line text-xs font-semibold uppercase tracking-wider text-muted">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama KK &amp; Alamat</th>
                        <th class="py-3 px-4">Jalur Detail</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4" x-show="kolomKoordinat">Koordinat (Lat, Lng)</th>
                        <th class="py-3 px-4 text-center" x-show="kolomSkor">Total Skor</th>
                        <th class="py-3 px-4" x-show="kolomSkor">Klasifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    <template x-for="(item, idx) in pagedData" :key="item.id || idx">
                        <tr class="hover:bg-paper/40 transition-colors">
                            <td class="py-3 px-4 text-center font-bold text-muted text-xs" x-text="(currentPage - 1) * perPage + idx + 1"></td>
                            <td class="py-3 px-4">
                                <p class="font-bold text-ink" x-text="item.nama"></p>
                                <p class="text-xs text-muted" x-text="item.alamat"></p>
                            </td>
                            <td class="py-3 px-4 text-xs font-medium">
                                <span class="inline-flex items-center px-2 py-0.5 rounded font-bold text-[11px] bg-brand/10 text-brand" x-text="item.jalur"></span>
                            </td>
                            <td class="py-3 px-4 text-xs">
                                <span class="inline-flex items-center px-2 py-0.5 rounded font-medium text-[11px]"
                                      :class="item.status_penduduk === 'Tetap' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200'"
                                      x-text="item.status_penduduk"></span>
                            </td>
                            <td class="py-3 px-4 text-xs tabular-nums text-muted font-mono" x-show="kolomKoordinat"
                                x-text="item.lat + ', ' + item.lng"></td>
                            <td class="py-3 px-4 text-center tabular-nums" x-show="kolomSkor">
                                <span class="font-bold text-ink block" x-text="item.skor"></span>
                                <span class="text-[10px] text-muted block font-normal" x-text="'(rata-rata ' + (item.skor / 9).toFixed(2).replace('.', ',') + ')'"></span>
                            </td>
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

        {{-- Pagination Footer Bar (SS3) --}}
        <div class="p-4 border-t border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs bg-paper/20">
            <div class="text-muted">
                Menampilkan <strong class="text-ink font-semibold" x-text="filteredData.length === 0 ? 0 : pagedData.length"></strong> dari <strong class="text-ink font-semibold" x-text="filteredData.length"></strong> total data warga (Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages"></span>)
            </div>
            <div class="flex items-center gap-1.5 self-center sm:self-auto" x-show="totalPages > 1">
                <button type="button" 
                        @click="currentPage = Math.max(1, currentPage - 1)" 
                        :disabled="currentPage === 1"
                        class="px-2.5 py-1 rounded-lg border border-line bg-white hover:bg-paper disabled:opacity-40 disabled:cursor-not-allowed font-medium text-ink transition-colors">
                    &lsaquo;
                </button>
                
                <template x-for="p in paginationPages" :key="p">
                    <div class="inline-flex">
                        <template x-if="p === '...'">
                            <span class="px-2 py-1 text-muted">...</span>
                        </template>
                        <template x-if="p !== '...'">
                            <button type="button"
                                    @click="currentPage = p"
                                    :class="currentPage === p ? 'bg-brand text-white font-bold border-brand' : 'bg-white text-ink hover:bg-paper border-line font-medium'"
                                    class="min-w-[32px] h-8 px-2 rounded-lg border text-xs flex items-center justify-center transition-colors"
                                    x-text="p">
                            </button>
                        </template>
                    </div>
                </template>
                
                <button type="button" 
                        @click="currentPage = Math.min(totalPages, currentPage + 1)" 
                        :disabled="currentPage === totalPages"
                        class="px-2.5 py-1 rounded-lg border border-line bg-white hover:bg-paper disabled:opacity-40 disabled:cursor-not-allowed font-medium text-ink transition-colors">
                    &rsaquo;
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL & LEMBAR LAPORAN RESMI KAMPUNG SOTA SIAP CETAK (PDF)
         ============================================================ --}}
    <div x-show="showPrintModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-ink/70 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4 print-modal-backdrop"
         style="display: none;"
         x-cloak>

        <div class="relative bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[92vh] flex flex-col overflow-hidden my-auto border border-line print-modal-dialog"
             @click.away="showPrintModal = false">

            {{-- Bar Kontrol Atas Modal (Disembunyikan saat cetak) --}}
            <div class="p-4 bg-paper border-b border-line flex flex-col sm:flex-row sm:items-center justify-between gap-3 print-modal-header no-print">
                <div class="flex items-center gap-3">
                    
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-ink text-sm">Lembar Laporan Resmi Kampung Sota</h4>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-brand/10 text-brand border border-brand/20">Adaptif: A4 / F4 Folio / Letter</span>
                        </div>
                        <p class="text-muted text-[11px] mt-0.5">Format resmi dinas dengan KOP ganda, rincian statistik kerentanan, dan legalitas tanda tangan</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <button type="button"
                            onclick="window.print()"
                            class="btn-primary !min-h-[36px] !py-1.5 !px-4 !text-xs font-bold shadow-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/><path d="M6 9V2h12v7"/></svg>
                        <span>Cetak / Simpan PDF Sekarang</span>
                    </button>
                    <button type="button"
                            @click="showPrintModal = false"
                            class="btn-secondary !min-h-[36px] !py-1.5 !px-3 !text-xs font-semibold">
                        Tutup
                    </button>
                </div>
            </div>

            {{-- Area Fisik Lembar Kerja Resmi A4/Folio/Letter --}}
            <div class="overflow-y-auto p-6 sm:p-10 bg-slate-100 flex justify-center print-modal-scroll">
                <div id="lembar-laporan-resmi"
                     class="bg-white text-black p-8 sm:p-12 shadow-md w-full max-w-[210mm] min-h-[297mm] text-xs font-serif leading-normal"
                     style="font-family: 'Times New Roman', Times, serif;">

                    {{-- 1. KOP SURAT LAPORAN INTERNAL KAMPUNG SOTA (SESUAI SS1 TANPA LOGO) --}}
                    <div class="kop-surat-section text-center pt-2 pb-1 leading-tight">
                        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-wider uppercase" style="font-family: 'Times New Roman', Times, serif; font-size: 24pt;">KANTOR KAMPUNG SOTA</h1>
                        <p class="text-xs sm:text-sm mt-2" style="font-family: 'Times New Roman', Times, serif; font-size: 10pt;">Jalan Poros PLBN Sota, Distrik Sota, Kabupaten Merauke, Provinsi Papua Selatan 99644</p>
                    </div>

                    {{-- 2. GARIS GANDA KOP SURAT (TEBAL & TIPIS) --}}
                    <div class="my-3">
                        <div style="border-top: 3.5px solid #000000;"></div>
                        <div style="border-top: 1.5px solid #000000; margin-top: 2.5px;"></div>
                    </div>

                    {{-- 3. JUDUL LAPORAN RESMI (SESUAI SS1) --}}
                    <div class="text-center my-4">
                        <h2 class="font-bold tracking-wide uppercase underline" style="font-family: 'Times New Roman', Times, serif; font-size: 11pt;">
                            LAPORAN REKAPITULASI HASIL PENGUKURAN INDEKS KERENTANAN SOSIAL EKONOMI (SVI)
                        </h2>
                        <p class="text-[11px] text-gray-800 mt-2" style="font-family: 'Times New Roman', Times, serif;">
                            Cakupan: <span class="font-bold" x-text="getWilayahText()"></span> &bull; Tanggal Penetapan: <strong>{{ $tanggalLaporan ?? now("Asia/Jayapura")->translatedFormat("d F Y") }}</strong>
                        </p>
                    </div>

                    {{-- 4. BAGIAN ATAS LAPORAN: RINCIAN TOTAL STATISTIK (SESUAI PERMINTAAN) --}}
                    <div class="my-4 ringkasan-box-section">
                        <div class="font-bold text-[11px] uppercase mb-1">A. RINGKASAN REKAPITULASI KERENTANAN</div>
                        <table class="w-full text-xs border-collapse" style="border: 1px solid #000000;">
                            <thead>
                                <tr style="background-color: #f2f2f2;">
                                    <th style="border: 1px solid #000000; padding: 6px; text-align: center;">Total Kepala Keluarga</th>
                                    <th style="border: 1px solid #000000; padding: 6px; text-align: center;">Kerentanan Tinggi (Prioritas Bansos)</th>
                                    <th style="border: 1px solid #000000; padding: 6px; text-align: center;">Kerentanan Sedang</th>
                                    <th style="border: 1px solid #000000; padding: 6px; text-align: center;">Kerentanan Rendah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="text-align: center; font-weight: bold; font-size: 11pt;">
                                    <td style="border: 1px solid #000000; padding: 8px;" x-text="ringkasanTotal.totalKK + ' KK'"></td>
                                    <td style="border: 1px solid #000000; padding: 8px; color: #991B1B;" x-text="ringkasanTotal.tinggi + ' KK'"></td>
                                    <td style="border: 1px solid #000000; padding: 8px; color: #92400E;" x-text="ringkasanTotal.sedang + ' KK'"></td>
                                    <td style="border: 1px solid #000000; padding: 8px; color: #166534;" x-text="ringkasanTotal.rendah + ' KK'"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- 5. TABEL REKAPITULASI DATA SVI LENGKAP --}}
                    <div class="my-4">
                        <div class="font-bold text-[11px] uppercase mb-1">B. RINCIAN DATA RESPONDEN KEPALA KELUARGA</div>
                        <table class="w-full text-[10px] border-collapse" style="border: 1px solid #000000;">
                            <thead>
                                <tr style="background-color: #f2f2f2; text-align: center; font-weight: bold;">
                                    <th style="border: 1px solid #000000; padding: 5px; width: 28px;">No</th>
                                    <th style="border: 1px solid #000000; padding: 5px; text-align: left;">Nama Kepala Keluarga</th>
                                    <th style="border: 1px solid #000000; padding: 5px; width: 65px;">Jalur Detail</th>
                                    <th style="border: 1px solid #000000; padding: 5px; width: 65px;">Status</th>
                                    <th style="border: 1px solid #000000; padding: 5px; text-align: left;">RT / RW &amp; Alamat</th>
                                    <th style="border: 1px solid #000000; padding: 5px;" x-show="kolomKoordinat">Titik Koordinat</th>
                                    <th style="border: 1px solid #000000; padding: 5px; width: 45px;" x-show="kolomSkor">Skor</th>
                                    <th style="border: 1px solid #000000; padding: 5px; width: 90px;" x-show="kolomSkor">Klasifikasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, idx) in filteredData" :key="'print-' + (item.id || idx)">
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 4px; text-align: center;" x-text="idx + 1"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; font-weight: bold;" x-text="item.nama"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; text-align: center; font-weight: bold;" x-text="item.jalur"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; text-align: center;" x-text="item.status_penduduk"></td>
                                        <td style="border: 1px solid #000000; padding: 4px;" x-text="item.alamat"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; font-family: monospace; font-size: 8.5px; text-align: center;" x-show="kolomKoordinat" x-text="item.lat + ', ' + item.lng"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; text-align: center; font-weight: bold;" x-show="kolomSkor" x-text="item.skor"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; text-align: center; font-weight: bold;"
                                            :style="item.klas === 'tinggi' ? 'color: #991B1B;' : (item.klas === 'sedang' ? 'color: #92400E;' : 'color: #166534;')"
                                            x-show="kolomSkor"
                                            x-text="item.klasLabel"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    {{-- 6. LEMBAR TANDA TANGAN RESMI KEPALA KAMPUNG SOTA --}}
                    <div class="mt-8 pt-4 flex justify-end text-xs lembar-ttd-section" style="page-break-inside: avoid;">
                        <div class="text-center w-72 leading-relaxed">
                            <p>Sota, {{ $tanggalLaporan ?? now("Asia/Jayapura")->translatedFormat("d F Y") }}</p>
                            <p class="font-bold">Mengetahui,</p>
                            <p class="font-bold uppercase">Kepala Kampung Sota</p>

                            {{-- Ruang Cap Stempel Dinas & Tanda Tangan --}}
                            <div class="relative h-24 flex items-center justify-center my-1">
                                {{-- Lingkaran Stempel Basah Dinas Kampung Sota --}}
                                <div class="absolute w-28 h-28 rounded-full border-2 border-dashed border-indigo-900/60 flex items-center justify-center text-indigo-900/80 text-[8.5px] font-bold uppercase rotate-[-12deg] pointer-events-none select-none text-center leading-tight p-1"
                                     style="font-family: sans-serif;">
                                    PEMERINTAH KABUPATEN MERAUKE<br>* DISTRIK SOTA *<br>KAMPUNG SOTA
                                </div>
                            </div>

                            <p class="font-bold underline text-sm tracking-wide uppercase">CONRADA MURNAN</p>
                            <p class="text-[11px]">NIP. -</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function panelLaporan() {
    return {
        // Filter State
        wilayah: 'semua',
        statusPenduduk: 'semua',
        tingkatRendah: true,
        tingkatSedang: true,
        tingkatTinggi: true,

        // Kolom State
        kolomIdentitas: true,
        kolomKoordinat: true,
        kolomSkor: true,

        // Pagination State (SS3)
        currentPage: 1,
        perPage: 10,

        get totalPages() {
            return Math.max(1, Math.ceil(this.filteredData.length / this.perPage));
        },

        get pagedData() {
            const start = (this.currentPage - 1) * this.perPage;
            return this.filteredData.slice(start, start + this.perPage);
        },

        get paginationPages() {
            const total = this.totalPages;
            const current = this.currentPage;
            if (total <= 7) {
                return Array.from({length: total}, (_, i) => i + 1);
            }
            const pages = [];
            if (current <= 4) {
                for (let i = 1; i <= 5; i++) pages.push(i);
                pages.push('...');
                pages.push(total);
            } else if (current >= total - 3) {
                pages.push(1);
                pages.push('...');
                for (let i = total - 4; i <= total; i++) pages.push(i);
            } else {
                pages.push(1);
                pages.push('...');
                pages.push(current - 1);
                pages.push(current);
                pages.push(current + 1);
                pages.push('...');
                pages.push(total);
            }
            return pages;
        },

        // Modal & Download State
        loadingPdf: false,
        loadingExcel: false,
        showPrintModal: false,
        tanggalCetakText: '{{ $tanggalLaporan ?? now("Asia/Jayapura")->translatedFormat("d F Y") }}',

        // Toast State
        toastVisible: false,
        toastType: 'berhasil',
        toastMessage: '',

        showToast(type, msg) {
            this.toastType = type;
            this.toastMessage = msg;
            this.toastVisible = true;
            setTimeout(() => { this.toastVisible = false; }, 4000);
        },
        // Dataset Responden Kampung Sota (Terverifikasi Lapangan dari Database)
        dataWarga: {!! $dataWargaJson !!},

        // Filter Data Sesuai Opsi
        get filteredData() {
            const data = this.dataWarga.filter(item => {
                const matchWilayah = this.wilayah === 'semua' || 
                                     item.jalur === this.wilayah ||
                                     (this.wilayah === 'Jalur A' && item.jalur.includes('A')) ||
                                     (this.wilayah === 'Jalur B' && item.jalur.includes('B')) ||
                                     (this.wilayah === 'Poros Trans' && item.jalur.includes('Poros'));
                const matchStatus = this.statusPenduduk === 'semua' || item.status_penduduk === this.statusPenduduk;
                const matchKlas = (this.tingkatRendah && item.klas === 'rendah') ||
                                  (this.tingkatSedang && item.klas === 'sedang') ||
                                  (this.tingkatTinggi && item.klas === 'tinggi');
                return matchWilayah && matchStatus && matchKlas;
            });
            const maxPage = Math.max(1, Math.ceil(data.length / this.perPage));
            if (this.currentPage > maxPage) {
                this.currentPage = 1;
            }
            return data;
        },

        // Ringkasan Total Laporan (Bagian Atas Laporan Sesuai Permintaan)
        get ringkasanTotal() {
            const list = this.filteredData;
            return {
                totalKK: list.length,
                tinggi: list.filter(d => d.klas === 'tinggi').length,
                sedang: list.filter(d => d.klas === 'sedang').length,
                rendah: list.filter(d => d.klas === 'rendah').length,
            };
        },

        getWilayahText() {
            let label = 'Seluruh Wilayah Kampung Sota (Jalur A, Jalur B & Poros Trans)';
            if (this.wilayah === 'Jalur A') label = 'Wilayah Jalur A (Jalur 1A - 6A)';
            else if (this.wilayah === 'Jalur B') label = 'Wilayah Jalur B (Jalur 1B - 7B)';
            else if (this.wilayah === 'Poros Trans') label = 'Wilayah Jalan Poros Trans Papua';

            if (this.statusPenduduk !== 'semua') {
                label += ' - ' + (this.statusPenduduk === 'Tetap' ? 'Warga Tetap' : 'Warga Tidak Tetap');
            }
            return label;
        },

        // =====================================================================
        // 1. UNDUH EXCEL BERFUNGSI PENUH (.XLS DENGAN RINGKASAN TOTAL DI ATAS)
        // =====================================================================
        unduhExcel() {
            if (this.loadingExcel || this.filteredData.length === 0) return;
            this.loadingExcel = true;

            setTimeout(() => {
                try {
                    const list = this.filteredData;
                    const ringkas = this.ringkasanTotal;
                    const wilayahLabel = this.getWilayahText();

                    // Hitung jumlah kolom aktif untuk colspan header
                    let totalKolom = 5; // No, Nama, Jalur, Status, RT/RW & Alamat
                    if (this.kolomKoordinat) totalKolom += 2; // Latitude, Longitude
                    if (this.kolomSkor) totalKolom += 2;      // Total Skor, Klasifikasi

                    let tableHeaderCols = '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: center;">No</th>' +
                                          '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: left;">Nama Kepala Keluarga</th>' +
                                          '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: center;">Jalur Detail</th>' +
                                          '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: center;">Status Kependudukan</th>' +
                                          '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: left;">RT / RW & Alamat Lengkap</th>';

                    if (this.kolomKoordinat) {
                        tableHeaderCols += '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: center;">Latitude</th>' +
                                           '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: center;">Longitude</th>';
                    }

                    if (this.kolomSkor) {
                        tableHeaderCols += '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: center;">Total Skor SVI</th>' +
                                           '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: center;">Klasifikasi Kerentanan</th>';
                    }

                    let tableRows = '';
                    list.forEach((item, index) => {
                        let klasBg = '#DCFCE7';
                        let klasColor = '#166534';
                        if (item.klas === 'tinggi') {
                            klasBg = '#FEE2E2';
                            klasColor = '#991B1B';
                        } else if (item.klas === 'sedang') {
                            klasBg = '#FEF3C7';
                            klasColor = '#92400E';
                        }

                        tableRows += '<tr>' +
                            '<td style="border: 1px solid #CBD5E1; text-align: center; padding: 6px;">' + (index + 1) + '</td>' +
                            '<td style="border: 1px solid #CBD5E1; padding: 6px; font-weight: bold;">' + item.nama + '</td>' +
                            '<td style="border: 1px solid #CBD5E1; text-align: center; padding: 6px; font-weight: bold;">' + item.jalur + '</td>' +
                            '<td style="border: 1px solid #CBD5E1; text-align: center; padding: 6px;">' + item.status_penduduk + '</td>' +
                            '<td style="border: 1px solid #CBD5E1; padding: 6px;">' + item.alamat + '</td>';

                        if (this.kolomKoordinat) {
                            tableRows += '<td style="border: 1px solid #CBD5E1; text-align: center; padding: 6px; font-family: monospace;">' + item.lat + '</td>' +
                                         '<td style="border: 1px solid #CBD5E1; text-align: center; padding: 6px; font-family: monospace;">' + item.lng + '</td>';
                        }

                        if (this.kolomSkor) {
                            tableRows += '<td style="border: 1px solid #CBD5E1; text-align: center; padding: 6px; font-weight: bold;">' + item.skor + '</td>' +
                                         '<td style="border: 1px solid #CBD5E1; text-align: center; padding: 6px; font-weight: bold; background-color: ' + klasBg + '; color: ' + klasColor + ';">' + item.klasLabel + '</td>';
                        }

                        tableRows += '</tr>';
                    });

                    // Susun Dokumen HTML Spreadsheet Excel
                    const excelHtml = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">' +
                        '<head>' +
                            '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' +
                            '<style>' +
                                'body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; }' +
                                '.kop-main { font-size: 14pt; font-weight: bold; text-align: center; }' +
                                '.kop-sub { font-size: 12pt; font-weight: bold; text-align: center; }' +
                                '.kop-title { font-size: 11pt; font-weight: bold; text-align: center; }' +
                                '.kop-desc { font-size: 9pt; text-align: center; color: #555555; }' +
                                '.box-ringkas { border: 1px solid #000000; border-collapse: collapse; }' +
                                '.box-ringkas th { background-color: #1E232A; color: #FFFFFF; font-weight: bold; padding: 6px; }' +
                                '.box-ringkas td { padding: 6px; border: 1px solid #CBD5E1; font-weight: bold; }' +
                            '</style>' +
                        '</head>' +
                        '<body>' +
                            '<table style="width: 100%;">' +
                                '<tr><td colspan="' + totalKolom + '" class="kop-main">KANTOR KAMPUNG SOTA</td></tr>' +
                                '<tr><td colspan="' + totalKolom + '" class="kop-sub">DISTRIK SOTA - KAMPUNG SOTA</td></tr>' +
                                '<tr><td colspan="' + totalKolom + '" class="kop-title">LAPORAN REKAPITULASI HASIL PENGUKURAN INDEKS KERENTANAN SOSIAL EKONOMI (SVI)</td></tr>' +
                                '<tr><td colspan="' + totalKolom + '" class="kop-desc">Cakupan: ' + wilayahLabel + ' | Tanggal Ekspor: ' + this.tanggalCetakText + '</td></tr>' +
                                '<tr><td colspan="' + totalKolom + '"></td></tr>' +
                            '</table>' +
                            '<table class="box-ringkas" style="width: 100%; margin-bottom: 16px;">' +
                                '<tr style="background-color: #1E232A; color: #FFFFFF;">' +
                                    '<th colspan="2" style="padding: 8px; font-size: 11pt; text-align: left; background-color: #1E232A; color: #FFFFFF;">RINGKASAN REKAPITULASI KERENTANAN SOSIAL EKONOMI (SVI)</th>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td style="width: 60%; background-color: #F8FAFC; padding: 6px;">Total Kepala Keluarga Terdata</td>' +
                                    '<td style="text-align: center; font-size: 11pt; padding: 6px;">' + ringkas.totalKK + ' Kepala Keluarga</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td style="background-color: #FEF2F2; color: #991B1B; padding: 6px;">Kerentanan Tinggi (Prioritas Intervensi Bansos)</td>' +
                                    '<td style="text-align: center; font-size: 11pt; color: #991B1B; background-color: #FEF2F2; padding: 6px;">' + ringkas.tinggi + ' KK</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td style="background-color: #FFFBEB; color: #92400E; padding: 6px;">Kerentanan Sedang</td>' +
                                    '<td style="text-align: center; font-size: 11pt; color: #92400E; background-color: #FFFBEB; padding: 6px;">' + ringkas.sedang + ' KK</td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<td style="background-color: #F0FDF4; color: #166534; padding: 6px;">Kerentanan Rendah</td>' +
                                    '<td style="text-align: center; font-size: 11pt; color: #166534; background-color: #F0FDF4; padding: 6px;">' + ringkas.rendah + ' KK</td>' +
                                '</tr>' +
                            '</table>' +
                            '<br>' +
                            '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">' +
                                '<thead><tr>' + tableHeaderCols + '</tr></thead>' +
                                '<tbody>' + tableRows + '</tbody>' +
                            '</table>' +
                            '<br><br>' +
                            '<table style="width: 100%;">' +
                                '<tr>' +
                                    '<td colspan="' + Math.max(1, totalKolom - 2) + '"></td>' +
                                    '<td colspan="2" style="text-align: center;">' +
                                        'Sota, ' + this.tanggalCetakText + '<br>' +
                                        '<strong>Kepala Kampung Sota</strong><br><br><br><br>' +
                                        '<strong><u>CONRADA MURNAN</u></strong><br>' +
                                        'NIP. -' +
                                    '</td>' +
                                '</tr>' +
                            '</table>' +
                        '</body>' +
                    '</html>';

                    // Unduh Langsung Berkas Excel (.xls) Menggunakan Blob UTF-8
                    const blob = new Blob(['\ufeff' + excelHtml], { type: 'application/vnd.ms-excel;charset=utf-8;' });
                    const downloadUrl = URL.createObjectURL(blob);
                    const tempLink = document.createElement('a');
                    tempLink.href = downloadUrl;
                    tempLink.download = 'Rekapitulasi_SVI_Kampung_Sota_2026.xls';
                    document.body.appendChild(tempLink);
                    tempLink.click();
                    document.body.removeChild(tempLink);
                    URL.revokeObjectURL(downloadUrl);

                    this.loadingExcel = false;
                    this.showToast('berhasil', 'Berkas Rekapitulasi_SVI_Kampung_Sota_2026.xls berhasil diunduh ke perangkat Anda.');
                } catch (err) {
                    console.error(err);
                    this.loadingExcel = false;
                    this.showToast('gagal', 'Terjadi kesalahan saat memproses ekspor Excel.');
                }
            }, 500);
        },

        // =====================================================================
        // 2. UNDUH / CETAK PDF RESMI KAMPUNG SOTA
        // =====================================================================
        unduhPdf() {
            if (this.loadingPdf || this.filteredData.length === 0) return;
            this.loadingPdf = true;

            // Buka modal lembar pratinjau resmi dan panggil window.print()
            this.showPrintModal = true;
            setTimeout(() => {
                this.loadingPdf = false;
                window.print();
            }, 450);
        }
    };
}

// Inisialisasi Alpine Data
if (window.Alpine) {
    Alpine.data('panelLaporan', panelLaporan);
} else {
    document.addEventListener('alpine:init', () => {
        Alpine.data('panelLaporan', panelLaporan);
    });
}
</script>
@endpush
