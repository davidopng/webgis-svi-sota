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
        'skor_rata_rata' => $w->skor_rata_rata ?? (number_format($w->total_skor / 9, 2, ',', '.')),
        'klas' => strtolower($w->klasifikasi),
        'klasLabel' => 'Kerentanan ' . ucfirst($w->klasifikasi),
        // 9 Variabel SVI Lapangan
        'pendapatan' => $w->pendapatan ?? '-',
        'pekerjaan' => $w->pekerjaan ?? '-',
        'pendidikan' => $w->pendidikan ?? '-',
        'jumlah_anggota' => (int) ($w->jumlah_anggota_keluarga ?? 4),
        'jenis_dinding' => $w->jenis_dinding ?? '-',
        'jenis_lantai' => $w->jenis_lantai ?? '-',
        'fisik_bangunan' => ($w->jenis_dinding ?? '-') . ' / ' . ($w->jenis_lantai ?? '-'),
        'sanitasi' => $w->fasilitas_sanitasi ?? '-',
        'sumber_air' => $w->sumber_air ?? '-',
        'pendapatan_tambahan' => $w->pendapatan_tambahan ?? '-',
        'kepemilikan_lahan' => $w->kepemilikan_lahan ?? '-',
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
            <p class="text-help text-xs mt-0.5">Penyusunan berkas cetak resmi kedinasan dan spreadsheet digital rekapitulasi kerentanan sosial ekonomi warga Kampung Sota untuk keperluan arsip kampung dan analisis tematik.</p>
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
                            <option value="Jalur A">Jalur A (Jalur 1A – 6A)</option>
                            <option value="Jalur B">Jalur B (Jalur 1B – 7B)</option>
                            <option value="Poros Trans">Jalan Poros Trans Merauke-Sota</option>
                            <option value="Poros Sota PLBN">Jalan Poros Sota PLBN</option>
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
                                Kerentanan Tinggi
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
                            <input type="checkbox" x-model="kolomSkor" class="rounded border-line text-brand h-4 w-4">
                            <span class="font-medium text-ink">Total Skor SVI &amp; Lencana Klasifikasi Kerentanan</span>
                        </label>

                        <div class="pt-2 border-t border-line/60 flex items-center justify-between text-[11px]">
                            <span class="text-muted">Kolom Indikator SVI Terpilih:</span>
                            <span class="font-bold text-brand bg-brand/10 px-2 py-0.5 rounded-full" x-text="activeKolomSviCount + ' Kolom Aktif (Diatur di Bawah)'"></span>
                        </div>
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
             FILTER & PILIHAN KOLOM 9 INDIKATOR SVI
             ============================================================ --}}
        <div class="border-t border-line pt-4 space-y-3">
            <div class="flex items-center justify-between cursor-pointer select-none bg-paper/60 hover:bg-paper p-3 rounded-xl border border-line transition-all"
                 @click="openSviAccordion = !openSviAccordion">
                <div class="flex items-center gap-2.5">
                    <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-brand/10 text-brand shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-ink flex items-center gap-2">
                            <span>Filter &amp; Pilihan Kolom 9 Indikator SVI</span>
                        </h4>
                        <p class="text-[11px] text-muted">Centang indikator yang ingin ditampilkan di kolom tabel, dan pilih opsi dropdown jika ingin memfilter data responden.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <template x-if="activeSviCount > 0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-brand text-white shadow-xs"
                              x-text="activeSviCount + ' Filter Aktif'"></span>
                    </template>
                    <template x-if="activeSviCount > 0">
                        <button type="button"
                                @click.stop="resetSviFilter()"
                                class="text-[11px] text-rose-600 hover:text-rose-800 font-bold px-2 py-0.5 rounded hover:bg-rose-50 transition-colors">
                            Reset Filter
                        </button>
                    </template>
                    <span class="text-muted text-xs font-bold transition-transform duration-200"
                          :class="openSviAccordion ? 'rotate-180' : ''">▼</span>
                </div>
            </div>

            {{-- Isi Accordion: Pilihan Cepat Kolom & Grid 9 Indikator --}}
            <div x-show="openSviAccordion" x-transition class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 px-1 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] text-muted font-medium">Pilihan Cepat Tampil Kolom:</span>
                        <button type="button" @click="pilihSemuaKolomSvi()" class="px-2 py-1 rounded bg-surface hover:bg-brand/10 hover:text-brand border border-line text-[11px] font-semibold transition-colors">Centang Semua (9 Kolom)</button>
                        <button type="button" @click="pilihStandarKolomSvi()" class="px-2 py-1 rounded bg-surface hover:bg-brand/10 hover:text-brand border border-line text-[11px] font-semibold transition-colors">Standar (Pekerjaan &amp; Pendapatan)</button>
                        <button type="button" @click="resetKolomSvi()" class="px-2 py-1 rounded bg-surface hover:bg-rose-50 hover:text-rose-600 border border-line text-[11px] font-semibold transition-colors">Hapus Semua Kolom SVI</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 p-4 bg-paper/30 rounded-xl border border-line/80">
                    {{-- 1. Pendapatan --}}
                    <div class="p-2.5 rounded-xl border transition-all"
                         :class="kolomSvi.pendapatan || sviFilter.pendapatan !== 'semua' ? 'border-brand/40 bg-brand/5 shadow-xs' : 'border-line/70 bg-paper/40'">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="kolomSvi.pendapatan" class="rounded border-line text-brand focus:ring-brand h-4 w-4">
                                <span class="text-xs font-bold text-ink">1. Pendapatan Bulanan</span>
                            </label>
                            <span class="text-[10px] font-bold text-brand" x-show="kolomSvi.pendapatan">Kolom Aktif</span>
                        </div>
                        <select x-model="sviFilter.pendapatan"
                                @change="if (sviFilter.pendapatan !== 'semua') kolomSvi.pendapatan = true"
                                class="input-select w-full text-xs"
                                :class="sviFilter.pendapatan !== 'semua' ? 'border-brand bg-white font-bold text-brand ring-1 ring-brand/30' : ''">
                            <option value="semua">Semua Rentang</option>
                            <option value="1">Rp2.500.000 ke atas</option>
                            <option value="2">Rp1.000.000 – Rp2.499.999</option>
                            <option value="3">Kurang dari Rp1.000.000</option>
                        </select>
                    </div>

                    {{-- 2. Pekerjaan --}}
                    <div class="p-2.5 rounded-xl border transition-all"
                         :class="kolomSvi.pekerjaan || sviFilter.pekerjaan !== 'semua' ? 'border-brand/40 bg-brand/5 shadow-xs' : 'border-line/70 bg-paper/40'">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="kolomSvi.pekerjaan" class="rounded border-line text-brand focus:ring-brand h-4 w-4">
                                <span class="text-xs font-bold text-ink">2. Pekerjaan Utama</span>
                            </label>
                            <span class="text-[10px] font-bold text-brand" x-show="kolomSvi.pekerjaan">Kolom Aktif</span>
                        </div>
                        <select x-model="sviFilter.pekerjaan"
                                @change="if (sviFilter.pekerjaan !== 'semua') kolomSvi.pekerjaan = true"
                                class="input-select w-full text-xs"
                                :class="sviFilter.pekerjaan !== 'semua' ? 'border-brand bg-white font-bold text-brand ring-1 ring-brand/30' : ''">
                            <option value="semua">Semua Pekerjaan</option>
                            <option value="1">Formal / Tetap / PNS / Wiraswasta</option>
                            <option value="2">Petani / Peternak / Honorer</option>
                            <option value="3">Buruh / Serabutan / Tidak Bekerja</option>
                        </select>
                    </div>

                    {{-- 3. Pendidikan --}}
                    <div class="p-2.5 rounded-xl border transition-all"
                         :class="kolomSvi.pendidikan || sviFilter.pendidikan !== 'semua' ? 'border-brand/40 bg-brand/5 shadow-xs' : 'border-line/70 bg-paper/40'">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="kolomSvi.pendidikan" class="rounded border-line text-brand focus:ring-brand h-4 w-4">
                                <span class="text-xs font-bold text-ink">3. Jenjang Pendidikan</span>
                            </label>
                            <span class="text-[10px] font-bold text-brand" x-show="kolomSvi.pendidikan">Kolom Aktif</span>
                        </div>
                        <select x-model="sviFilter.pendidikan"
                                @change="if (sviFilter.pendidikan !== 'semua') kolomSvi.pendidikan = true"
                                class="input-select w-full text-xs"
                                :class="sviFilter.pendidikan !== 'semua' ? 'border-brand bg-white font-bold text-brand ring-1 ring-brand/30' : ''">
                            <option value="semua">Semua Pendidikan</option>
                            <option value="1">Perguruan Tinggi (Diploma/S1/S2)</option>
                            <option value="2">Tamat SMA / Sederajat</option>
                            <option value="3">SD / SMP / Tidak Tamat</option>
                        </select>
                    </div>

                    {{-- 4. Anggota Keluarga --}}
                    <div class="p-2.5 rounded-xl border transition-all"
                         :class="kolomSvi.anggota || sviFilter.anggota !== 'semua' ? 'border-brand/40 bg-brand/5 shadow-xs' : 'border-line/70 bg-paper/40'">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="kolomSvi.anggota" class="rounded border-line text-brand focus:ring-brand h-4 w-4">
                                <span class="text-xs font-bold text-ink">4. Jumlah Anggota Keluarga</span>
                            </label>
                            <span class="text-[10px] font-bold text-brand" x-show="kolomSvi.anggota">Kolom Aktif</span>
                        </div>
                        <select x-model="sviFilter.anggota"
                                @change="if (sviFilter.anggota !== 'semua') kolomSvi.anggota = true"
                                class="input-select w-full text-xs"
                                :class="sviFilter.anggota !== 'semua' ? 'border-brand bg-white font-bold text-brand ring-1 ring-brand/30' : ''">
                            <option value="semua">Semua Jumlah Anggota</option>
                            <option value="1">1 – 2 orang</option>
                            <option value="2">3 – 4 orang</option>
                            <option value="3">&gt; 4 orang</option>
                        </select>
                    </div>

                    {{-- 5. Material Dinding --}}
                    <div class="p-2.5 rounded-xl border transition-all"
                         :class="kolomSvi.dinding || sviFilter.dinding !== 'semua' ? 'border-brand/40 bg-brand/5 shadow-xs' : 'border-line/70 bg-paper/40'">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="kolomSvi.dinding" class="rounded border-line text-brand focus:ring-brand h-4 w-4">
                                <span class="text-xs font-bold text-ink">5. Material Dinding</span>
                            </label>
                            <span class="text-[10px] font-bold text-brand" x-show="kolomSvi.dinding">Kolom Aktif</span>
                        </div>
                        <select x-model="sviFilter.dinding"
                                @change="if (sviFilter.dinding !== 'semua') kolomSvi.dinding = true"
                                class="input-select w-full text-xs"
                                :class="sviFilter.dinding !== 'semua' ? 'border-brand bg-white font-bold text-brand ring-1 ring-brand/30' : ''">
                            <option value="semua">Semua Dinding</option>
                            <option value="tembok">Tembok Permanen</option>
                            <option value="kayu">Kayu / Kulit Kayu</option>
                        </select>
                    </div>

                    {{-- 6. Material Lantai --}}
                    <div class="p-2.5 rounded-xl border transition-all"
                         :class="kolomSvi.lantai || sviFilter.lantai !== 'semua' ? 'border-brand/40 bg-brand/5 shadow-xs' : 'border-line/70 bg-paper/40'">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="kolomSvi.lantai" class="rounded border-line text-brand focus:ring-brand h-4 w-4">
                                <span class="text-xs font-bold text-ink">6. Material Lantai</span>
                            </label>
                            <span class="text-[10px] font-bold text-brand" x-show="kolomSvi.lantai">Kolom Aktif</span>
                        </div>
                        <select x-model="sviFilter.lantai"
                                @change="if (sviFilter.lantai !== 'semua') kolomSvi.lantai = true"
                                class="input-select w-full text-xs"
                                :class="sviFilter.lantai !== 'semua' ? 'border-brand bg-white font-bold text-brand ring-1 ring-brand/30' : ''">
                            <option value="semua">Semua Lantai</option>
                            <option value="keramik">Keramik / Ubin</option>
                            <option value="semen">Semen</option>
                            <option value="tanah_kayu">Tanah / Papan Kayu</option>
                        </select>
                    </div>

                    {{-- 7. Fasilitas Sanitasi (MCK) --}}
                    <div class="p-2.5 rounded-xl border transition-all"
                         :class="kolomSvi.sanitasi || sviFilter.sanitasi !== 'semua' ? 'border-brand/40 bg-brand/5 shadow-xs' : 'border-line/70 bg-paper/40'">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="kolomSvi.sanitasi" class="rounded border-line text-brand focus:ring-brand h-4 w-4">
                                <span class="text-xs font-bold text-ink">7. Fasilitas Sanitasi (MCK)</span>
                            </label>
                            <span class="text-[10px] font-bold text-brand" x-show="kolomSvi.sanitasi">Kolom Aktif</span>
                        </div>
                        <select x-model="sviFilter.sanitasi"
                                @change="if (sviFilter.sanitasi !== 'semua') kolomSvi.sanitasi = true"
                                class="input-select w-full text-xs"
                                :class="sviFilter.sanitasi !== 'semua' ? 'border-brand bg-white font-bold text-brand ring-1 ring-brand/30' : ''">
                            <option value="semua">Semua Sanitasi</option>
                            <option value="pribadi">MCK Pribadi / Jamban Sehat</option>
                            <option value="umum">MCK Umum / Berbagi Tetangga</option>
                            <option value="tidak_ada">Tidak Ada Sanitasi / Menumpang</option>
                        </select>
                    </div>

                    {{-- 8. Sumber Air Bersih --}}
                    <div class="p-2.5 rounded-xl border transition-all"
                         :class="kolomSvi.air || sviFilter.air !== 'semua' ? 'border-brand/40 bg-brand/5 shadow-xs' : 'border-line/70 bg-paper/40'">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="kolomSvi.air" class="rounded border-line text-brand focus:ring-brand h-4 w-4">
                                <span class="text-xs font-bold text-ink">8. Sumber Air Bersih</span>
                            </label>
                            <span class="text-[10px] font-bold text-brand" x-show="kolomSvi.air">Kolom Aktif</span>
                        </div>
                        <select x-model="sviFilter.air"
                                @change="if (sviFilter.air !== 'semua') kolomSvi.air = true"
                                class="input-select w-full text-xs"
                                :class="sviFilter.air !== 'semua' ? 'border-brand bg-white font-bold text-brand ring-1 ring-brand/30' : ''">
                            <option value="semua">Semua Sumber Air</option>
                            <option value="pdam">PDAM</option>
                            <option value="bor_galon">Sumur Bor / Air Galon</option>
                            <option value="gali">Sumur Gali / Hujan</option>
                        </select>
                    </div>

                    {{-- 9. Legalitas Lahan & Rumah --}}
                    <div class="p-2.5 rounded-xl border transition-all"
                         :class="kolomSvi.lahan || sviFilter.lahan !== 'semua' ? 'border-brand/40 bg-brand/5 shadow-xs' : 'border-line/70 bg-paper/40'">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" x-model="kolomSvi.lahan" class="rounded border-line text-brand focus:ring-brand h-4 w-4">
                                <span class="text-xs font-bold text-ink">9. Legalitas Lahan &amp; Rumah</span>
                            </label>
                            <span class="text-[10px] font-bold text-brand" x-show="kolomSvi.lahan">Kolom Aktif</span>
                        </div>
                        <select x-model="sviFilter.lahan"
                                @change="if (sviFilter.lahan !== 'semua') kolomSvi.lahan = true"
                                class="input-select w-full text-xs"
                                :class="sviFilter.lahan !== 'semua' ? 'border-brand bg-white font-bold text-brand ring-1 ring-brand/30' : ''">
                            <option value="semua">Semua Kepemilikan</option>
                            <option value="sendiri">Milik Sendiri / Warisan</option>
                            <option value="sewa">Sewa / Garapan / Pinjam Pakai</option>
                            <option value="tidak_ada">Tidak Ada Lahan / Menumpang</option>
                        </select>
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
                        <th class="py-3 px-4" x-show="kolomSvi.pendapatan">Pendapatan Bulanan</th>
                        <th class="py-3 px-4" x-show="kolomSvi.pekerjaan">Pekerjaan Utama</th>
                        <th class="py-3 px-4" x-show="kolomSvi.pendidikan">Jenjang Pendidikan</th>
                        <th class="py-3 px-4 text-center" x-show="kolomSvi.anggota">Jml Anggota</th>
                        <th class="py-3 px-4" x-show="kolomSvi.dinding">Material Dinding</th>
                        <th class="py-3 px-4" x-show="kolomSvi.lantai">Material Lantai</th>
                        <th class="py-3 px-4" x-show="kolomSvi.sanitasi">Fasilitas Sanitasi</th>
                        <th class="py-3 px-4" x-show="kolomSvi.air">Sumber Air Bersih</th>
                        <th class="py-3 px-4" x-show="kolomSvi.lahan">Legalitas Lahan/Rumah</th>
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
                            <td class="py-3 px-4 text-xs text-ink" x-show="kolomSvi.pendapatan" x-text="item.pendapatan"></td>
                            <td class="py-3 px-4 text-xs font-semibold text-ink" x-show="kolomSvi.pekerjaan" x-text="item.pekerjaan"></td>
                            <td class="py-3 px-4 text-xs text-ink" x-show="kolomSvi.pendidikan" x-text="item.pendidikan"></td>
                            <td class="py-3 px-4 text-xs text-center tabular-nums text-ink" x-show="kolomSvi.anggota" x-text="item.jumlah_anggota + ' org'"></td>
                            <td class="py-3 px-4 text-xs text-ink" x-show="kolomSvi.dinding" x-text="item.jenis_dinding"></td>
                            <td class="py-3 px-4 text-xs text-ink" x-show="kolomSvi.lantai" x-text="item.jenis_lantai"></td>
                            <td class="py-3 px-4 text-xs text-ink" x-show="kolomSvi.sanitasi" x-text="item.sanitasi"></td>
                            <td class="py-3 px-4 text-xs text-ink" x-show="kolomSvi.air" x-text="item.sumber_air"></td>
                            <td class="py-3 px-4 text-xs text-ink" x-show="kolomSvi.lahan" x-text="item.kepemilikan_lahan"></td>
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
                            Cakupan: <span class="font-bold" x-text="getWilayahText()"></span> &bull; Kriteria SVI: <span class="font-bold" x-text="getKriteriaSviText()"></span> &bull; Tanggal Penetapan: <strong>{{ $tanggalLaporan ?? now("Asia/Jayapura")->translatedFormat("d F Y") }}</strong>
                        </p>
                    </div>

                    {{-- 4. BAGIAN ATAS LAPORAN: RINCIAN TOTAL STATISTIK (SESUAI PERMINTAAN) --}}
                    <div class="my-4 ringkasan-box-section">
                        <div class="font-bold text-[11px] uppercase mb-1">A. RINGKASAN REKAPITULASI KERENTANAN</div>
                        <table class="w-full text-xs border-collapse" style="border: 1px solid #000000;">
                            <thead>
                                <tr style="background-color: #f2f2f2;">
                                    <th style="border: 1px solid #000000; padding: 6px; text-align: center;">Total Kepala Keluarga</th>
                                    <th style="border: 1px solid #000000; padding: 6px; text-align: center;">Kerentanan Tinggi</th>
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
                                    <th style="border: 1px solid #000000; padding: 5px; text-align: left;" x-show="kolomSvi.pendapatan">Pendapatan Bulanan</th>
                                    <th style="border: 1px solid #000000; padding: 5px; text-align: left;" x-show="kolomSvi.pekerjaan">Pekerjaan Utama</th>
                                    <th style="border: 1px solid #000000; padding: 5px; text-align: left;" x-show="kolomSvi.pendidikan">Jenjang Pendidikan</th>
                                    <th style="border: 1px solid #000000; padding: 5px; width: 45px; text-align: center;" x-show="kolomSvi.anggota">Anggota</th>
                                    <th style="border: 1px solid #000000; padding: 5px; text-align: left;" x-show="kolomSvi.dinding">Dinding</th>
                                    <th style="border: 1px solid #000000; padding: 5px; text-align: left;" x-show="kolomSvi.lantai">Lantai</th>
                                    <th style="border: 1px solid #000000; padding: 5px; text-align: left;" x-show="kolomSvi.sanitasi">Sanitasi</th>
                                    <th style="border: 1px solid #000000; padding: 5px; text-align: left;" x-show="kolomSvi.air">Sumber Air</th>
                                    <th style="border: 1px solid #000000; padding: 5px; text-align: left;" x-show="kolomSvi.lahan">Legalitas Lahan</th>
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
                                        <td style="border: 1px solid #000000; padding: 4px; font-size: 8.5px;" x-show="kolomSvi.pendapatan" x-text="item.pendapatan"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; font-size: 8.5px; font-weight: bold;" x-show="kolomSvi.pekerjaan" x-text="item.pekerjaan"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; font-size: 8.5px;" x-show="kolomSvi.pendidikan" x-text="item.pendidikan"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; font-size: 8.5px; text-align: center;" x-show="kolomSvi.anggota" x-text="item.jumlah_anggota + ' org'"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; font-size: 8.5px;" x-show="kolomSvi.dinding" x-text="item.jenis_dinding"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; font-size: 8.5px;" x-show="kolomSvi.lantai" x-text="item.jenis_lantai"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; font-size: 8.5px;" x-show="kolomSvi.sanitasi" x-text="item.sanitasi"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; font-size: 8.5px;" x-show="kolomSvi.air" x-text="item.sumber_air"></td>
                                        <td style="border: 1px solid #000000; padding: 4px; font-size: 8.5px;" x-show="kolomSvi.lahan" x-text="item.kepemilikan_lahan"></td>
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
        kolomSkor: true,

        // Kolom 9 SVI (Checkbox independen per indikator)
        kolomSvi: {
            pendapatan: true,
            pekerjaan: true,
            pendidikan: false,
            anggota: false,
            dinding: false,
            lantai: false,
            sanitasi: false,
            air: false,
            lahan: false
        },

        get activeKolomSviCount() {
            let count = 0;
            for (const k in this.kolomSvi) {
                if (this.kolomSvi[k]) count++;
            }
            return count;
        },

        pilihSemuaKolomSvi() {
            for (const k in this.kolomSvi) this.kolomSvi[k] = true;
        },

        pilihStandarKolomSvi() {
            for (const k in this.kolomSvi) this.kolomSvi[k] = (k === 'pekerjaan' || k === 'pendapatan');
        },

        resetKolomSvi() {
            for (const k in this.kolomSvi) this.kolomSvi[k] = false;
        },

        // Accordion & Filter 9 SVI State
        openSviAccordion: false,
        sviFilter: {
            pendapatan: 'semua',
            pekerjaan: 'semua',
            pendidikan: 'semua',
            anggota: 'semua',
            dinding: 'semua',
            lantai: 'semua',
            sanitasi: 'semua',
            air: 'semua',
            lahan: 'semua'
        },

        get activeSviCount() {
            let count = 0;
            for (const k in this.sviFilter) {
                if (this.sviFilter[k] !== 'semua') count++;
            }
            return count;
        },

        resetSviFilter() {
            for (const k in this.sviFilter) {
                this.sviFilter[k] = 'semua';
            }
        },

        getKriteriaSviText() {
            const parts = [];
            const sf = this.sviFilter;
            if (sf.pendapatan !== 'semua') {
                const labels = { '1': '> Rp 2.5 Jt', '2': 'Rp 1 - 2.5 Jt', '3': '< Rp 1 Jt' };
                parts.push('Pendapatan: ' + (labels[sf.pendapatan] || sf.pendapatan));
            }
            if (sf.pekerjaan !== 'semua') {
                const labels = { '1': 'PNS/TNI/Polri/Tetap', '2': 'Petani/Kebun/Pedagang', '3': 'Buruh/Serabutan/Tdk Bekerja' };
                parts.push('Pekerjaan: ' + (labels[sf.pekerjaan] || sf.pekerjaan));
            }
            if (sf.pendidikan !== 'semua') {
                const labels = { '1': 'Perguruan Tinggi', '2': 'SMA Sederajat', '3': 'SD/SMP/Tdk Tamat' };
                parts.push('Pendidikan: ' + (labels[sf.pendidikan] || sf.pendidikan));
            }
            if (sf.anggota !== 'semua') {
                const labels = { '1': '1-2 org', '2': '3-4 org', '3': '>4 org' };
                parts.push('Jml Anggota: ' + (labels[sf.anggota] || sf.anggota));
            }
            if (sf.dinding !== 'semua') {
                parts.push('Dinding: ' + (sf.dinding === 'tembok' ? 'Tembok' : 'Kayu'));
            }
            if (sf.lantai !== 'semua') {
                const labels = { 'keramik': 'Keramik', 'semen': 'Semen', 'tanah_kayu': 'Tanah/Kayu' };
                parts.push('Lantai: ' + (labels[sf.lantai] || sf.lantai));
            }
            if (sf.sanitasi !== 'semua') {
                const labels = { 'pribadi': 'MCK Pribadi', 'umum': 'MCK Umum', 'tidak_ada': 'Tdk Ada Sanitasi' };
                parts.push('Sanitasi: ' + (labels[sf.sanitasi] || sf.sanitasi));
            }
            if (sf.air !== 'semua') {
                const labels = { 'pdam': 'PDAM', 'bor_galon': 'Bor/Galon', 'gali': 'Gali/Hujan' };
                parts.push('Air: ' + (labels[sf.air] || sf.air));
            }
            if (sf.lahan !== 'semua') {
                const labels = { 'sendiri': 'Milik Sendiri', 'sewa': 'Sewa/Garapan', 'tidak_ada': 'Tdk Ada/Numpang' };
                parts.push('Lahan: ' + (labels[sf.lahan] || sf.lahan));
            }

            if (parts.length === 0) return 'Semua Indikator SVI (Standar)';
            return parts.join(', ');
        },

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
                let matchWilayah = true;
                if (this.wilayah !== 'semua') {
                    const r = (item.jalur || '').trim().toLowerCase();
                    const t = this.wilayah.trim().toLowerCase();
                    if (t === 'jalur a' || t.includes('jalur a')) {
                        matchWilayah = r === 'jalur a' || /^jalur\s*([1-6]a|a)$/i.test(r) || (/a$/i.test(r) && r.includes('jalur'));
                    } else if (t === 'jalur b' || t.includes('jalur b')) {
                        matchWilayah = r === 'jalur b' || /^jalur\s*([1-7]b|b)$/i.test(r) || (/b$/i.test(r) && r.includes('jalur'));
                    } else if (t.includes('plbn')) {
                        matchWilayah = r.includes('plbn');
                    } else if (t.includes('poros trans') || t === 'poros trans') {
                        matchWilayah = r.includes('poros trans') || (r.includes('poros') && !r.includes('plbn'));
                    } else {
                        matchWilayah = r.includes(t);
                    }
                }
                const matchStatus = this.statusPenduduk === 'semua' || item.status_penduduk === this.statusPenduduk;
                const matchKlas = (this.tingkatRendah && item.klas === 'rendah') ||
                                  (this.tingkatSedang && item.klas === 'sedang') ||
                                  (this.tingkatTinggi && item.klas === 'tinggi');

                // Filter 9 Variabel SVI
                let matchSvi = true;
                const sf = this.sviFilter;

                if (sf.pendapatan !== 'semua') {
                    const v = (item.pendapatan || '').toLowerCase();
                    if (sf.pendapatan === '3') {
                        matchSvi = matchSvi && (v.includes('kurang') || v.includes('<') || v.includes('dibawah'));
                    } else if (sf.pendapatan === '1') {
                        matchSvi = matchSvi && (v.includes('2.500.000 ke atas') || v.includes('5.000.000') || v.includes('> 2.500.000') || (v.includes('2.500.000') && !v.includes('1.000.000')));
                    } else if (sf.pendapatan === '2') {
                        matchSvi = matchSvi && (v.includes('1.000.000') && v.includes('2.500.000'));
                    }
                }

                if (sf.pekerjaan !== 'semua') {
                    const v = (item.pekerjaan || '').toLowerCase();
                    if (sf.pekerjaan === '1') {
                        matchSvi = matchSvi && (/pns|tni|polri|tetap|wiraswasta|guru|dosen/i.test(v));
                    } else if (sf.pekerjaan === '2') {
                        matchSvi = matchSvi && (/petani|peternak|kebun|tani|pedagang|honorer|kontrak|sopir|supir|angkot|warung|warunf|bengkel/i.test(v));
                    } else if (sf.pekerjaan === '3') {
                        matchSvi = matchSvi && (/buruh|serabutan|tidak bekerja|menganggur|irt|ibu rumah/i.test(v));
                    }
                }

                if (sf.pendidikan !== 'semua') {
                    const v = (item.pendidikan || '').toLowerCase();
                    if (sf.pendidikan === '1') {
                        matchSvi = matchSvi && (/perguruan|diploma|s1|s2|s3|sarjana|kuliah/i.test(v));
                    } else if (sf.pendidikan === '2') {
                        matchSvi = matchSvi && (/sma|smk|sederajat|paket c|ma/i.test(v));
                    } else if (sf.pendidikan === '3') {
                        matchSvi = matchSvi && (/sd|smp|tidak tamat|tidak sekolah|belum tamat/i.test(v));
                    }
                }

                if (sf.anggota !== 'semua') {
                    const v = parseInt(item.jumlah_anggota) || 4;
                    if (sf.anggota === '1') matchSvi = matchSvi && (v <= 2);
                    else if (sf.anggota === '2') matchSvi = matchSvi && (v === 3 || v === 4);
                    else if (sf.anggota === '3') matchSvi = matchSvi && (v > 4);
                }

                if (sf.dinding !== 'semua') {
                    const v = (item.jenis_dinding || '').toLowerCase();
                    if (sf.dinding === 'tembok') matchSvi = matchSvi && (/tembok|permanen/i.test(v));
                    else if (sf.dinding === 'kayu') matchSvi = matchSvi && (/kayu|papan|kulit/i.test(v));
                }

                if (sf.lantai !== 'semua') {
                    const v = (item.jenis_lantai || '').toLowerCase();
                    if (sf.lantai === 'keramik') matchSvi = matchSvi && (/keramik|ubin|granit/i.test(v));
                    else if (sf.lantai === 'semen') matchSvi = matchSvi && (/semen|plester/i.test(v));
                    else if (sf.lantai === 'tanah_kayu') matchSvi = matchSvi && (/tanah|papan|panggung/i.test(v));
                }

                if (sf.sanitasi !== 'semua') {
                    const v = (item.sanitasi || '').toLowerCase();
                    if (sf.sanitasi === 'pribadi') matchSvi = matchSvi && (/pribadi|leher angsa|sehat/i.test(v));
                    else if (sf.sanitasi === 'umum') matchSvi = matchSvi && (/umum|bersama|berbagi/i.test(v));
                    else if (sf.sanitasi === 'tidak_ada') matchSvi = matchSvi && (/tidak|numpang|sungai|kebun/i.test(v));
                }

                if (sf.air !== 'semua') {
                    const v = (item.sumber_air || '').toLowerCase();
                    if (sf.air === 'pdam') matchSvi = matchSvi && (/pdam|perpipaan/i.test(v));
                    else if (sf.air === 'bor_galon') matchSvi = matchSvi && (/bor|galon/i.test(v));
                    else if (sf.air === 'gali') matchSvi = matchSvi && (/gali|hujan|sungai/i.test(v));
                }

                if (sf.lahan !== 'semua') {
                    const v = (item.kepemilikan_lahan || '').toLowerCase();
                    if (sf.lahan === 'sendiri') matchSvi = matchSvi && (/sendiri|warisan/i.test(v));
                    else if (sf.lahan === 'sewa') matchSvi = matchSvi && (/sewa|pinjam|garapan|adat/i.test(v));
                    else if (sf.lahan === 'tidak_ada') matchSvi = matchSvi && (/tidak|numpang/i.test(v));
                }

                return matchWilayah && matchStatus && matchKlas && matchSvi;
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
            let label = 'Seluruh Wilayah Kampung Sota (Jalur A, Jalur B & Poros)';
            if (this.wilayah === 'Jalur A') label = 'Wilayah Jalur A (Jalur 1A – 6A)';
            else if (this.wilayah === 'Jalur B') label = 'Wilayah Jalur B (Jalur 1B – 7B)';
            else if (this.wilayah === 'Poros Trans') label = 'Wilayah Jalan Poros Trans Merauke-Sota';
            else if (this.wilayah === 'Poros Sota PLBN') label = 'Wilayah Jalan Poros Sota PLBN';

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
                    if (this.kolomSvi.pendapatan) totalKolom++;
                    if (this.kolomSvi.pekerjaan) totalKolom++;
                    if (this.kolomSvi.pendidikan) totalKolom++;
                    if (this.kolomSvi.anggota) totalKolom++;
                    if (this.kolomSvi.dinding) totalKolom++;
                    if (this.kolomSvi.lantai) totalKolom++;
                    if (this.kolomSvi.sanitasi) totalKolom++;
                    if (this.kolomSvi.air) totalKolom++;
                    if (this.kolomSvi.lahan) totalKolom++;
                    if (this.kolomSkor) totalKolom += 2;          // Total Skor, Klasifikasi

                    let tableHeaderCols = '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: center;">No</th>' +
                                          '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: left;">Nama Kepala Keluarga</th>' +
                                          '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: center;">Jalur Detail</th>' +
                                          '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: center;">Status Kependudukan</th>' +
                                          '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: left;">RT / RW & Alamat Lengkap</th>';

                    if (this.kolomSvi.pendapatan) tableHeaderCols += '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: left;">Pendapatan Bulanan</th>';
                    if (this.kolomSvi.pekerjaan) tableHeaderCols += '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: left;">Pekerjaan Utama</th>';
                    if (this.kolomSvi.pendidikan) tableHeaderCols += '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: left;">Jenjang Pendidikan</th>';
                    if (this.kolomSvi.anggota) tableHeaderCols += '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: center;">Jumlah Anggota</th>';
                    if (this.kolomSvi.dinding) tableHeaderCols += '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: left;">Material Dinding</th>';
                    if (this.kolomSvi.lantai) tableHeaderCols += '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: left;">Material Lantai</th>';
                    if (this.kolomSvi.sanitasi) tableHeaderCols += '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: left;">Fasilitas Sanitasi</th>';
                    if (this.kolomSvi.air) tableHeaderCols += '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: left;">Sumber Air Bersih</th>';
                    if (this.kolomSvi.lahan) tableHeaderCols += '<th style="background-color: #175C6B; color: #FFFFFF; border: 1px solid #000000; padding: 8px; text-align: left;">Legalitas Lahan/Rumah</th>';

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

                        if (this.kolomSvi.pendapatan) tableRows += '<td style="border: 1px solid #CBD5E1; padding: 6px;">' + (item.pendapatan || '-') + '</td>';
                        if (this.kolomSvi.pekerjaan) tableRows += '<td style="border: 1px solid #CBD5E1; padding: 6px;">' + (item.pekerjaan || '-') + '</td>';
                        if (this.kolomSvi.pendidikan) tableRows += '<td style="border: 1px solid #CBD5E1; padding: 6px;">' + (item.pendidikan || '-') + '</td>';
                        if (this.kolomSvi.anggota) tableRows += '<td style="border: 1px solid #CBD5E1; text-align: center; padding: 6px;">' + item.jumlah_anggota + ' org</td>';
                        if (this.kolomSvi.dinding) tableRows += '<td style="border: 1px solid #CBD5E1; padding: 6px;">' + (item.jenis_dinding || '-') + '</td>';
                        if (this.kolomSvi.lantai) tableRows += '<td style="border: 1px solid #CBD5E1; padding: 6px;">' + (item.jenis_lantai || '-') + '</td>';
                        if (this.kolomSvi.sanitasi) tableRows += '<td style="border: 1px solid #CBD5E1; padding: 6px;">' + (item.sanitasi || '-') + '</td>';
                        if (this.kolomSvi.air) tableRows += '<td style="border: 1px solid #CBD5E1; padding: 6px;">' + (item.sumber_air || '-') + '</td>';
                        if (this.kolomSvi.lahan) tableRows += '<td style="border: 1px solid #CBD5E1; padding: 6px;">' + (item.kepemilikan_lahan || '-') + '</td>';

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
                                '<tr><td colspan="' + totalKolom + '" class="kop-desc">Cakupan: ' + wilayahLabel + ' | Kriteria: ' + this.getKriteriaSviText() + ' | Tanggal Ekspor: ' + this.tanggalCetakText + '</td></tr>' +
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
                                    '<td style="background-color: #FEF2F2; color: #991B1B; padding: 6px;">Kerentanan Tinggi</td>' +
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
