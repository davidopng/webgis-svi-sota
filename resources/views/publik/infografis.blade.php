@extends('layouts.public')

@section('title', 'Infografis Demografi & Potensi Kampung Sota — SOTA-GIS')
@section('meta-description', 'Infografis demografi, peta interaktif interest point, profil potensi agrikultur, perdagangan perbatasan, dan kependudukan Kampung Sota, Distrik Sota, Merauke.')

@push('head')
    <script>
        window.SOTA_PEKERJAAN_STATS = @json($chartData ?? null);
    </script>
    @vite(['resources/js/infografis-publik.js'])
@endpush

@section('content')
<div class="bg-paper min-h-screen">

    {{-- ============================================================
         1. HERO HEADER (SS1 & SS3): FULL WIDTH DENGAN BACKGROUND newplbn
         ============================================================ --}}
    <section class="relative w-full overflow-hidden bg-slate-900 text-white pt-24 pb-20 sm:pt-32 sm:pb-28 border-b border-line/40 shadow-sm"
             style="background-image: linear-gradient(180deg, rgba(15, 23, 42, 0.78) 0%, rgba(15, 23, 42, 0.88) 100%), url('{{ asset('images/newplbn.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        
        {{-- Ambient Glow --}}
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-black/40 pointer-events-none"></div>
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-brand/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 text-center space-y-6">
            {{-- Badge Wilayah (SS3) --}}
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-white/10 text-white border border-white/20 backdrop-blur-md shadow-sm">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Profil Wilayah &amp; Potensi Perbatasan RI – PNG</span>
            </div>

            {{-- Judul Utama (SS3) --}}
            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight drop-shadow-md">
                Infografis Demografi &amp; Potensi Kampung Sota
            </h1>

            {{-- Deskripsi Terpusat (SS3) --}}
            <p class="text-white/90 text-sm sm:text-base leading-relaxed max-w-2xl mx-auto font-normal">
                Sistem informasi visual dan pemetaan potensi wilayah terpadu yang menyajikan ringkasan statistik kependudukan, keragaman mata pencaharian, dan peluang pengembangan kawasan perbatasan Pos Lintas Batas Negara (PLBN) Sota, Kabupaten Merauke.
            </p>

            {{-- Tombol Aksi (Hanya Masuk ke Sistem, tombol metodologi telah dipindahkan ke pimpinan) --}}
            <div class="pt-2 flex justify-center items-center">
                <a href="/login" class="btn-primary !min-h-[46px] !py-2.5 !px-7 !text-sm font-bold shadow-xl flex items-center gap-2.5 hover:scale-[1.02] transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    <span>Masuk ke Sistem</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Container Konten Utama --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">

        {{-- ============================================================
             2. SECTION PETA KAMPUNG SOTA — INTEREST POINT (SS1)
             ============================================================ --}}
        <section id="peta-kampung" class="card p-6 sm:p-8 space-y-6 rounded-2xl shadow-sm border border-line relative isolate">
            {{-- Header Section Peta --}}
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-4 border-b border-line">
                <div class="space-y-1">
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-ink uppercase bg-gradient-to-r from-brand to-teal-700 bg-clip-text text-transparent">
                        PETA KAMPUNG SOTA
                    </h2>
                    <p class="text-sm text-muted font-medium">
                        Menampilkan Peta Kampung Dengan Interest Point Kampung Sota
                    </p>
                </div>
            </div>

            {{-- Canvas Peta Interaktif Leaflet dengan Collapsible Side Panel (SS2) & Floating Basemap Toggle --}}
            <div x-data="{ sidePanelOpen: true }" class="relative isolate w-full rounded-xl overflow-hidden border border-line shadow-inner bg-slate-100 h-[520px] sm:h-[580px]">
                
                {{-- Floating Toggle untuk Membuka Kembali Side Panel saat Tertutup --}}
                <div x-show="!sidePanelOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute top-3.5 left-3.5 z-20"
                     x-cloak>
                    <button @click="sidePanelOpen = true; $nextTick(() => { if (window.leafletMapSota) window.leafletMapSota.invalidateSize(); })"
                            type="button"
                            class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-bold rounded-xl bg-white/95 text-ink border border-line shadow-lg backdrop-blur-md hover:bg-white hover:text-brand transition-all hover:scale-[1.02] active:scale-95 group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                        <span>Daftar Fasilitas</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-brand text-white font-extrabold">26</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-muted group-hover:translate-x-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                </div>

                {{-- Collapsible Side Panel (SS2 - Daftar Fasilitas Kampung Sota dari QGIS) --}}
                <div x-show="sidePanelOpen"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full opacity-80"
                     x-transition:enter-end="translate-x-0 opacity-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="translate-x-0 opacity-100"
                     x-transition:leave-end="-translate-x-full opacity-80"
                     class="absolute top-0 bottom-0 left-0 z-30 w-80 sm:w-88 max-w-[85%] bg-white/95 backdrop-blur-md border-r border-line shadow-2xl flex flex-col overflow-hidden">
                    
                    {{-- Side Panel Header --}}
                    <div class="p-3.5 sm:p-4 border-b border-line bg-gradient-to-r from-slate-50 to-white flex items-center justify-between gap-2 shrink-0">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-brand/10 text-brand flex items-center justify-center shrink-0 border border-brand/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-xs sm:text-sm font-extrabold text-ink leading-tight truncate">Fasilitas Kampung Sota</h3>
                                <p class="text-[10px] text-muted font-medium flex items-center gap-1.5 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>26 Titik Terdata</span>
                                </p>
                            </div>
                        </div>
                        <button @click="sidePanelOpen = false; $nextTick(() => { if (window.leafletMapSota) window.leafletMapSota.invalidateSize(); })"
                                type="button"
                                title="Tutup / Sembunyikan Panel"
                                class="p-1.5 rounded-lg text-muted hover:text-ink hover:bg-slate-200/70 transition-colors shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                    </div>

                    {{-- Search & Filter Section --}}
                    <div class="p-3 border-b border-line bg-slate-50/70 space-y-2 shrink-0">
                        <div class="relative">
                            <input type="text"
                                   id="fasil-search-input"
                                   placeholder="Cari fasilitas di Sota..."
                                   class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-line bg-white focus:outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition-all placeholder:text-muted/60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-muted absolute left-2.5 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                        {{-- Kategori Filter Pills --}}
                        <div class="flex items-center gap-1 overflow-x-auto pb-0.5 scrollbar-none text-[10px]">
                            <button type="button" data-cat="all" class="fasil-filter-btn px-2.5 py-1 rounded-md font-bold bg-brand text-white shadow-2xs whitespace-nowrap transition-colors">Semua</button>
                            <button type="button" data-cat="ibadah" class="fasil-filter-btn px-2 py-1 rounded-md font-medium text-muted hover:text-ink hover:bg-white whitespace-nowrap transition-colors">Ibadah</button>
                            <button type="button" data-cat="pendidikan" class="fasil-filter-btn px-2 py-1 rounded-md font-medium text-muted hover:text-ink hover:bg-white whitespace-nowrap transition-colors">Pendidikan</button>
                            <button type="button" data-cat="kesehatan" class="fasil-filter-btn px-2 py-1 rounded-md font-medium text-muted hover:text-ink hover:bg-white whitespace-nowrap transition-colors">Kesehatan</button>
                            <button type="button" data-cat="pemerintahan" class="fasil-filter-btn px-2 py-1 rounded-md font-medium text-muted hover:text-ink hover:bg-white whitespace-nowrap transition-colors">Pemerintahan</button>
                            <button type="button" data-cat="layanan_wisata" class="fasil-filter-btn px-2 py-1 rounded-md font-medium text-muted hover:text-ink hover:bg-white whitespace-nowrap transition-colors">Layanan/Wisata</button>
                        </div>
                    </div>

                    {{-- Facility List Container --}}
                    <div id="side-panel-facility-list" class="flex-1 overflow-y-auto divide-y divide-line/60">
                        {{-- Diisi secara dinamis melalui JavaScript --}}
                    </div>

                    {{-- Side Panel Footer --}}
                    <div class="p-2.5 border-t border-line bg-slate-50/90 flex items-center justify-between gap-2 shrink-0 text-xs">
                        <span id="side-panel-count-label" class="text-[11px] font-semibold text-muted">26 Fasilitas Terdata</span>
                        <button id="btn-reset-map-view" type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white border border-line text-[11px] font-bold text-ink hover:bg-paper hover:text-brand transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <span>Reset Zoom</span>
                        </button>
                    </div>
                </div>

                {{-- Floating Basemap Layer Toggle (Peta / Satelit) di Kanan Atas Sejajar Kontrol Zoom --}}
                <div class="absolute top-3.5 right-14 z-20 bg-white/95 backdrop-blur-xs p-1 rounded-xl border border-line shadow-md flex items-center gap-1">
                    <button id="btn-layer-peta"
                            type="button"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg bg-brand text-white shadow-xs transition-colors">
                        Peta
                    </button>
                    <button id="btn-layer-satelit"
                            type="button"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg text-ink/70 hover:text-ink hover:bg-paper transition-colors">
                        Satelit
                    </button>
                </div>

                {{-- Canvas Map --}}
                <div id="peta-kampung-sota" class="w-full h-full z-10"></div>
            </div>
        </section>

        {{-- ============================================================
             3. TIGA KARTU INDIKATOR UTAMA (DEMOGRAFI & WILAYAH)
             ============================================================ --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Penduduk --}}
            <div class="card p-5 border-l-4 border-l-brand hover:shadow-md transition-shadow">
                <p class="text-xs font-bold uppercase tracking-wider text-muted">Total Penduduk</p>
                <p class="text-metric text-ink tabular-nums mt-1">1.686</p>
                <p class="text-xs text-muted mt-2">Jiwa tersebar di Kampung Sota</p>
            </div>

            {{-- Kepala Keluarga --}}
            <div class="card p-5 border-l-4 border-l-emerald-600 hover:shadow-md transition-shadow">
                <p class="text-xs font-bold uppercase tracking-wider text-muted">Kepala Keluarga</p>
                <p class="text-metric text-ink tabular-nums mt-1">425</p>
                <p class="text-xs text-muted mt-2">Kepala Keluarga tercatat</p>
            </div>

            {{-- Luas Wilayah --}}
            <div class="card p-5 border-l-4 border-l-blue-600 hover:shadow-md transition-shadow">
                <p class="text-xs font-bold uppercase tracking-wider text-muted">Luas Wilayah</p>
                <p class="text-metric text-ink tabular-nums mt-1">669,69 <span class="text-sm font-normal text-muted">km²</span></p>
                <p class="text-xs text-muted mt-2">Wilayah terluas di Distrik Sota</p>
            </div>

            {{-- Wilayah Administratif RT/RW --}}
            <div class="card p-5 border-l-4 border-l-amber-600 hover:shadow-md transition-shadow">
                <p class="text-xs font-bold uppercase tracking-wider text-muted">Struktur Rukun Tetangga</p>
                <p class="text-metric text-ink tabular-nums mt-1">12 <span class="text-base font-normal text-muted">RT / 3 RW</span></p>
                <p class="text-xs text-muted mt-2">Terbagi dalam 3 koridor utama</p>
            </div>
        </section>

        {{-- ============================================================
             4. GRAFIK SEBARAN SEKTOR PEKERJAAN PER WILAYAH (LEBAR PENUH)
             ============================================================ --}}
        <section id="grafik-demografi" class="w-full scroll-mt-20">
            {{-- Grafik Bar Bertumpuk Sebaran per Koridor Permukiman (Lebar Penuh) --}}
            <div class="card p-6 sm:p-7 space-y-5">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-line">
                        <div>
                            <h2 class="text-section-title text-ink">Sebaran Sektor Pekerjaan per Wilayah</h2>
                            <p class="text-help text-xs">Komparasi sektor mata pencaharian pada tiga koridor permukiman</p>
                        </div>
                        <span class="text-xs font-bold text-muted bg-paper px-2.5 py-1 rounded-full border border-line">3 Koridor Permukiman</span>
                    </div>

                    {{-- Canvas Chart.js --}}
                    <div class="relative h-72 sm:h-80 w-full pt-4">
                        <canvas id="chart-infografis-bar"></canvas>
                    </div>
                </div>

                {{-- Tabel Rekapitulasi Komparasi Jalur --}}
                <div class="pt-3 border-t border-line overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="text-muted font-semibold border-b border-line pb-1.5">
                                <th class="pb-2">Koridor Permukiman</th>
                                <th class="pb-2 text-center text-teal-700 font-bold">Sektor Informal &amp; Pertanian</th>
                                <th class="pb-2 text-center text-indigo-700 font-bold">Sektor Formal / Bergaji Tetap</th>
                                <th class="pb-2 text-center text-amber-700 font-bold">Sektor Harian / Rentan</th>
                                <th class="pb-2 text-right font-bold">Total KK</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-ink tabular-nums">
                            @foreach(['Jalur A', 'Jalur B', 'Poros Trans'] as $jName)
                            @php
                                $stats = $pekerjaanStats[$jName] ?? ['formal' => 0, 'informal' => 0, 'harian' => 0, 'total' => 0];
                            @endphp
                            <tr>
                                <td class="py-2.5 font-semibold">{{ $jName }}</td>
                                <td class="py-2.5 text-center text-teal-800 font-medium">{{ $stats['informal'] }} KK</td>
                                <td class="py-2.5 text-center text-indigo-800 font-medium">{{ $stats['formal'] }} KK</td>
                                <td class="py-2.5 text-center text-amber-800 font-medium">{{ $stats['harian'] }} KK</td>
                                <td class="py-2.5 text-right font-bold">{{ $stats['total'] }} KK</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Keterangan Sumber Data Resmi --}}
                <div class="pt-3 border-t border-line/70 flex items-center gap-2 text-xs text-muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted shrink-0">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <span>Sumber: Hasil Survei Lapangan Peneliti bersama Tim Universitas Musamus, Oktober 2025.</span>
                </div>
            </div>
        </section>

        {{-- ============================================================
             5. PROFIL KORIDOR & KARAKTERISTIK KAWASAN UTAMA (4 KARTU)
             ============================================================ --}}
        <section id="profil-wilayah" class="card p-6 sm:p-8 space-y-6 scroll-mt-20">
            <div class="border-b border-line pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-ink">Karakteristik Wilayah &amp; Koridor Utama Kampung Sota</h2>
                    <p class="text-help text-xs sm:text-sm mt-0.5">Ringkasan tipologi kawasan, potensi aktivitas masyarakat, dan tata ruang perbatasan Distrik Sota</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-brand/10 text-brand border border-brand/20 self-start sm:self-auto">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
                    <span>4 Kawasan Strategis</span>
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Kartu 1: Kawasan Pertanian (Jalur A) --}}
                <div class="group bg-paper hover:bg-white p-6 sm:p-7 rounded-2xl border border-line hover:border-emerald-500/40 hover:shadow-md transition-all duration-200 flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 inset-x-0 h-1.5 bg-emerald-500 opacity-80 group-hover:opacity-100 transition-opacity"></div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-500/20 group-hover:scale-105 transition-transform">
                                {{-- Icon Sprout / Agriculture --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M7 20h10"/>
                                    <path d="M10 20c5.5-2.5.8-6.4 3-10"/>
                                    <path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/>
                                    <path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-emerald-800 bg-emerald-500/10 border border-emerald-500/25 px-3 py-1 rounded-full whitespace-nowrap">
                                Jalur A
                            </span>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-ink text-base sm:text-lg leading-snug group-hover:text-emerald-700 transition-colors">
                                Kawasan Pertanian (Jalur A)
                            </h3>
                            <p class="text-xs font-semibold text-emerald-700 mt-1">
                                Fokus: Ketahanan pangan lokal &amp; aktivitas agrikultur
                            </p>
                        </div>
                        <p class="text-xs sm:text-sm text-muted leading-relaxed pt-1">
                            Sentra budidaya pertanian di wilayah koridor Jalur A Kampung Sota. Menjadi lumbung penyedia sayur-mayur, palawija, serta peternakan swadaya masyarakat dengan hamparan lahan subur.
                        </p>
                    </div>
                    <div class="pt-3.5 mt-5 border-t border-line/70 flex items-center gap-2 text-xs font-bold text-slate-600">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Agrikultur &amp; Pangan Swadaya</span>
                    </div>
                </div>

                {{-- Kartu 2: Kawasan Permukiman Campuran (Jalur B) --}}
                <div class="group bg-paper hover:bg-white p-6 sm:p-7 rounded-2xl border border-line hover:border-blue-500/40 hover:shadow-md transition-all duration-200 flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 inset-x-0 h-1.5 bg-blue-500 opacity-80 group-hover:opacity-100 transition-opacity"></div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="w-11 h-11 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0 border border-blue-500/20 group-hover:scale-105 transition-transform">
                                {{-- Icon Users / Home Neighborhood --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-blue-800 bg-blue-500/10 border border-blue-500/25 px-3 py-1 rounded-full whitespace-nowrap">
                                Jalur B
                            </span>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-ink text-base sm:text-lg leading-snug group-hover:text-blue-700 transition-colors">
                                Kawasan Permukiman Campuran (Jalur B)
                            </h3>
                            <p class="text-xs font-semibold text-blue-700 mt-1">
                                Fokus: Demografi heterogen &amp; sentra hunian warga
                            </p>
                        </div>
                        <p class="text-xs sm:text-sm text-muted leading-relaxed pt-1">
                            Sentra permukiman padat warga di wilayah koridor Jalur B Kampung Sota. Memiliki karakteristik sosial demografi yang heterogen, lingkungan ramah anak, perkebunan pekarangan, serta fasilitas ibadah.
                        </p>
                    </div>
                    <div class="pt-3.5 mt-5 border-t border-line/70 flex items-center gap-2 text-xs font-bold text-slate-600">
                        <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                        <span>Hunian Harmonis &amp; Perkebunan</span>
                    </div>
                </div>

                {{-- Kartu 3: Pusat Aktivitas & Perdagangan (Poros Trans) --}}
                <div class="group bg-paper hover:bg-white p-6 sm:p-7 rounded-2xl border border-line hover:border-amber-500/40 hover:shadow-md transition-all duration-200 flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 inset-x-0 h-1.5 bg-amber-500 opacity-80 group-hover:opacity-100 transition-opacity"></div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0 border border-amber-500/20 group-hover:scale-105 transition-transform">
                                {{-- Icon Store / Commercial Hub --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                                    <path d="M3 6h18"/>
                                    <path d="M16 10a4 4 0 0 1-8 0"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-amber-800 bg-amber-500/10 border border-amber-500/25 px-3 py-1 rounded-full whitespace-nowrap">
                                Poros Trans
                            </span>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-ink text-base sm:text-lg leading-snug group-hover:text-amber-700 transition-colors">
                                Pusat Aktivitas &amp; Perdagangan (Poros Trans)
                            </h3>
                            <p class="text-xs font-semibold text-amber-700 mt-1">
                                Fokus: Urat nadi transportasi darat &amp; sentra niaga
                            </p>
                        </div>
                        <p class="text-xs sm:text-sm text-muted leading-relaxed pt-1">
                            Koridor arteri utama Jalan Poros Trans-Papua. Berfungsi sebagai pusat perputaran ekonomi harian, pertokoan, kios sembako, jasa bengkel, logistik, dan fasilitas pelayanan publik.
                        </p>
                    </div>
                    <div class="pt-3.5 mt-5 border-t border-line/70 flex items-center gap-2 text-xs font-bold text-slate-600">
                        <span class="inline-block w-2 h-2 rounded-full bg-amber-500"></span>
                        <span>Logistik &amp; Perdagangan Jasa</span>
                    </div>
                </div>

                {{-- Kartu 4: Gerbang Lintas Batas RI-PNG (Kawasan PLBN & Titik Nol) --}}
                <div class="group bg-paper hover:bg-white p-6 sm:p-7 rounded-2xl border border-line hover:border-brand/40 hover:shadow-md transition-all duration-200 flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 inset-x-0 h-1.5 bg-brand opacity-80 group-hover:opacity-100 transition-opacity"></div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="w-11 h-11 rounded-xl bg-brand/10 text-brand flex items-center justify-center shrink-0 border border-brand/20 group-hover:scale-105 transition-transform">
                                {{-- Icon Landmark / Boundary / Compass --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-brand bg-brand/10 border border-brand/25 px-3 py-1 rounded-full whitespace-nowrap">
                                Kawasan PLBN
                            </span>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-ink text-base sm:text-lg leading-snug group-hover:text-brand transition-colors">
                                Gerbang Lintas Batas RI–PNG (Kawasan PLBN &amp; Titik Nol)
                            </h3>
                            <p class="text-xs font-semibold text-brand mt-1">
                                Fokus: Geopolitik, pelintas batas &amp; wisata batas negara
                            </p>
                        </div>
                        <p class="text-xs sm:text-sm text-muted leading-relaxed pt-1">
                            Kawasan etalase kedaulatan negara di ujung timur Indonesia. Menjadi episentrum pemeriksaan CIQS, pasar niaga perbatasan bagi pelintas tradisional RI–PNG, serta destinasi ikonik Monumen Titik Nol Kilometer Merauke–Sabang.
                        </p>
                    </div>
                    <div class="pt-3.5 mt-5 border-t border-line/70 flex items-center gap-2 text-xs font-bold text-slate-600">
                        <span class="inline-block w-2 h-2 rounded-full bg-brand"></span>
                        <span>Geopolitik &amp; Ekonomi Perbatasan</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================
             6. SECTION: GALERI POTENSI KAMPUNG SOTA (4 FOTO)
             ============================================================ --}}
        <section id="galeri-potensi" class="card p-6 sm:p-8 space-y-6 scroll-mt-20">
            <div class="border-b border-line pb-3">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-ink">Dokumentasi Potensi Wilayah Kampung Sota</h2>
                    <p class="text-help text-xs sm:text-sm mt-0.5">Dokumentasi lanskap perbatasan, gerbang negara, serta geliat perekonomian dan agrikultur masyarakat Sota</p>
                </div>
            </div>

            {{-- Grid Responsif 4 Kolom Foto --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                {{-- Foto 1: PLBN Sota --}}
                <div class="group bg-surface border border-line rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-all duration-300 flex flex-col">
                    <div class="relative overflow-hidden aspect-[4/3] bg-paper">
                        <img src="{{ asset('images/plbn1.jpg') }}"
                             alt="Pos Lintas Batas Negara (PLBN) Sota"
                             class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                             loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-ink/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <span class="absolute bottom-2 left-2 text-[10px] font-bold text-white bg-black/50 backdrop-blur-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300">PLBN Sota</span>
                    </div>
                    <div class="p-4 space-y-1 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-ink text-sm group-hover:text-brand transition-colors">Pos Lintas Batas Negara</h3>
                            <p class="text-xs text-muted mt-1 leading-relaxed">
                                Gerbang perbatasan utama RI – PNG sebagai pusat integrasi kepabeanan, imigrasi, dan etalase kedaulatan negara.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Foto 2: Titik Nol Kilometer Sota --}}
                <div class="group bg-surface border border-line rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-all duration-300 flex flex-col">
                    <div class="relative overflow-hidden aspect-[4/3] bg-paper">
                        <img src="{{ asset('images/0km-sota.jpg') }}"
                             alt="Titik Nol Kilometer Sota Merauke"
                             class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                             loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-ink/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <span class="absolute bottom-2 left-2 text-[10px] font-bold text-white bg-black/50 backdrop-blur-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300">0 Km Sota</span>
                    </div>
                    <div class="p-4 space-y-1 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-ink text-sm group-hover:text-brand transition-colors">Titik Nol Kilometer Sota</h3>
                            <p class="text-xs text-muted mt-1 leading-relaxed">
                                Landmark ikonik ujung timur Indonesia di Merauke yang menjadi simbol persatuan dan daya tarik kunjungan wisata.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Foto 3: Pertanian Warga Sota --}}
                <div class="group bg-surface border border-line rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-all duration-300 flex flex-col">
                    <div class="relative overflow-hidden aspect-[4/3] bg-paper">
                        <img src="{{ asset('images/sotapetani.jpg') }}"
                             alt="Aktivitas Pertanian Warga Kampung Sota"
                             class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                             loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-ink/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <span class="absolute bottom-2 left-2 text-[10px] font-bold text-white bg-black/50 backdrop-blur-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300">Agrikultur</span>
                    </div>
                    <div class="p-4 space-y-1 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-ink text-sm group-hover:text-brand transition-colors">Aktivitas Tani &amp; Agrikultur</h3>
                            <p class="text-xs text-muted mt-1 leading-relaxed">
                                Potensi budidaya lahan subur dan perkebunan palawija yang dikembangkan oleh petani lokal untuk ketahanan pangan.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Foto 4: Pelintas Batas & Dinamika Niaga --}}
                <div class="group bg-surface border border-line rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-all duration-300 flex flex-col">
                    <div class="relative overflow-hidden aspect-[4/3] bg-paper">
                        <img src="{{ asset('images/pelintas.jpg') }}"
                             alt="Dinamika Pelintas Batas dan Niaga di Sota"
                             class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                             loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-ink/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <span class="absolute bottom-2 left-2 text-[10px] font-bold text-white bg-black/50 backdrop-blur-xs px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-300">Pasar Batas</span>
                    </div>
                    <div class="p-4 space-y-1 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-ink text-sm group-hover:text-brand transition-colors">Dinamika Niaga &amp; Perlintasan</h3>
                            <p class="text-xs text-muted mt-1 leading-relaxed">
                                Geliat perekonomian pasar perbatasan dan interaksi sosial masyarakat di garis batas Indonesia – Papua Nugini.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================
             7. SECTION: KONTAK & INFORMASI RESMI (SS2 HUBUNGI KAMI)
             ============================================================ --}}
        <section id="kontak" class="space-y-6 scroll-mt-20">
            {{-- Banner Header Gelap Hubungi Kami (SS2) --}}
            <div class="relative overflow-hidden bg-slate-900 text-white rounded-2xl p-8 sm:p-12 text-center border border-slate-800 shadow-md">
                {{-- Ambient Glow --}}
                <div class="absolute -top-20 left-1/2 -translate-x-1/2 w-80 h-80 bg-brand/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 space-y-3 max-w-2xl mx-auto">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold bg-white/10 text-white border border-white/20 backdrop-blur-md shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                        <span>Kontak Resmi</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                        Hubungi Kami
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-normal">
                        Ada pertanyaan atau membutuhkan bantuan teknis langsung? Jangan ragu untuk menghubungi kami atau mengunjungi kantor operasional kami secara langsung.
                    </p>
                </div>
            </div>

            {{-- Grid Konten Informasi Kontak & Peta Interaktif (SS2) --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Kolom Kiri: Informasi Kontak (5 Cols) --}}
                <div class="lg:col-span-5 space-y-4">
                    <div class="card p-6 space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-line">
                            <div class="h-10 w-10 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-ink text-base">Informasi Kontak</h3>
                                <p class="text-xs text-muted">Saluran komunikasi resmi Kampung Sota</p>
                            </div>
                        </div>

                        <p class="text-xs text-muted leading-relaxed">
                            Silakan gunakan salah satu saluran komunikasi resmi kami berikut untuk berkonsultasi mengenai keluhan layanan, informasi kependudukan, atau potensi wilayah.
                        </p>

                        <div class="space-y-3 pt-1">
                            {{-- Item 1: Alamat Kantor Pusat --}}
                            <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-paper border border-line hover:border-brand/40 transition-colors">
                                <div class="h-9 w-9 rounded-lg bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                                <div class="space-y-0.5 text-xs">
                                    <span class="font-bold text-ink block text-xs">Alamat Kantor Pusat</span>
                                    <span class="text-muted leading-relaxed block text-[11px]">Balai Kampung Sota, Sota, Kabupaten Merauke, South Papua.</span>
                                </div>
                            </div>

                            {{-- Item 2: Nomor Telepon & WhatsApp --}}
                            <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-paper border border-line hover:border-brand/40 transition-colors">
                                <div class="h-9 w-9 rounded-lg bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </div>
                                <div class="space-y-0.5 text-xs">
                                    <span class="font-bold text-ink block text-xs">Nomor Telepon &amp; WhatsApp</span>
                                    <span class="text-muted block text-[11px]">(0971) 321456 / +62 812-3456-7890</span>
                                </div>
                            </div>

                            {{-- Item 3: Alamat Email Resmi --}}
                            <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-paper border border-line hover:border-brand/40 transition-colors">
                                <div class="h-9 w-9 rounded-lg bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                </div>
                                <div class="space-y-0.5 text-xs">
                                    <span class="font-bold text-ink block text-xs">Alamat Email Resmi</span>
                                    <span class="text-muted block text-[11px]">kontak@kampungsota.id</span>
                                </div>
                            </div>

                            {{-- Item 4: Jam Operasional Pelayanan --}}
                            <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-paper border border-line hover:border-brand/40 transition-colors">
                                <div class="h-9 w-9 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                                <div class="space-y-0.5 text-xs">
                                    <span class="font-bold text-ink block text-xs">Jam Operasional Pelayanan</span>
                                    <span class="text-muted block text-[11px]">Senin – Jumat: Pukul 08.00 – 15.00 WIT</span>
                                    <span class="text-[10px] text-muted/80 block italic">Sabtu, Minggu &amp; Hari Libur Nasional: Tutup</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Peta Lokasi Kantor / Balai Kampung Sota (7 Cols) --}}
                <div class="lg:col-span-7">
                    <div class="card p-4 sm:p-5 h-full flex flex-col justify-between space-y-4">
                        {{-- Header Map Card --}}
                        <div class="flex items-center justify-between pb-2 border-b border-line gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-teal-500 animate-ping shrink-0"></span>
                                <span class="text-xs font-bold text-ink truncate">Lokasi Kantor Balai Kampung Sota</span>
                            </div>
                            <a href="https://maps.app.goo.gl/h8QSBSEPQsRpMHL38" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-brand hover:underline flex items-center gap-1 shrink-0">
                                <span>Buka Google Maps</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                        </div>

                        {{-- Leaflet Mini-map Canvas --}}
                        <div class="relative w-full rounded-xl overflow-hidden border border-line bg-slate-100 flex-1 min-h-[300px]">
                            <div id="peta-lokasi-kantor" class="w-full h-full min-h-[300px] z-10"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</div>
@endsection
