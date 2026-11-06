@extends('layouts.app', [
    'role' => $role ?? (Auth::user()?->role ?? 'operator'),
    'userName' => Auth::user()?->name ?? ($userName ?? 'Pengguna')
])

@section('title', 'Peta Sebaran Kerentanan — SOTA-GIS')
@section('page-title', 'Peta Sebaran Kerentanan Sosial Ekonomi')

@push('head')
    {{-- Leaflet CSS & Bundled JS --}}
    @vite(['resources/js/peta-publik.js'])
    <script>window.SOTA_REAL_WARGA = @json($wargaPoints ?? []);</script>
    <style>
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
        .active-svi-filter {
            border: 2px solid #175c6b !important;
            background-color: rgba(23, 92, 107, 0.12) !important;
            color: #175c6b !important;
            font-weight: 700 !important;
            box-shadow: 0 0 0 1px #175c6b !important;
        }
        .active-svi-label {
            color: #175c6b !important;
            font-weight: 700 !important;
        }
        .active-svi-badge {
            background-color: #175c6b !important;
            color: #ffffff !important;
        }
        .active-svi-accordion {
            border: 1.5px solid #175c6b !important;
            background-color: rgba(23, 92, 107, 0.08) !important;
            color: #175c6b !important;
        }
    </style>
@endpush

@section('full-content')
<script>
    function sharedPetaSebaran() {
        return {
            detailModal: false,
            selectedWarga: null,
            photoFailed: false,
            mobileSheetOpen: false,
            currentBasemap: 'osm',
            searchQuery: '',
            filterPanelOpen: true,
            openSviAccordion: false,
            filterRendah: true,
            filterSedang: true,
            filterTinggi: true,
            selectedJalur: 'semua',
            selectedStatus: 'semua',
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
            get hasActiveSviFilter() {
                for (const k in this.sviFilter) {
                    if (this.sviFilter[k] !== 'semua') return true;
                }
                return false;
            },
            get activeSviCount() {
                let count = 0;
                for (const k in this.sviFilter) {
                    if (this.sviFilter[k] !== 'semua') count++;
                }
                return count;
            },
            get activeFilterCount() {
                let count = 0;
                if (!this.filterRendah || !this.filterSedang || !this.filterTinggi) count++;
                if (this.selectedJalur !== 'semua') count++;
                if (this.selectedStatus !== 'semua') count++;
                count += this.activeSviCount;
                return count;
            },
            resetAllFilters() {
                this.filterRendah = true;
                this.filterSedang = true;
                this.filterTinggi = true;
                this.selectedJalur = 'semua';
                this.selectedStatus = 'semua';
                this.sviFilter = {
                    pendapatan: 'semua',
                    pekerjaan: 'semua',
                    pendidikan: 'semua',
                    anggota: 'semua',
                    dinding: 'semua',
                    lantai: 'semua',
                    sanitasi: 'semua',
                    air: 'semua',
                    lahan: 'semua'
                };
                this.syncFilter();
            },
            syncFilter() {
                const list = [];
                if (this.filterRendah) list.push('rendah');
                if (this.filterSedang) list.push('sedang');
                if (this.filterTinggi) list.push('tinggi');
                if (window.applyComprehensiveFilters) {
                    window.applyComprehensiveFilters({
                        kerentanan: list,
                        jalur: this.selectedJalur,
                        status_penduduk: this.selectedStatus,
                        svi: this.sviFilter
                    });
                }
            },
            init() {
                window.bukaDetailModal = (warga) => {
                    this.selectedWarga = warga;
                    this.photoFailed = false;
                    this.detailModal = true;
                };
                this.$nextTick(() => {
                    this.syncFilter();
                });
            }
        };
    }
</script>

<div x-data="sharedPetaSebaran()" class="absolute inset-0 w-full h-full overflow-hidden">

    {{-- ============================================================
         1. CONTAINER PETA LEAFLET (100% Layar Area Utama)
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
         3. POJOK KANAN ATAS: TOMBOL PENGALIH BASEMAP (PETA / SATELIT)
         ============================================================ --}}
    <div class="absolute top-4 right-4 lg:right-6 z-20 pointer-events-auto select-none">
        <div class="inline-flex items-center rounded-xl bg-white shadow-md border border-line/80 p-1 text-xs font-semibold backdrop-blur-sm">
            <button type="button"
                    @click="currentBasemap = 'osm'; window.toggleBasemap('osm')"
                    :class="currentBasemap === 'osm' ? 'bg-brand text-white font-bold shadow-xs' : 'text-slate-600 hover:text-ink hover:bg-slate-100 font-medium'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
                {{-- Lucide Icon: map --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                <span>Peta</span>
            </button>
            <button type="button"
                    @click="currentBasemap = 'satellite'; window.toggleBasemap('satellite')"
                    :class="currentBasemap === 'satellite' ? 'bg-brand text-white font-bold shadow-xs' : 'text-slate-600 hover:text-ink hover:bg-slate-100 font-medium'"
                    class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
                {{-- Lucide Icon: globe --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                <span>Satelit</span>
            </button>
        </div>
    </div>

    {{-- ============================================================
         4. PANEL KONTROL KIRI DESKTOP (Pencarian & Panel Filter Toggle)
         ============================================================ --}}
    <div class="hidden lg:block absolute top-4 left-4 z-20 w-80 sm:w-[320px] space-y-2 pointer-events-none">
        {{-- Toolbar Atas: Pencarian & Tombol Ikon Filter --}}
        <div class="flex items-center gap-2 pointer-events-auto">
            {{-- Kotak Pencarian Nama Kepala Keluarga --}}
            <div class="card p-2.5 shadow-md flex-1 relative bg-white border border-line/80 rounded-xl">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-muted"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text"
                           x-model="searchQuery"
                           @input="window.handleSearchInput($event.target.value)"
                           placeholder="Cari nama KK…"
                           class="w-full pl-8 pr-6 py-1 text-xs font-medium bg-transparent border-none focus:outline-none text-ink placeholder:text-muted"
                           aria-label="Cari nama kepala keluarga">
                    <button x-show="searchQuery.length > 0"
                            @click="searchQuery = ''; window.handleSearchInput('')"
                            class="absolute right-1 top-1/2 -translate-y-1/2 text-muted hover:text-ink p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
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

            {{-- Tombol Ikon Filter (Bisa Sembunyikan / Munculkan Panel) --}}
            <button type="button"
                    @click="filterPanelOpen = !filterPanelOpen"
                    :class="filterPanelOpen ? 'bg-brand text-white shadow-md' : 'bg-white text-ink hover:bg-paper border border-line/80 shadow-md'"
                    class="h-[42px] px-3 rounded-xl flex items-center gap-1.5 text-xs font-bold transition-all shrink-0"
                    :title="filterPanelOpen ? 'Sembunyikan Panel Filter' : 'Buka Panel Filter'">
                {{-- Lucide Icon: filter / sliders-horizontal --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                <span x-text="filterPanelOpen ? 'Tutup' : 'Filter'"></span>
                <template x-if="activeFilterCount > 0">
                    <span class="h-4 w-4 rounded-full text-[10px] flex items-center justify-center font-bold"
                          :class="filterPanelOpen ? 'bg-white text-brand' : 'bg-brand text-white'"
                          x-text="activeFilterCount"></span>
                </template>
            </button>
        </div>

        {{-- Kotak Panel Filter (Bisa Disembunyikan & Dimunculkan) --}}
        <div x-show="filterPanelOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
             class="card p-4 shadow-xl pointer-events-auto space-y-3.5 rounded-2xl bg-white/95 backdrop-blur-md border border-line/80 max-h-[calc(100vh-140px)] overflow-y-auto">

            {{-- Header Panel Filter --}}
            <div class="flex items-center justify-between pb-2 border-b border-line">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-brand/10 text-brand">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    </span>
                    <h3 class="text-xs font-bold text-ink tracking-tight">Filter Peta Sebaran</h3>
                </div>
                <button type="button"
                        @click="filterPanelOpen = false"
                        class="text-[11px] font-semibold text-muted hover:text-ink px-1.5 py-0.5 rounded hover:bg-paper transition-colors"
                        title="Sembunyikan panel">
                    ✕ Sembunyikan
                </button>
            </div>

            {{-- 1. Tingkat Kerentanan SVI --}}
            <div class="space-y-1.5">
                <span class="block text-[11px] font-bold text-muted uppercase tracking-wider">Tingkat Kerentanan</span>
                <div class="space-y-1">
                    {{-- Rendah --}}
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

                    {{-- Sedang --}}
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

                    {{-- Tinggi --}}
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
                </div>
            </div>

            {{-- 2. Wilayah (Jalur) & Status Kependudukan --}}
            <div class="pt-2 border-t border-line/60 space-y-2">
                <span class="block text-[11px] font-bold text-muted uppercase tracking-wider">Wilayah &amp; Kependudukan</span>
                
                {{-- Dropdown Jalur / Kawasan --}}
                <div>
                    <label class="block text-[11px] font-medium text-ink mb-1">Jalur / Kawasan</label>
                    <select x-model="selectedJalur"
                            @change="syncFilter()"
                            class="input-select w-full !h-8 text-xs font-normal">
                        <option value="semua">Semua Jalur</option>
                        <option value="Jalur A">Jalur A (1A–6A)</option>
                        <option value="Jalur B">Jalur B (1B–7B)</option>
                        <option value="Poros Trans">Poros Trans Merauke-Sota</option>
                        <option value="Poros Sota PLBN">Poros Sota PLBN</option>
                    </select>
                </div>

                {{-- Dropdown Status Kependudukan --}}
                <div>
                    <label class="block text-[11px] font-medium text-ink mb-1">Status Kependudukan</label>
                    <select x-model="selectedStatus"
                            @change="syncFilter()"
                            class="input-select w-full !h-8 text-xs font-normal">
                        <option value="semua">Semua Status Penduduk</option>
                        <option value="Tetap">Warga Tetap</option>
                        <option value="Tidak Tetap">Warga Tidak Tetap</option>
                    </select>
                </div>
            </div>

            {{-- 3. Filter 9 Indikator SVI Spesifik (Accordion / Collapsible) --}}
            <div class="pt-2 border-t border-line/60 space-y-2">
                <button type="button"
                        @click="openSviAccordion = !openSviAccordion"
                        :class="hasActiveSviFilter ? 'active-svi-accordion' : 'bg-paper text-ink hover:bg-slate-100'"
                        class="w-full flex items-center justify-between py-1.5 px-2 rounded-lg text-xs font-bold transition-all">
                    <span class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                        <span>9 Indikator SVI Spesifik</span>
                        <template x-if="hasActiveSviFilter">
                            <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-bold active-svi-badge" x-text="activeSviCount"></span>
                        </template>
                    </span>
                    <span class="text-[10px] font-mono" :class="hasActiveSviFilter ? 'text-[#175c6b] font-bold' : 'text-muted'" x-text="openSviAccordion ? '▲ Lipat' : '▼ Buka'"></span>
                </button>

                <div x-show="openSviAccordion"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="space-y-2.5 pt-1 pl-1 pr-1 max-h-56 overflow-y-auto">

                    {{-- 1. Pendapatan --}}
                    <div>
                        <label class="block text-[10px] font-medium mb-0.5 transition-colors" :class="sviFilter.pendapatan !== 'semua' ? 'active-svi-label' : 'text-muted'">1. Rentang Pendapatan</label>
                        <select x-model="sviFilter.pendapatan" @change="syncFilter()" class="input-select w-full !h-7 text-[11px] transition-all" :class="sviFilter.pendapatan !== 'semua' ? 'active-svi-filter' : ''">
                            <option value="semua">Semua Rentang</option>
                            <option value="1">Rp2.500.000 ke atas</option>
                            <option value="2">Rp1.000.000 – Rp2.499.999</option>
                            <option value="3">Kurang dari Rp1.000.000</option>
                        </select>
                    </div>

                    {{-- 2. Pekerjaan --}}
                    <div>
                        <label class="block text-[10px] font-medium mb-0.5 transition-colors" :class="sviFilter.pekerjaan !== 'semua' ? 'active-svi-label' : 'text-muted'">2. Pekerjaan Utama</label>
                        <select x-model="sviFilter.pekerjaan" @change="syncFilter()" class="input-select w-full !h-7 text-[11px] transition-all" :class="sviFilter.pekerjaan !== 'semua' ? 'active-svi-filter' : ''">
                            <option value="semua">Semua Pekerjaan</option>
                            <option value="1">Formal / Tetap / PNS / Wiraswasta</option>
                            <option value="2">Petani / Peternak / Honorer</option>
                            <option value="3">Buruh / Serabutan / Tidak Bekerja</option>
                        </select>
                    </div>

                    {{-- 3. Pendidikan --}}
                    <div>
                        <label class="block text-[10px] font-medium mb-0.5 transition-colors" :class="sviFilter.pendidikan !== 'semua' ? 'active-svi-label' : 'text-muted'">3. Jenjang Pendidikan</label>
                        <select x-model="sviFilter.pendidikan" @change="syncFilter()" class="input-select w-full !h-7 text-[11px] transition-all" :class="sviFilter.pendidikan !== 'semua' ? 'active-svi-filter' : ''">
                            <option value="semua">Semua Pendidikan</option>
                            <option value="1">Perguruan Tinggi (Diploma/S1/S2)</option>
                            <option value="2">Tamat SMA / Sederajat</option>
                            <option value="3">SD / SMP / Tidak Tamat</option>
                        </select>
                    </div>

                    {{-- 4. Anggota Keluarga --}}
                    <div>
                        <label class="block text-[10px] font-medium mb-0.5 transition-colors" :class="sviFilter.anggota !== 'semua' ? 'active-svi-label' : 'text-muted'">4. Jumlah Anggota Keluarga</label>
                        <select x-model="sviFilter.anggota" @change="syncFilter()" class="input-select w-full !h-7 text-[11px] transition-all" :class="sviFilter.anggota !== 'semua' ? 'active-svi-filter' : ''">
                            <option value="semua">Semua Jumlah Anggota</option>
                            <option value="1">1 – 2 orang</option>
                            <option value="2">3 – 4 orang</option>
                            <option value="3">&gt; 4 orang</option>
                        </select>
                    </div>

                    {{-- 5. Dinding Rumah --}}
                    <div>
                        <label class="block text-[10px] font-medium mb-0.5 transition-colors" :class="sviFilter.dinding !== 'semua' ? 'active-svi-label' : 'text-muted'">5. Material Dinding</label>
                        <select x-model="sviFilter.dinding" @change="syncFilter()" class="input-select w-full !h-7 text-[11px] transition-all" :class="sviFilter.dinding !== 'semua' ? 'active-svi-filter' : ''">
                            <option value="semua">Semua Dinding</option>
                            <option value="tembok">Tembok Permanen</option>
                            <option value="kayu">Kayu / Kulit Kayu</option>
                        </select>
                    </div>

                    {{-- 6. Lantai Rumah --}}
                    <div>
                        <label class="block text-[10px] font-medium mb-0.5 transition-colors" :class="sviFilter.lantai !== 'semua' ? 'active-svi-label' : 'text-muted'">6. Material Lantai</label>
                        <select x-model="sviFilter.lantai" @change="syncFilter()" class="input-select w-full !h-7 text-[11px] transition-all" :class="sviFilter.lantai !== 'semua' ? 'active-svi-filter' : ''">
                            <option value="semua">Semua Lantai</option>
                            <option value="keramik">Keramik / Ubin</option>
                            <option value="semen">Semen</option>
                            <option value="tanah_kayu">Tanah / Papan Kayu</option>
                        </select>
                    </div>

                    {{-- 7. Sanitasi / MCK --}}
                    <div>
                        <label class="block text-[10px] font-medium mb-0.5 transition-colors" :class="sviFilter.sanitasi !== 'semua' ? 'active-svi-label' : 'text-muted'">7. Fasilitas Sanitasi (MCK)</label>
                        <select x-model="sviFilter.sanitasi" @change="syncFilter()" class="input-select w-full !h-7 text-[11px] transition-all" :class="sviFilter.sanitasi !== 'semua' ? 'active-svi-filter' : ''">
                            <option value="semua">Semua Sanitasi</option>
                            <option value="pribadi">MCK Pribadi / Jamban Sehat</option>
                            <option value="umum">MCK Umum / Berbagi Tetangga</option>
                            <option value="tidak_ada">Tidak Ada Sanitasi / Menumpang</option>
                        </select>
                    </div>

                    {{-- 8. Sumber Air Bersih --}}
                    <div>
                        <label class="block text-[10px] font-medium mb-0.5 transition-colors" :class="sviFilter.air !== 'semua' ? 'active-svi-label' : 'text-muted'">8. Sumber Air Bersih</label>
                        <select x-model="sviFilter.air" @change="syncFilter()" class="input-select w-full !h-7 text-[11px] transition-all" :class="sviFilter.air !== 'semua' ? 'active-svi-filter' : ''">
                            <option value="semua">Semua Sumber Air</option>
                            <option value="pdam">PDAM</option>
                            <option value="bor_galon">Sumur Bor / Air Galon</option>
                            <option value="gali">Sumur Gali / Hujan</option>
                        </select>
                    </div>

                    {{-- 9. Status Kepemilikan Lahan --}}
                    <div>
                        <label class="block text-[10px] font-medium mb-0.5 transition-colors" :class="sviFilter.lahan !== 'semua' ? 'active-svi-label' : 'text-muted'">9. Legalitas Lahan &amp; Rumah</label>
                        <select x-model="sviFilter.lahan" @change="syncFilter()" class="input-select w-full !h-7 text-[11px] transition-all" :class="sviFilter.lahan !== 'semua' ? 'active-svi-filter' : ''">
                            <option value="semua">Semua Kepemilikan</option>
                            <option value="sendiri">Milik Sendiri / Warisan</option>
                            <option value="sewa">Sewa / Garapan / Pinjam Pakai</option>
                            <option value="tidak_ada">Tidak Ada Lahan / Menumpang</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Footer Counter & Action --}}
            <div class="border-t border-line/60 pt-2.5 space-y-2">
                <p id="filter-counter-text" class="text-[11px] text-ink font-medium">
                    Menampilkan <strong class="font-bold text-ink" x-text="dataWarga.length"></strong> dari <strong class="font-bold text-ink" x-text="dataWarga.length"></strong> rumah tangga
                </p>
                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="resetAllFilters()"
                            class="flex-1 py-1.5 px-2 bg-paper hover:bg-slate-200 text-ink text-xs font-bold rounded-lg border border-line transition-colors flex items-center justify-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        <span>Reset Filter</span>
                    </button>
                    <button type="button"
                            @click="filterPanelOpen = false"
                            class="py-1.5 px-3 bg-brand/10 hover:bg-brand/20 text-brand text-xs font-bold rounded-lg transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         5. FLOATING SEARCH & BOTTOM SHEET MOBILE (<1024px)
         ============================================================ --}}
    <div class="lg:hidden absolute top-3 inset-x-3 z-20 flex items-center gap-2">
        <div class="card p-2 shadow-lg flex-1 relative bg-white/95 backdrop-blur-sm">
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-muted"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text"
                       @input="window.handleSearchInput($event.target.value)"
                       placeholder="Cari nama KK…"
                       class="w-full pl-8 pr-2 py-1 text-xs font-medium bg-transparent border-none focus:outline-none"
                       aria-label="Cari nama KK">
            </div>
        </div>
        <button @click="mobileSheetOpen = !mobileSheetOpen"
                class="btn-primary !min-h-[38px] !py-1 !px-3 !text-xs shadow-md flex items-center gap-1.5 rounded-xl font-bold shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            <span>Filter</span>
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
         class="lg:hidden fixed inset-x-0 bottom-0 z-40 bg-surface rounded-t-2xl shadow-2xl border-t border-line p-5 space-y-4 max-h-[85vh] overflow-y-auto"
         style="display: none;"
         x-cloak>

        <div class="flex items-center justify-between pb-2 border-b border-line">
            <div class="flex items-center gap-2">
                <span class="w-8 h-1 bg-line rounded-full mx-auto block mb-1"></span>
                <h3 class="text-sm font-bold text-ink">Filter Peta Sebaran SVI</h3>
            </div>
            <button @click="mobileSheetOpen = false" class="text-muted hover:text-ink p-1">✕</button>
        </div>

        {{-- Konten Filter Mobile --}}
        <div class="space-y-3 text-xs">
            {{-- Tingkat Kerentanan --}}
            <div class="space-y-1.5">
                <span class="font-bold text-ink block">Tingkat Kerentanan</span>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="toggleFilter('rendah')"
                            :class="filterRendah ? 'bg-rendah text-white font-bold' : 'bg-paper text-muted border border-line'"
                            class="px-3 py-1.5 rounded-lg text-xs transition-colors flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" :class="filterRendah ? 'bg-white' : 'bg-rendah'"></span>
                        <span>Rendah</span>
                    </button>
                    <button type="button" @click="toggleFilter('sedang')"
                            :class="filterSedang ? 'bg-sedang text-white font-bold' : 'bg-paper text-muted border border-line'"
                            class="px-3 py-1.5 rounded-lg text-xs transition-colors flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" :class="filterSedang ? 'bg-white' : 'bg-sedang'"></span>
                        <span>Sedang</span>
                    </button>
                    <button type="button" @click="toggleFilter('tinggi')"
                            :class="filterTinggi ? 'bg-tinggi text-white font-bold' : 'bg-paper text-muted border border-line'"
                            class="px-3 py-1.5 rounded-lg text-xs transition-colors flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" :class="filterTinggi ? 'bg-white' : 'bg-tinggi'"></span>
                        <span>Tinggi</span>
                    </button>
                </div>
            </div>

            {{-- Wilayah Jalur --}}
            <div>
                <label class="font-bold text-ink block mb-1">Jalur / Kawasan</label>
                <select x-model="selectedJalur" @change="syncFilter()" class="input-select w-full !h-9 text-xs">
                    <option value="semua">Semua Jalur</option>
                    <option value="Jalur A">Jalur A (1A–6A)</option>
                    <option value="Jalur B">Jalur B (1B–7B)</option>
                    <option value="Poros Trans">Poros Trans Merauke-Sota</option>
                    <option value="Poros Sota PLBN">Poros Sota PLBN</option>
                </select>
            </div>

            {{-- Status Penduduk --}}
            <div>
                <label class="font-bold text-ink block mb-1">Status Kependudukan</label>
                <select x-model="selectedStatus" @change="syncFilter()" class="input-select w-full !h-9 text-xs">
                    <option value="semua">Semua Status Penduduk</option>
                    <option value="Tetap">Warga Tetap</option>
                    <option value="Tidak Tetap">Warga Tidak Tetap</option>
                </select>
            </div>
        </div>

        <div class="border-t border-line/60 pt-2 text-center">
            <p class="text-[11px] text-ink">
                Menampilkan <strong class="font-bold text-ink" x-text="dataWarga.length"></strong> rumah tangga
            </p>
        </div>

        <div class="flex gap-2">
            <button type="button" @click="resetAllFilters()" class="py-2.5 px-4 bg-paper hover:bg-slate-200 text-ink text-xs font-bold rounded-xl border border-line">
                Reset
            </button>
            <button @click="mobileSheetOpen = false" class="btn-primary flex-1 !min-h-[44px] !text-xs font-bold">
                Terapkan Filter
            </button>
        </div>
    </div>

    {{-- POJOK KANAN BAWAH: Tombol Lokasi Saya & Tombol Zoom Vertikal --}}
    <div class="absolute bottom-6 right-4 lg:right-6 z-20 flex flex-col items-center gap-2 pointer-events-auto select-none">
        {{-- Tombol Lokasi Saya --}}
        <button type="button"
                id="btn-lokasi-saya"
                onclick="window.ambilLokasiSaya()"
                title="Cari lokasi saya saat ini"
                class="h-10 w-10 bg-white rounded-lg border border-line shadow-md flex items-center justify-center text-ink hover:text-brand hover:bg-paper active:scale-95 transition-all focus:outline-none"
                aria-label="Lokasi saya">
            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
        </button>

        {{-- Kontrol Zoom Vertikal (+ dan -) --}}
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
         6. MODAL DETAIL WARGA POP-UP (Hanya Internal Admin & Operator)
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
                <template x-if="selectedWarga && selectedWarga.foto_rumah && !photoFailed">
                    <img :src="selectedWarga.foto_rumah" alt="" class="w-full h-full object-cover"
                         x-on:error="photoFailed = true">
                </template>
                <div x-show="!selectedWarga || !selectedWarga.foto_rumah || photoFailed"
                     class="flex flex-col items-center justify-center text-muted gap-1.5 p-4 text-center w-full h-full">
                    <div class="h-10 w-10 rounded-full bg-line/60 flex items-center justify-center text-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <span class="text-xs font-medium">Foto Rumah / Bangunan</span>
                </div>

                {{-- Tombol Tutup [×] --}}
                <button type="button" @click="detailModal = false"
                        class="absolute top-3 right-3 h-8 w-8 rounded-full bg-ink/70 text-white hover:bg-ink flex items-center justify-center transition-colors"
                        aria-label="Tutup">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Detail Informasi Lengkap --}}
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
                            skala 9–27 &middot; Rata-rata <strong class="text-ink" x-text="selectedWarga ? (selectedWarga.skor_rata_rata || (selectedWarga.total_skor ? (selectedWarga.total_skor / 9).toFixed(2).replace('.', ',') : '-')) : ''"></strong>
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
        </div>
    </div>
</div>
@endsection
