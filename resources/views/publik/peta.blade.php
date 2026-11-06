@extends('layouts.public')

@section('title', 'SOTA-GIS — Peta Sebaran Kerentanan Sosial Ekonomi Kampung Sota')
@section('meta-description', 'Peta interaktif sebaran tingkat kerentanan sosial ekonomi masyarakat Kampung Sota, Distrik Sota, Kabupaten Merauke berdasarkan indikator SVI.')

@push('head')
    {{-- Leaflet CSS & Bundled JS --}}
    @vite(['resources/js/peta-publik.js'])
    <script>window.SOTA_REAL_WARGA = @json($wargaPoints ?? []);</script>
    <style>
        html, body {
            height: 100% !important;
            overflow: hidden !important;
            overscroll-behavior: none !important;
        }
        .leaflet-control-attribution {
            user-select: text !important;
        }
        .custom-cluster-icon {
            background: transparent !important;
            border: none !important;
        }
        .sota-map-marker {
            background: transparent !important;
            border: none !important;
        }
        .leaflet-bottom.leaflet-right .leaflet-control-scale {
            margin-right: 75px !important;
            margin-bottom: 6px !important;
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
    filterRendah: true,
    filterSedang: true,
    filterTinggi: true,
    dataWarga: window.SOTA_REAL_WARGA || [],
    toggleFilter(level) {
        if (level === 'rendah') this.filterRendah = !this.filterRendah;
        if (level === 'sedang') this.filterSedang = !this.filterSedang;
        if (level === 'tinggi') this.filterTinggi = !this.filterTinggi;
        this.syncFilter();
    },
    get countRendah() {
        return this.dataWarga.filter(w => (w.klasifikasi || '').toLowerCase() === 'rendah').length;
    },
    get countSedang() {
        return this.dataWarga.filter(w => (w.klasifikasi || '').toLowerCase() === 'sedang').length;
    },
    get countTinggi() {
        return this.dataWarga.filter(w => (w.klasifikasi || '').toLowerCase() === 'tinggi').length;
    },
    getCountJalur(jalurName, level) {
        return this.dataWarga.filter(w => {
            if ((w.klasifikasi || '').toLowerCase() !== level) return false;
            const r = (w.jalur || '').trim().toLowerCase();
            const t = jalurName.trim().toLowerCase();
            if (t === 'jalur a' || t.includes('jalur a')) {
                return r === 'jalur a' || /^jalur\s*([1-6]a|a)$/i.test(r) || (/a$/i.test(r) && r.includes('jalur'));
            }
            if (t === 'jalur b' || t.includes('jalur b')) {
                return r === 'jalur b' || /^jalur\s*([1-7]b|b)$/i.test(r) || (/b$/i.test(r) && r.includes('jalur'));
            }
            if (t.includes('plbn')) {
                return r.includes('plbn');
            }
            if (t.includes('poros trans') || t === 'poros trans') {
                return r.includes('poros trans') || (r.includes('poros') && !r.includes('plbn'));
            }
            return r.includes(t);
        }).length;
    },
    get totalTampil() {
        let total = 0;
        if (this.filterRendah) total += this.countRendah;
        if (this.filterSedang) total += this.countSedang;
        if (this.filterTinggi) total += this.countTinggi;
        return total;
    },
    syncFilter() {
        const list = [];
        if (this.filterRendah) list.push('rendah');
        if (this.filterSedang) list.push('sedang');
        if (this.filterTinggi) list.push('tinggi');
        if (window.handleKerentananChange) {
            window.handleKerentananChange(list);
        }
    },
    init() {
        window.bukaDetailModal = (warga) => {
            this.selectedWarga = warga;
            this.detailModal = true;
        };
        this.$nextTick(() => {
            this.syncFilter();
        });
    }
}" class="fixed inset-0 top-14 w-full h-[calc(100vh-56px)] overflow-hidden">

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

        {{-- Kotak Filter Kerentanan (Persis Sesuai Screenshot 4) --}}
        <div class="card p-4 shadow-lg pointer-events-auto space-y-3 rounded-2xl w-[260px] bg-white border border-line/70">
            <h3 class="text-xs font-bold text-ink tracking-tight">Tingkat kerentanan</h3>

            <div class="space-y-2">
                {{-- 1. Rendah --}}
                <div>
                    <div class="flex items-center justify-between py-1 cursor-pointer select-none rounded-lg hover:bg-paper/60 px-1 -mx-1 transition-colors"
                         @click="toggleFilter('rendah')">
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                   :checked="filterRendah"
                                   @click.stop="toggleFilter('rendah')"
                                   class="h-4 w-4 rounded border-line text-brand focus:ring-0 cursor-pointer">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-rendah"></span>
                            <span class="text-xs text-ink font-medium">Rendah</span>
                        </div>
                        <span class="text-xs text-muted tabular-nums" x-text="countRendah"></span>
                    </div>

                    {{-- Sebaran per wilayah untuk Rendah --}}
                    <div x-show="filterRendah"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="ml-2 pl-3 border-l-2 border-rendah my-1.5 space-y-1 text-xs">
                        <p class="text-[11px] text-muted font-normal">Sebaran per wilayah</p>
                        <div class="flex justify-between text-ink font-semibold text-xs">
                            <span>Jalur B</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Jalur B\', \'rendah\')"></span>
                        </div>
                        <div class="flex justify-between text-ink font-semibold text-xs">
                            <span>Poros Trans</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Poros Trans\', \'rendah\')"></span>
                        </div>
                        <div class="flex justify-between text-muted font-normal text-xs">
                            <span>Jalur A</span><span class="tabular-nums" x-text="getCountJalur(\'Jalur A\', \'rendah\')"></span>
                        </div>
                    </div>
                </div>

                {{-- 2. Sedang --}}
                <div>
                    <div class="flex items-center justify-between py-1 cursor-pointer select-none rounded-lg hover:bg-paper/60 px-1 -mx-1 transition-colors"
                         @click="toggleFilter('sedang')">
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                   :checked="filterSedang"
                                   @click.stop="toggleFilter('sedang')"
                                   class="h-4 w-4 rounded border-line text-brand focus:ring-0 cursor-pointer">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-sedang"></span>
                            <span class="text-xs text-ink font-medium">Sedang</span>
                        </div>
                        <span class="text-xs text-muted tabular-nums" x-text="countSedang"></span>
                    </div>

                    {{-- Sebaran per wilayah untuk Sedang --}}
                    <div x-show="filterSedang"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="ml-2 pl-3 border-l-2 border-sedang my-1.5 space-y-1 text-xs"
                         style="display: none;">
                        <p class="text-[11px] text-muted font-normal">Sebaran per wilayah</p>
                        <div class="flex justify-between text-ink font-semibold text-xs">
                            <span>Poros Trans</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Poros Trans\', \'sedang\')"></span>
                        </div>
                        <div class="flex justify-between text-ink font-semibold text-xs">
                            <span>Jalur B</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Jalur B\', \'sedang\')"></span>
                        </div>
                        <div class="flex justify-between text-ink font-semibold text-xs">
                            <span>Jalur A</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Jalur A\', \'sedang\')"></span>
                        </div>
                    </div>
                </div>

                {{-- 3. Tinggi --}}
                <div>
                    <div class="flex items-center justify-between py-1 cursor-pointer select-none rounded-lg hover:bg-paper/60 px-1 -mx-1 transition-colors"
                         @click="toggleFilter('tinggi')">
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                   :checked="filterTinggi"
                                   @click.stop="toggleFilter('tinggi')"
                                   class="h-4 w-4 rounded border-line text-brand focus:ring-0 cursor-pointer">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-tinggi"></span>
                            <span class="text-xs text-ink font-medium">Tinggi</span>
                        </div>
                        <span class="text-xs text-muted tabular-nums" x-text="countTinggi"></span>
                    </div>

                    {{-- Sebaran per wilayah untuk Tinggi --}}
                    <div x-show="filterTinggi"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="ml-2 pl-3 border-l-2 border-tinggi my-1.5 space-y-1 text-xs"
                         style="display: none;">
                        <p class="text-[11px] text-muted font-normal">Sebaran per wilayah</p>
                        <div class="flex justify-between text-ink font-semibold text-xs">
                            <span>Jalur B</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Jalur B\', \'tinggi\')"></span>
                        </div>
                        <div class="flex justify-between text-ink font-semibold text-xs">
                            <span>Poros Trans</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Poros Trans\', \'tinggi\')"></span>
                        </div>
                        <div class="flex justify-between text-ink font-semibold text-xs">
                            <span>Jalur A</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Jalur A\', \'tinggi\')"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Counter --}}
            <div class="border-t border-line/60 pt-2.5">
                <p id="filter-counter-text" class="text-[11px] text-ink">
                    Menampilkan <strong class="font-bold text-ink" x-text="totalTampil"></strong> dari <strong class="font-bold text-ink" x-text="dataWarga.length"></strong> rumah tangga
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

    {{-- Tombol Tarik Bottom Sheet Mobile (Kiri Bawah, Di Atas Tombol Layers) --}}
    <div class="lg:hidden absolute bottom-24 left-4 z-20">
        <button @click="mobileSheetOpen = !mobileSheetOpen"
                class="btn-primary !min-h-[40px] !py-1.5 !px-3.5 !text-xs shadow-xl flex items-center gap-2 rounded-full font-bold">
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

        {{-- Konten Filter Mobile (Screenshot 4) --}}
        <div class="space-y-2">
            {{-- 1. Rendah --}}
            <div>
                <div class="flex items-center justify-between py-1 cursor-pointer select-none"
                     @click="toggleFilter('rendah')">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" :checked="filterRendah" @click.stop="toggleFilter('rendah')" class="h-4 w-4 rounded border-line text-brand">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-rendah"></span>
                        <span class="text-xs text-ink font-medium">Rendah</span>
                    </div>
                    <span class="text-xs text-muted tabular-nums" x-text="countRendah"></span>
                </div>
                <div x-show="filterRendah" class="ml-2 pl-3 border-l-2 border-rendah my-1.5 space-y-1 text-xs">
                    <p class="text-[10px] text-muted">Sebaran per wilayah</p>
                    <div class="flex justify-between text-ink font-semibold text-xs"><span>Jalur B</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Jalur B\', \'rendah\')"></span></div>
                    <div class="flex justify-between text-ink font-semibold text-xs"><span>Poros Trans</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Poros Trans\', \'rendah\')"></span></div>
                    <div class="flex justify-between text-muted font-normal text-xs"><span>Jalur A</span><span class="tabular-nums" x-text="getCountJalur(\'Jalur A\', \'rendah\')"></span></div>
                </div>
            </div>

            {{-- 2. Sedang --}}
            <div>
                <div class="flex items-center justify-between py-1 cursor-pointer select-none"
                     @click="toggleFilter('sedang')">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" :checked="filterSedang" @click.stop="toggleFilter('sedang')" class="h-4 w-4 rounded border-line text-brand">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-sedang"></span>
                        <span class="text-xs text-ink font-medium">Sedang</span>
                    </div>
                    <span class="text-xs text-muted tabular-nums" x-text="countSedang"></span>
                </div>
                <div x-show="filterSedang" class="ml-2 pl-3 border-l-2 border-sedang my-1.5 space-y-1 text-xs" style="display: none;">
                    <p class="text-[10px] text-muted">Sebaran per wilayah</p>
                    <div class="flex justify-between text-ink font-semibold text-xs"><span>Poros Trans</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Poros Trans\', \'sedang\')"></span></div>
                    <div class="flex justify-between text-ink font-semibold text-xs"><span>Jalur B</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Jalur B\', \'sedang\')"></span></div>
                    <div class="flex justify-between text-ink font-semibold text-xs"><span>Jalur A</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Jalur A\', \'sedang\')"></span></div>
                </div>
            </div>

            {{-- 3. Tinggi --}}
            <div>
                <div class="flex items-center justify-between py-1 cursor-pointer select-none"
                     @click="toggleFilter('tinggi')">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" :checked="filterTinggi" @click.stop="toggleFilter('tinggi')" class="h-4 w-4 rounded border-line text-brand">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-tinggi"></span>
                        <span class="text-xs text-ink font-medium">Tinggi</span>
                    </div>
                    <span class="text-xs text-muted tabular-nums" x-text="countTinggi"></span>
                </div>
                <div x-show="filterTinggi" class="ml-2 pl-3 border-l-2 border-tinggi my-1.5 space-y-1 text-xs" style="display: none;">
                    <p class="text-[10px] text-muted">Sebaran per wilayah</p>
                    <div class="flex justify-between text-ink font-semibold text-xs"><span>Jalur B</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Jalur B\', \'tinggi\')"></span></div>
                    <div class="flex justify-between text-ink font-semibold text-xs"><span>Poros Trans</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Poros Trans\', \'tinggi\')"></span></div>
                    <div class="flex justify-between text-ink font-semibold text-xs"><span>Jalur A</span><span class="font-normal tabular-nums" x-text="getCountJalur(\'Jalur A\', \'tinggi\')"></span></div>
                </div>
            </div>
        </div>

        <div class="border-t border-line/60 pt-2 text-center">
            <p class="text-[11px] text-ink">
                Menampilkan <strong class="font-bold text-ink" x-text="totalTampil"></strong> dari <strong class="font-bold text-ink" x-text="dataWarga.length"></strong> rumah tangga
            </p>
        </div>

        <button @click="mobileSheetOpen = false" class="btn-primary w-full !min-h-[44px] !text-xs font-bold">
            Terapkan Filter
        </button>
    </div>

    {{-- ============================================================
         5. TATA LETAK KONTROL PETA SESUAI GOOGLE MAPS (SCREENSHOT 1)
         ============================================================ --}}
    {{-- POJOK KIRI BAWAH: Tombol Pengalih Basemap (Peta & Satelit) Sesuai Screenshot 2 --}}
    <div class="absolute bottom-6 left-4 lg:left-6 z-20 pointer-events-auto select-none">
        <div class="inline-flex items-center rounded-lg bg-white/95 backdrop-blur-sm shadow-md border border-line p-1 text-xs font-semibold">
            <button type="button"
                    @click="currentBasemap = 'osm'; window.toggleBasemap('osm')"
                    :class="currentBasemap === 'osm' ? 'bg-brand text-white font-bold shadow-xs' : 'text-slate-600 hover:text-ink hover:bg-slate-100 font-medium'"
                    class="px-3.5 py-1.5 rounded-md text-xs transition-all">
                Peta
            </button>
            <button type="button"
                    @click="currentBasemap = 'satellite'; window.toggleBasemap('satellite')"
                    :class="currentBasemap === 'satellite' ? 'bg-brand text-white font-bold shadow-xs' : 'text-slate-600 hover:text-ink hover:bg-slate-100 font-medium'"
                    class="px-3.5 py-1.5 rounded-md text-xs transition-all">
                Satelit
            </button>
        </div>
    </div>

    {{-- POJOK KANAN BAWAH: Tombol Lokasi Saya & Tombol Zoom Vertikal (Tersusun Rapi Sesuai Screenshot 1) --}}
    <div class="absolute bottom-6 right-4 lg:right-6 z-20 flex flex-col items-center gap-2 pointer-events-auto select-none">
        {{-- Tombol Lokasi Saya (GPS Target Bulat/Rounded Bersih) --}}
        <button type="button"
                id="btn-lokasi-saya"
                onclick="window.ambilLokasiSaya()"
                title="Cari lokasi saya saat ini"
                class="h-10 w-10 bg-white rounded-lg border border-line shadow-md flex items-center justify-center text-ink hover:text-brand hover:bg-paper active:scale-95 transition-all focus:outline-none"
                aria-label="Lokasi saya">
            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
        </button>

        {{-- Kontrol Zoom Vertikal Mandiri (+ dan -) --}}
        <div class="bg-white rounded-lg border border-line shadow-md flex flex-col overflow-hidden">
            <button type="button"
                    onclick="window.sotaMapZoomIn()"
                    title="Perbesar peta"
                    class="h-9 w-10 flex items-center justify-center text-ink hover:bg-paper hover:text-brand active:bg-paper/80 transition-colors focus:outline-none"
                    aria-label="Perbesar">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
            <div class="h-px w-full bg-line"></div>
            <button type="button"
                    onclick="window.sotaMapZoomOut()"
                    title="Perkecil peta"
                    class="h-9 w-10 flex items-center justify-center text-ink hover:bg-paper hover:text-brand active:bg-paper/80 transition-colors focus:outline-none"
                    aria-label="Perkecil">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
        </div>
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
            <div class="p-5 space-y-4 max-h-[calc(90vh-220px)] overflow-y-auto">
                {{-- Nama KK & Alamat --}}
                <div class="border-b border-line pb-3">
                    <h3 class="text-lg font-bold text-ink" x-text="selectedWarga ? selectedWarga.nama_kk : ''"></h3>
                    <div class="flex items-center gap-1.5 text-xs text-muted mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded font-bold text-[11px] bg-brand/10 text-brand"
                              x-text="selectedWarga ? selectedWarga.jalur : ''"></span>
                        <span>&middot;</span>
                        <span x-text="selectedWarga ? selectedWarga.alamat : ''"></span>
                    </div>
                    <template x-if="selectedWarga && selectedWarga.patokan">
                        <p class="text-[11px] text-muted italic mt-1 flex items-center gap-1">
                            <span class="font-semibold not-italic text-ink/70">Patokan:</span>
                            <span x-text="selectedWarga.patokan"></span>
                        </p>
                    </template>
                </div>

                {{-- Grid Skor SVI & Klasifikasi --}}
                <div class="grid grid-cols-2 gap-3 bg-paper p-3 rounded-xl border border-line text-center">
                    <div>
                        <span class="text-[11px] text-muted block font-medium">Total Skor SVI</span>
                        <span class="text-2xl font-bold text-ink tabular-nums" x-text="selectedWarga ? selectedWarga.total_skor : ''"></span>
                        <span class="text-[10px] text-muted block mt-0.5">
                            skala 9–27 &middot; Rata-rata <strong class="text-ink" x-text="selectedWarga ? selectedWarga.skor_rata_rata : ''"></strong>
                        </span>
                    </div>
                    <div class="border-l border-line flex flex-col items-center justify-center">
                        <span class="text-[11px] text-muted block font-medium mb-1">Klasifikasi</span>
                        <template x-if="selectedWarga && selectedWarga.klasifikasi === 'tinggi'">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-tinggi-tx bg-tinggi-bg border border-tinggi/30 px-2.5 py-1 rounded-full">
                                <span class="inline-block w-2 h-2 rounded-full bg-tinggi"></span> <span>Kerentanan Tinggi</span>
                            </span>
                        </template>
                        <template x-if="selectedWarga && selectedWarga.klasifikasi === 'sedang'">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-sedang-tx bg-sedang-bg border border-sedang/30 px-2.5 py-1 rounded-full">
                                <span class="inline-block w-2 h-2 rounded-full bg-sedang"></span> <span>Kerentanan Sedang</span>
                            </span>
                        </template>
                        <template x-if="selectedWarga && selectedWarga.klasifikasi === 'rendah'">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-rendah-tx bg-rendah-bg border border-rendah/30 px-2.5 py-1 rounded-full">
                                <span class="inline-block w-2 h-2 rounded-full bg-rendah"></span> <span>Kerentanan Rendah</span>
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Rincian Lengkap 9 Variabel SVI --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between border-b border-line pb-1.5">
                        <h4 class="text-xs font-bold text-ink uppercase tracking-wider flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            <span>Rincian 9 Variabel SVI</span>
                        </h4>
                        <span class="text-[10px] font-bold text-brand bg-brand/[0.08] px-2 py-0.5 rounded-full">
                            Terverifikasi
                        </span>
                    </div>

                    <div class="space-y-1.5 text-xs divide-y divide-line/60">
                        {{-- 1. Pendapatan --}}
                        <div class="flex items-start justify-between py-1.5 gap-2">
                            <span class="text-muted shrink-0 font-medium">1. Pendapatan Bulanan</span>
                            <span class="font-bold text-ink text-right" x-text="selectedWarga?.pendapatan || 'Kurang dari Rp1.000.000 / bln'"></span>
                        </div>
                        {{-- 2. Pekerjaan --}}
                        <div class="flex items-start justify-between py-1.5 gap-2">
                            <span class="text-muted shrink-0 font-medium">2. Pekerjaan Utama KK</span>
                            <span class="font-bold text-ink text-right" x-text="selectedWarga?.pekerjaan || 'Petani'"></span>
                        </div>
                        {{-- 3. Pendidikan --}}
                        <div class="flex items-start justify-between py-1.5 gap-2">
                            <span class="text-muted shrink-0 font-medium">3. Pendidikan Tertinggi</span>
                            <span class="font-bold text-ink text-right" x-text="selectedWarga?.pendidikan || 'Tamat SMA / Sederajat'"></span>
                        </div>
                        {{-- 4. Jumlah Anggota Keluarga --}}
                        <div class="flex items-start justify-between py-1.5 gap-2">
                            <span class="text-muted shrink-0 font-medium">4. Jumlah Anggota Keluarga</span>
                            <span class="font-bold text-ink text-right" x-text="(selectedWarga?.jumlah_anggota || '4') + ' orang'"></span>
                        </div>
                        {{-- 5. Fisik Bangunan --}}
                        <div class="flex items-start justify-between py-1.5 gap-2">
                            <span class="text-muted shrink-0 font-medium">5. Fisik Bangunan (Dinding/Lantai)</span>
                            <span class="font-bold text-ink text-right" x-text="selectedWarga?.fisik_bangunan || 'Dinding Kayu / Lantai Semen'"></span>
                        </div>
                        {{-- 6. Fasilitas Sanitasi --}}
                        <div class="flex items-start justify-between py-1.5 gap-2">
                            <span class="text-muted shrink-0 font-medium">6. Fasilitas Sanitasi / MCK</span>
                            <span class="font-bold text-ink text-right" x-text="selectedWarga?.sanitasi || 'MCK umum / bersama'"></span>
                        </div>
                        {{-- 7. Sumber Air --}}
                        <div class="flex items-start justify-between py-1.5 gap-2">
                            <span class="text-muted shrink-0 font-medium">7. Sumber Air Bersih</span>
                            <span class="font-bold text-ink text-right" x-text="selectedWarga?.sumber_air || 'Sumur gali'"></span>
                        </div>
                        {{-- 8. Pendapatan Tambahan --}}
                        <div class="flex items-start justify-between py-1.5 gap-2">
                            <span class="text-muted shrink-0 font-medium">8. Pendapatan Tambahan</span>
                            <span class="font-bold text-ink text-right" x-text="selectedWarga?.pendapatan_tambahan || 'Tidak ada'"></span>
                        </div>
                        {{-- 9. Kepemilikan Lahan --}}
                        <div class="flex items-start justify-between py-1.5 gap-2">
                            <span class="text-muted shrink-0 font-medium">9. Kepemilikan Lahan & Rumah</span>
                            <span class="font-bold text-ink text-right" x-text="selectedWarga?.kepemilikan_lahan || 'Milik sendiri'"></span>
                        </div>
                    </div>
                </div>

                {{-- Titik Koordinat GPS --}}
                <div class="flex items-center justify-between px-3 py-2 bg-paper rounded-lg border border-line text-xs">
                    <span class="text-muted flex items-center gap-1.5 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
                        <span>Titik Koordinat (WGS 84):</span>
                    </span>
                    <span class="font-mono font-bold text-ink tabular-nums" x-text="selectedWarga ? selectedWarga.latitude + ', ' + selectedWarga.longitude : ''"></span>
                </div>
            </div>
        </div></div>
    </div>
</div>
@endsection
