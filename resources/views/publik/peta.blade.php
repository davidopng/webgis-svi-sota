@extends('layouts.public')

@section('title', 'SOTA-GIS — Peta Sebaran Kerentanan Sosial Ekonomi Kampung Sota')
@section('meta-description', 'Peta interaktif sebaran tingkat kerentanan sosial ekonomi masyarakat Kampung Sota, Distrik Sota, Kabupaten Merauke berdasarkan indikator SVI.')

@push('head')
    {{-- Leaflet CSS & Bundled JS --}}
    @vite(['resources/js/peta-publik.js'])
    <style>
        .custom-cluster-icon {
            background: transparent !important;
            border: none !important;
        }
        .sota-map-marker {
            background: transparent !important;
            border: none !important;
        }
    </style>
@endpush

@section('content')
<div x-data="{
    detailModal: false,
    selectedWarga: null,
    mobileSheetOpen: false,
    currentBasemap: 'osm',
    searchQuery: '',
    init() {
        window.bukaDetailModal = (warga) => {
            this.selectedWarga = warga;
            this.detailModal = true;
        };
    }
}" class="relative w-full overflow-hidden" style="height: calc(100vh - 56px); margin-top: 56px;">

    {{-- ============================================================
         1. CONTAINER PETA LEAFLET (100% Layar)
         ============================================================ --}}
    <div id="map-container" class="absolute inset-0 z-0 bg-[#e5e9ec]"></div>

    {{-- ============================================================
         2. FLOATING BANNER: JIKA FILTER KOSONG
         ============================================================ --}}
    <div id="filter-empty-warning"
         class="hidden absolute top-4 left-1/2 -translate-x-1/2 z-20 bg-sedang-bg border border-sedang/40 text-sedang-tx px-4 py-2.5 rounded-lg shadow-md text-xs font-semibold flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>Tidak ada kategori yang dipilih. Centang minimal satu tingkat kerentanan atau wilayah.</span>
    </div>

    {{-- ============================================================
         3. PANEL KONTROL KIRI DESKTOP (Wireframe PRD 8.1)
         ============================================================ --}}
    <div class="hidden lg:block absolute top-4 left-4 z-20 w-80 space-y-3 pointer-events-none">
        {{-- Kotak Pencarian Nama Kepala Keluarga --}}
        <div class="card p-3 shadow-lg pointer-events-auto relative">
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text"
                       x-model="searchQuery"
                       @input="window.handleSearchInput($event.target.value)"
                       placeholder="Cari nama kepala keluarga…"
                       class="input-text pl-9 !min-h-[40px] !py-1.5 text-xs font-medium focus:ring-1"
                       aria-label="Cari nama kepala keluarga">
                <button x-show="searchQuery.length > 0"
                        @click="searchQuery = ''; window.handleSearchInput('')"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted hover:text-ink p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Dropdown Hasil Saran Pencarian --}}
            <div id="search-suggestions"
                 class="hidden absolute top-full left-0 right-0 mt-1.5 bg-surface border border-line rounded-lg shadow-xl overflow-hidden z-30 max-h-64 overflow-y-auto">
                <ul id="search-suggestions-list" class="divide-y divide-line"></ul>
                <div id="search-empty-msg" class="hidden p-3 text-xs text-muted text-center italic bg-paper">
                    Nama itu belum ada di data terverifikasi. Coba potongan nama yang lain.
                </div>
            </div>
        </div>

        {{-- Kotak Filter Kerentanan & Wilayah --}}
        <div class="card p-4 shadow-lg pointer-events-auto space-y-4">
            {{-- Filter Tingkat Kerentanan --}}
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-muted mb-2">Tingkat Kerentanan</p>
                <div class="space-y-1.5 text-xs">
                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-paper cursor-pointer">
                        <input type="checkbox" name="filter_kerentanan" value="rendah" checked
                               onchange="window.handleFilterChange()"
                               class="rounded border-line text-rendah focus:ring-rendah/30 h-4 w-4">
                        <span class="text-rendah font-bold text-sm">●</span>
                        <span class="flex-1 font-medium text-ink">Kerentanan Rendah</span>
                        <span class="text-muted tabular-nums font-bold">7</span>
                    </label>

                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-paper cursor-pointer">
                        <input type="checkbox" name="filter_kerentanan" value="sedang" checked
                               onchange="window.handleFilterChange()"
                               class="rounded border-line text-sedang focus:ring-sedang/30 h-4 w-4">
                        <span class="text-sedang font-bold text-sm">▲</span>
                        <span class="flex-1 font-medium text-ink">Kerentanan Sedang</span>
                        <span class="text-muted tabular-nums font-bold">60</span>
                    </label>

                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-paper cursor-pointer">
                        <input type="checkbox" name="filter_kerentanan" value="tinggi" checked
                               onchange="window.handleFilterChange()"
                               class="rounded border-line text-tinggi focus:ring-tinggi/30 h-4 w-4">
                        <span class="text-tinggi font-bold text-sm">◆</span>
                        <span class="flex-1 font-medium text-ink">Kerentanan Tinggi</span>
                        <span class="text-muted tabular-nums font-bold">24</span>
                    </label>
                </div>
            </div>

            {{-- Filter Wilayah / Jalur --}}
            <div class="border-t border-line pt-3">
                <p class="text-xs font-bold uppercase tracking-wider text-muted mb-2">Wilayah Permukiman</p>
                <div class="space-y-1.5 text-xs">
                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-paper cursor-pointer">
                        <input type="checkbox" name="filter_wilayah" value="Jalur A" checked
                               onchange="window.handleFilterChange()"
                               class="rounded border-line text-brand focus:ring-brand/30 h-4 w-4">
                        <span class="flex-1 font-medium text-ink">Jalur A</span>
                        <span class="text-muted tabular-nums font-bold">15</span>
                    </label>

                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-paper cursor-pointer">
                        <input type="checkbox" name="filter_wilayah" value="Jalur B" checked
                               onchange="window.handleFilterChange()"
                               class="rounded border-line text-brand focus:ring-brand/30 h-4 w-4">
                        <span class="flex-1 font-medium text-ink">Jalur B</span>
                        <span class="text-muted tabular-nums font-bold">33</span>
                    </label>

                    <label class="flex items-center gap-2 py-1 px-1.5 rounded hover:bg-paper cursor-pointer">
                        <input type="checkbox" name="filter_wilayah" value="Poros Trans" checked
                               onchange="window.handleFilterChange()"
                               class="rounded border-line text-brand focus:ring-brand/30 h-4 w-4">
                        <span class="flex-1 font-medium text-ink">Poros Trans</span>
                        <span class="text-muted tabular-nums font-bold">43</span>
                    </label>
                </div>
            </div>

            {{-- Tombol Atur Ulang & Info Counter --}}
            <div class="border-t border-line pt-3 space-y-2">
                <button type="button" onclick="window.resetFilter()"
                        class="btn-secondary w-full !min-h-[34px] !py-1 !text-xs font-semibold">
                    Atur ulang filter
                </button>
                <p id="filter-counter-text" class="text-center text-[11px] text-muted font-medium pt-1">
                    Menampilkan 91 rumah tangga terverifikasi
                </p>
            </div>
        </div>
    </div>

    {{-- ============================================================
         4. FLOATING SEARCH & BOTTOM SHEET MOBILE (<1024px)
         ============================================================ --}}
    {{-- Search Bar Mengambang Mobile --}}
    <div class="lg:hidden absolute top-3 inset-x-3 z-20">
        <div class="card p-2.5 shadow-lg relative bg-white/95 backdrop-blur-sm">
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text"
                       @input="window.handleSearchInput($event.target.value)"
                       placeholder="Cari nama kepala keluarga…"
                       class="input-text pl-9 !min-h-[38px] !py-1 text-xs font-medium"
                       aria-label="Cari nama KK">
            </div>
        </div>
    </div>

    {{-- Tombol Tarik Bottom Sheet Mobile (Kiri Bawah) --}}
    <div class="lg:hidden absolute bottom-4 left-4 z-20">
        <button @click="mobileSheetOpen = !mobileSheetOpen"
                class="btn-primary !min-h-[42px] !py-1.5 !px-3.5 !text-xs shadow-xl flex items-center gap-2 rounded-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            <span>Filter Peta</span>
        </button>
    </div>

    {{-- Lembar Bawah (Bottom Sheet) Mobile --}}
    <div x-show="mobileSheetOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="lg:hidden fixed inset-x-0 bottom-0 z-40 bg-surface rounded-t-2xl shadow-2xl border-t border-line p-5 space-y-4 max-h-[80vh] overflow-y-auto"
         style="display: none;"
         x-cloak>

        <div class="flex items-center justify-between pb-2 border-b border-line">
            <div class="flex items-center gap-2">
                <span class="w-8 h-1 bg-line rounded-full mx-auto block mb-1"></span>
                <h3 class="text-sm font-bold text-ink">Filter Kerentanan &amp; Wilayah</h3>
            </div>
            <button @click="mobileSheetOpen = false" class="text-muted hover:text-ink p-1">✕</button>
        </div>

        {{-- Checkboxes Kerentanan Mobile --}}
        <div>
            <p class="text-xs font-bold text-muted mb-2">Tingkat Kerentanan</p>
            <div class="space-y-2 text-xs">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="filter_kerentanan" value="rendah" checked onchange="window.handleFilterChange()" class="h-4 w-4 rounded border-line text-rendah">
                    <span class="text-rendah">●</span>
                    <span class="flex-1 font-medium text-ink">Kerentanan Rendah</span>
                    <span class="font-bold tabular-nums">7</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="filter_kerentanan" value="sedang" checked onchange="window.handleFilterChange()" class="h-4 w-4 rounded border-line text-sedang">
                    <span class="text-sedang">▲</span>
                    <span class="flex-1 font-medium text-ink">Kerentanan Sedang</span>
                    <span class="font-bold tabular-nums">60</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="filter_kerentanan" value="tinggi" checked onchange="window.handleFilterChange()" class="h-4 w-4 rounded border-line text-tinggi">
                    <span class="text-tinggi">◆</span>
                    <span class="flex-1 font-medium text-ink">Kerentanan Tinggi</span>
                    <span class="font-bold tabular-nums">24</span>
                </label>
            </div>
        </div>

        {{-- Checkboxes Wilayah Mobile --}}
        <div class="border-t border-line pt-3">
            <p class="text-xs font-bold text-muted mb-2">Wilayah</p>
            <div class="grid grid-cols-3 gap-2 text-xs">
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" name="filter_wilayah" value="Jalur A" checked onchange="window.handleFilterChange()" class="h-4 w-4 rounded border-line text-brand">
                    <span class="font-medium text-ink">Jalur A</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" name="filter_wilayah" value="Jalur B" checked onchange="window.handleFilterChange()" class="h-4 w-4 rounded border-line text-brand">
                    <span class="font-medium text-ink">Jalur B</span>
                </label>
                <label class="flex items-center gap-1.5 cursor-pointer">
                    <input type="checkbox" name="filter_wilayah" value="Poros Trans" checked onchange="window.handleFilterChange()" class="h-4 w-4 rounded border-line text-brand">
                    <span class="font-medium text-ink">Poros</span>
                </label>
            </div>
        </div>

        <button @click="mobileSheetOpen = false" class="btn-primary w-full !min-h-[44px] !text-xs font-bold">
            Terapkan Filter
        </button>
    </div>

    {{-- ============================================================
         5. KONTROL MENGAMBANG KANAN (Basemap Switcher & Lokasi Saya)
         ============================================================ --}}
    <div class="absolute bottom-6 right-4 z-20 flex flex-col items-end gap-2 pointer-events-auto">
        {{-- Pengalih Basemap (Satelit vs OSM) --}}
        <div class="bg-surface rounded-lg border border-line shadow-lg p-1 flex items-center gap-1 text-xs">
            <button type="button"
                    @click="currentBasemap = 'osm'; window.toggleBasemap('osm')"
                    :class="currentBasemap === 'osm' ? 'bg-brand text-white font-bold' : 'text-muted hover:text-ink'"
                    class="px-2.5 py-1.5 rounded transition-colors">
                Peta
            </button>
            <button type="button"
                    @click="currentBasemap = 'satellite'; window.toggleBasemap('satellite')"
                    :class="currentBasemap === 'satellite' ? 'bg-brand text-white font-bold' : 'text-muted hover:text-ink'"
                    class="px-2.5 py-1.5 rounded transition-colors">
                Satelit
            </button>
        </div>

        {{-- Tombol Lokasi Saya --}}
        <button type="button"
                id="btn-lokasi-saya"
                onclick="window.ambilLokasiSaya()"
                title="Cari lokasi saya saat ini"
                class="h-10 w-10 bg-surface rounded-lg border border-line shadow-lg flex items-center justify-center text-ink hover:bg-paper transition-colors"
                aria-label="Lokasi saya">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
        </button>
    </div>

    {{-- ============================================================
         6. MODAL DETAIL WARGA POP-UP (Wireframe PRD 8.3)
         ============================================================ --}}
    <div x-show="detailModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 overflow-y-auto bg-ink/60 flex items-center justify-center p-4"
         style="display: none;"
         x-cloak>

        <div @click.outside="detailModal = false"
             class="bg-surface rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-line animate-in">

            {{-- Foto Rumah (16:9) --}}
            <div class="relative aspect-video bg-paper border-b border-line flex items-center justify-center overflow-hidden">
                <template x-if="selectedWarga && selectedWarga.foto_rumah">
                    <img :src="selectedWarga.foto_rumah" alt="Foto Rumah" class="w-full h-full object-cover">
                </template>
                <template x-if="!selectedWarga || !selectedWarga.foto_rumah">
                    <div class="flex flex-col items-center justify-center text-muted gap-1.5 p-4 text-center">
                        <div class="h-10 w-10 rounded-full bg-line/60 flex items-center justify-center text-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <span class="text-xs font-medium">Foto belum tersedia</span>
                    </div>
                </template>

                {{-- Tombol Tutup [×] --}}
                <button type="button" @click="detailModal = false"
                        class="absolute top-3 right-3 h-8 w-8 rounded-full bg-ink/70 text-white hover:bg-ink flex items-center justify-center transition-colors"
                        aria-label="Tutup">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Detail Informasi --}}
            <div class="p-5 space-y-4">
                {{-- Nama KK & Alamat --}}
                <div>
                    <h3 class="text-lg font-bold text-ink" x-text="selectedWarga ? 'Bpk. ' + selectedWarga.nama_kk : ''"></h3>
                    <p class="text-xs text-muted mt-0.5" x-text="selectedWarga ? selectedWarga.jalur + ' · ' + selectedWarga.alamat : ''"></p>
                </div>

                {{-- Grid Skor SVI & Klasifikasi (Sesuai PRD 8.3) --}}
                <div class="grid grid-cols-2 gap-3 bg-paper p-3 rounded-xl border border-line text-center">
                    <div>
                        <span class="text-[11px] text-muted block font-medium">Total Skor SVI</span>
                        <span class="text-2xl font-bold text-ink tabular-nums" x-text="selectedWarga ? selectedWarga.total_skor : ''"></span>
                        <span class="text-[10px] text-muted block">(skala 9–27)</span>
                    </div>
                    <div class="border-l border-line flex flex-col items-center justify-center">
                        <span class="text-[11px] text-muted block font-medium mb-1">Klasifikasi</span>
                        <template x-if="selectedWarga && selectedWarga.klasifikasi === 'tinggi'">
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-tinggi-tx bg-tinggi-bg border border-tinggi/30 px-2.5 py-1 rounded-full">
                                <span>◆</span> <span>Kerentanan Tinggi</span>
                            </span>
                        </template>
                        <template x-if="selectedWarga && selectedWarga.klasifikasi === 'sedang'">
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-sedang-tx bg-sedang-bg border border-sedang/30 px-2.5 py-1 rounded-full">
                                <span>▲</span> <span>Kerentanan Sedang</span>
                            </span>
                        </template>
                        <template x-if="selectedWarga && selectedWarga.klasifikasi === 'rendah'">
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-rendah-tx bg-rendah-bg border border-rendah/30 px-2.5 py-1 rounded-full">
                                <span>●</span> <span>Kerentanan Rendah</span>
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Data Deskriptif Pendukung --}}
                <div class="space-y-2 text-xs divide-y divide-line">
                    <div class="flex justify-between py-1.5">
                        <span class="text-muted">Pekerjaan</span>
                        <span class="font-bold text-ink" x-text="selectedWarga ? selectedWarga.pekerjaan : ''"></span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-muted">Jumlah anggota keluarga</span>
                        <span class="font-bold text-ink" x-text="selectedWarga ? selectedWarga.jumlah_anggota + ' orang' : ''"></span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-muted">Titik Koordinat</span>
                        <span class="font-bold text-ink tabular-nums" x-text="selectedWarga ? selectedWarga.latitude + ', ' + selectedWarga.longitude : ''"></span>
                    </div>
                </div>

                {{-- Footer Link Metode SVI --}}
                <div class="pt-2 border-t border-line text-center">
                    <p class="text-[11px] text-muted">
                        Skor dihitung dari 9 variabel SVI.
                        <a href="/tentang" class="text-brand font-bold hover:underline inline-flex items-center gap-0.5">
                            Pelajari metodenya &rarr;
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
