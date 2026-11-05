@extends('layouts.public')

@section('title', 'Potensi Kampung Sota — Galeri & Dokumentasi Wilayah')
@section('meta-description', 'Potensi Kampung Sota: Menjadi pusat asimilasi budaya, penggerak ekonomi lintas batas, dan lumbung pertanian strategis di wilayah perbatasan RI-Papua Nugini, Distrik Sota, Merauke.')

@section('content')
<div class="pt-16 pb-20 bg-paper min-h-screen" x-data="{
    activeTab: 'all',
    lightboxOpen: false,
    lightboxImg: '',
    lightboxTitle: '',
    lightboxTag: '',
    lightboxDesc: '',
    openLightbox(img, title, tag, desc) {
        this.lightboxImg = img;
        this.lightboxTitle = title;
        this.lightboxTag = tag;
        this.lightboxDesc = desc;
        this.lightboxOpen = true;
    }
}">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

        {{-- ============================================================
             HERO HEADER (SS3)
             Judul Utama: Potensi Kampung Sota
             Sub-judul: Sesuai instruksi baku user
             ============================================================ --}}
        <div class="relative overflow-hidden bg-slate-900 text-white rounded-3xl p-8 sm:p-14 text-center border border-slate-800 shadow-xl"
             style="background-image: linear-gradient(180deg, rgba(15, 23, 42, 0.82) 0%, rgba(15, 23, 42, 0.94) 100%), url('{{ asset('images/newplbn.jpg') }}'); background-size: cover; background-position: center;">
            {{-- Ambient Glow --}}
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-brand/25 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 space-y-4 max-w-4xl mx-auto">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold bg-white/10 text-white border border-white/20 backdrop-blur-md shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Dokumentasi Visual &amp; Lanskap Perbatasan</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                    Potensi Kampung Sota
                </h1>
                <p class="text-sm sm:text-lg text-slate-200 leading-relaxed max-w-3xl mx-auto font-normal">
                    Menjadi pusat asimilasi budaya, penggerak ekonomi lintas batas, dan lumbung pertanian strategis di wilayah perbatasan RI-Papua Nugini, Distrik Sota, Kabupaten Merauke.
                </p>
            </div>
        </div>

        {{-- ============================================================
             3 PILAR UTAMA POTENSI KAMPUNG SOTA
             ============================================================ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Pilar 1 --}}
            <div class="card p-6 space-y-3 border-t-4 border-t-teal-600 hover:shadow-md transition-shadow">
                <div class="w-11 h-11 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0 border border-teal-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 20h10"/>
                        <path d="M10 20c5.5-2.5.8-6.4 3-10"/>
                        <path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/>
                        <path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>
                    </svg>
                </div>
                <h3 class="font-extrabold text-ink text-base">Lumbung Pertanian Strategis</h3>
                <p class="text-xs text-muted leading-relaxed">
                    Hamparan lahan subur di sepanjang Jalur A dan Jalur B yang dibudidayakan untuk komoditas palawija, sayur-mayur, jagung, dan peternakan sebagai penopang swasembada pangan lokal.
                </p>
            </div>

            {{-- Pilar 2 --}}
            <div class="card p-6 space-y-3 border-t-4 border-t-blue-600 hover:shadow-md transition-shadow">
                <div class="w-11 h-11 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0 border border-blue-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3 class="font-extrabold text-ink text-base">Pusat Asimilasi Budaya</h3>
                <p class="text-xs text-muted leading-relaxed">
                    Harmoni sosial multietnis yang menyatukan masyarakat adat Papua, warga transmigran, dan pelintas tradisional dengan kearifan lokal yang terjaga secara rukun dan damai.
                </p>
            </div>

            {{-- Pilar 3 --}}
            <div class="card p-6 space-y-3 border-t-4 border-t-brand hover:shadow-md transition-shadow">
                <div class="w-11 h-11 rounded-xl bg-brand/10 text-brand flex items-center justify-center shrink-0 border border-brand/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" x2="21" y1="22" y2="22"/>
                        <line x1="6" x2="6" y1="18"/>
                        <line x1="10" x2="10" y1="18"/>
                        <line x1="14" x2="14" y1="18"/>
                        <line x1="18" x2="18" y1="18"/>
                        <polygon points="12 2 20 7 4 7"/>
                    </svg>
                </div>
                <h3 class="font-extrabold text-ink text-base">Penggerak Ekonomi Perbatasan</h3>
                <p class="text-xs text-muted leading-relaxed">
                    Etalase kedaulatan bangsa melalui PLBN terpadu Sota dan Pasar Niaga Perbatasan yang memfasilitasi pertukaran logistik, kebutuhan pokok, dan pariwisata Titik 0 Km.
                </p>
            </div>
        </div>

        {{-- ============================================================
             GALERI POTENSI KAMPUNG SOTA — DOKUMENTASI LENGKAP
             ============================================================ --}}
        <section class="card p-6 sm:p-8 space-y-6 shadow-sm border border-line">
            <div class="border-b border-line pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-ink uppercase tracking-tight">
                        Galeri Potensi Kampung Sota
                    </h2>
                    <p class="text-xs sm:text-sm text-muted mt-0.5">
                        Dokumentasi lanskap perbatasan, gerbang negara, serta geliat perekonomian dan agrikultur masyarakat Sota
                    </p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-brand/10 text-brand border border-brand/20 self-start sm:self-auto">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
                    <span>Dokumentasi Resmi Wilayah</span>
                </span>
            </div>

            {{-- Grid Foto Galeri Interaktif --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Foto 1: PLBN Sota --}}
                <div class="group bg-surface border border-line rounded-2xl overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col cursor-pointer"
                     @click="openLightbox('{{ asset('images/plbn1.jpg') }}', 'Pos Lintas Batas Negara (PLBN) Sota', 'Kedaulatan &amp; Hub Perbatasan', 'Gerbang perbatasan utama RI – Papua Nugini sebagai pusat integrasi kepabeanan, imigrasi, karantina, keamanan (CIQS), dan etalase kedaulatan negara Republik Indonesia di wilayah timur.')">
                    <div class="relative overflow-hidden aspect-[16/10] bg-paper">
                        <img src="{{ asset('images/plbn1.jpg') }}"
                             alt="Pos Lintas Batas Negara Sota"
                             class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                             loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-80 group-hover:opacity-90 transition-opacity"></div>
                        <div class="absolute top-3 left-3">
                            <span class="text-xs font-bold text-white bg-brand/90 backdrop-blur-xs px-3 py-1 rounded-full shadow-sm">
                                PLBN Sota
                            </span>
                        </div>
                        <div class="absolute bottom-3 right-3 text-white/90 text-xs flex items-center gap-1 bg-black/40 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <span>Klik untuk Perbesar</span>
                        </div>
                    </div>
                    <div class="p-5 space-y-2 flex-1 flex flex-col justify-between bg-white">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                                Kedaulatan &amp; Hub Perbatasan
                            </span>
                            <h3 class="font-extrabold text-ink text-base mt-2 group-hover:text-brand transition-colors">
                                Pos Lintas Batas Negara (PLBN) Sota
                            </h3>
                            <p class="text-xs text-muted mt-1 leading-relaxed">
                                Gerbang perbatasan modern yang megah, menjadi pusat integrasi kepabeanan, imigrasi, karantina, dan keamanan (CIQS), sekaligus simbol kedaulatan dan kebanggaan Indonesia di batas darat Papua Selatan.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-line/70 flex items-center justify-between text-xs text-muted">
                            <span>📍 Ujung Poros Trans-Papua</span>
                            <span class="font-semibold text-brand group-hover:underline">Buka Detail →</span>
                        </div>
                    </div>
                </div>

                {{-- Foto 2: Titik Nol Kilometer Merauke - Sabang --}}
                <div class="group bg-surface border border-line rounded-2xl overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col cursor-pointer"
                     @click="openLightbox('{{ asset('images/0km-sota.jpg') }}', 'Monumen Titik Nol Kilometer Merauke – Sabang', 'Landmark Ikonik &amp; Wisata', 'Monumen bersejarah di garis batas timur Indonesia yang menjadi simbol keutuhan NKRI dan destinasi wisata favorit bagi pengunjung dari berbagai penjuru tanah air.')">
                    <div class="relative overflow-hidden aspect-[16/10] bg-paper">
                        <img src="{{ asset('images/0km-sota.jpg') }}"
                             alt="Titik Nol Kilometer Sota Merauke"
                             class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                             loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-80 group-hover:opacity-90 transition-opacity"></div>
                        <div class="absolute top-3 left-3">
                            <span class="text-xs font-bold text-white bg-blue-600/90 backdrop-blur-xs px-3 py-1 rounded-full shadow-sm">
                                Titik 0 KM
                            </span>
                        </div>
                        <div class="absolute bottom-3 right-3 text-white/90 text-xs flex items-center gap-1 bg-black/40 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <span>Klik untuk Perbesar</span>
                        </div>
                    </div>
                    <div class="p-5 space-y-2 flex-1 flex flex-col justify-between bg-white">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                Landmark Ikonik &amp; Wisata
                            </span>
                            <h3 class="font-extrabold text-ink text-base mt-2 group-hover:text-brand transition-colors">
                                Monumen Titik Nol Kilometer Sota
                            </h3>
                            <p class="text-xs text-muted mt-1 leading-relaxed">
                                Landmark kebanggaan bangsa yang menandai tapal batas paling timur Indonesia dari rangkaian Sabang sampai Merauke. Menjadi ikon destinasi pariwisata kebangsaan yang ramai dikunjungi.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-line/70 flex items-center justify-between text-xs text-muted">
                            <span>📍 Kawasan Wisata PLBN Sota</span>
                            <span class="font-semibold text-brand group-hover:underline">Buka Detail →</span>
                        </div>
                    </div>
                </div>

                {{-- Foto 3: Pertanian & Ketahanan Pangan Sota --}}
                <div class="group bg-surface border border-line rounded-2xl overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col cursor-pointer"
                     @click="openLightbox('{{ asset('images/sotapetani.jpg') }}', 'Aktivitas Tani &amp; Agrikultur Masyarakat', 'Lumbung Pangan Strategis', 'Petani lokal Kampung Sota dengan hasil panen jagung manis dan palawija. Potensi agrikultur ini menjadi tumpuan ketahanan pangan lokal di perbatasan.')">
                    <div class="relative overflow-hidden aspect-[16/10] bg-paper">
                        <img src="{{ asset('images/sotapetani.jpg') }}"
                             alt="Aktivitas Pertanian Warga Kampung Sota"
                             class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                             loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-80 group-hover:opacity-90 transition-opacity"></div>
                        <div class="absolute top-3 left-3">
                            <span class="text-xs font-bold text-white bg-emerald-600/90 backdrop-blur-xs px-3 py-1 rounded-full shadow-sm">
                                Pertanian
                            </span>
                        </div>
                        <div class="absolute bottom-3 right-3 text-white/90 text-xs flex items-center gap-1 bg-black/40 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <span>Klik untuk Perbesar</span>
                        </div>
                    </div>
                    <div class="p-5 space-y-2 flex-1 flex flex-col justify-between bg-white">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                Lumbung Pangan Strategis
                            </span>
                            <h3 class="font-extrabold text-ink text-base mt-2 group-hover:text-brand transition-colors">
                                Aktivitas Tani &amp; Agrikultur Masyarakat
                            </h3>
                            <p class="text-xs text-muted mt-1 leading-relaxed">
                                Keuletan para petani Sota dalam mengelola lahan produktif menghasilkan komoditas palawija, jagung unggul, sayur-mayur, dan hasil kebun segar yang menyuplai konsumsi lokal perbatasan hingga pasar Merauke.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-line/70 flex items-center justify-between text-xs text-muted">
                            <span>📍 Koridor Pertanian Jalur A &amp; B</span>
                            <span class="font-semibold text-brand group-hover:underline">Buka Detail →</span>
                        </div>
                    </div>
                </div>

                {{-- Foto 4: Pelintas Batas Tradisional & Niaga --}}
                <div class="group bg-surface border border-line rounded-2xl overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col cursor-pointer"
                     @click="openLightbox('{{ asset('images/pelintas.jpg') }}', 'Dinamika Niaga &amp; Pelintas Batas Tradisional', 'Pasar Niaga &amp; Budaya', 'Interaksi perekonomian perbatasan di mana warga Papua Nugini melintas secara tradisional untuk berbelanja kebutuhan pokok di pasar dan kios warga Kampung Sota.')">
                    <div class="relative overflow-hidden aspect-[16/10] bg-paper">
                        <img src="{{ asset('images/pelintas.jpg') }}"
                             alt="Dinamika Pelintas Batas dan Niaga di Sota"
                             class="w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
                             loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-80 group-hover:opacity-90 transition-opacity"></div>
                        <div class="absolute top-3 left-3">
                            <span class="text-xs font-bold text-white bg-amber-600/90 backdrop-blur-xs px-3 py-1 rounded-full shadow-sm">
                                Pasar Batas
                            </span>
                        </div>
                        <div class="absolute bottom-3 right-3 text-white/90 text-xs flex items-center gap-1 bg-black/40 backdrop-blur-xs px-2.5 py-1 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <span>Klik untuk Perbesar</span>
                        </div>
                    </div>
                    <div class="p-5 space-y-2 flex-1 flex flex-col justify-between bg-white">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                Pasar Niaga &amp; Budaya
                            </span>
                            <h3 class="font-extrabold text-ink text-base mt-2 group-hover:text-brand transition-colors">
                                Dinamika Niaga &amp; Pelintas Batas Tradisional
                            </h3>
                            <p class="text-xs text-muted mt-1 leading-relaxed">
                                Geliat interaksi sosial dan perekonomian rakyat yang unik di perbatasan RI-PNG. Masyarakat lintas batas saling memenuhi kebutuhan sandang, pangan, dan komoditas harian dengan aman dan bersahabat.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-line/70 flex items-center justify-between text-xs text-muted">
                            <span>📍 Koridor Poros Trans &amp; Batas Negara</span>
                            <span class="font-semibold text-brand group-hover:underline">Buka Detail →</span>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        {{-- Call To Action Peta Interaktif --}}
        <div class="card p-6 sm:p-8 bg-gradient-to-r from-teal-900 to-slate-900 text-white flex flex-col sm:flex-row items-center justify-between gap-6 rounded-2xl shadow-md">
            <div class="space-y-1 text-center sm:text-left">
                <h3 class="text-lg sm:text-xl font-extrabold">Ingin Melihat Sebaran Fasilitas &amp; Koordinat di Peta?</h3>
                <p class="text-xs sm:text-sm text-slate-300">
                    Buka peta interaktif Kampung Sota lengkap dengan 26 titik fasilitas resmi dan data kerentanan sosial ekonomi.
                </p>
            </div>
            <a href="/#peta-kampung" class="btn-primary !min-h-[44px] !py-2.5 !px-6 !text-xs font-bold shadow-lg whitespace-nowrap">
                Buka Peta Interaktif Sota →
            </a>
        </div>

    </div>

    {{-- ============================================================
         MODAL LIGHTBOX FULL RESOLUTION FOTO
         ============================================================ --}}
    <div x-show="lightboxOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4"
         @keydown.escape.window="lightboxOpen = false"
         x-cloak>
        <div class="relative max-w-4xl w-full bg-slate-900 text-white rounded-3xl overflow-hidden shadow-2xl border border-white/10"
             @click.outside="lightboxOpen = false">
            <button @click="lightboxOpen = false"
                    class="absolute top-4 right-4 z-20 h-10 w-10 rounded-full bg-black/60 hover:bg-black text-white flex items-center justify-center transition-colors">
                ✕
            </button>
            <div class="relative aspect-[16/10] bg-black">
                <img :src="lightboxImg" :alt="lightboxTitle" class="w-full h-full object-contain">
            </div>
            <div class="p-6 sm:p-7 space-y-2 bg-slate-900">
                <span class="text-xs font-bold text-teal-400 uppercase tracking-wider block" x-text="lightboxTag"></span>
                <h3 class="text-lg sm:text-2xl font-black text-white" x-text="lightboxTitle"></h3>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed" x-text="lightboxDesc"></p>
            </div>
        </div>
    </div>
</div>
@endsection
