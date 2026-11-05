@extends('layouts.public')

@section('title', 'Profil Kampung Sota — Distrik Sota, Kabupaten Merauke')
@section('meta-description', 'Profil resmi Kampung Sota, Distrik Sota, Merauke: Kilas sejarah, visi-misi, struktur aparatur pemerintah kampung tahun 2026, batas wilayah geografis, dan potensi agrikultur perbatasan RI-PNG.')

@section('content')
<div class="bg-paper min-h-screen" x-data="{
    modalStrukturOpen: false
}">

    {{-- ============================================================
         1. HERO HEADER PROFIL KAMPUNG SOTA
         ============================================================ --}}
    <section class="relative w-full overflow-hidden bg-slate-900 text-white pt-24 pb-20 sm:pt-32 sm:pb-28 border-b border-line/40 shadow-sm"
             style="background-image: linear-gradient(180deg, rgba(15, 23, 42, 0.78) 0%, rgba(15, 23, 42, 0.90) 100%), url('{{ asset('images/kantor_kp.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        
        {{-- Ambient Glow --}}
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-transparent to-black/40 pointer-events-none"></div>
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-brand/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 text-center space-y-6">
            {{-- Official Badge (Breadcrumb dihilangkan sesuai SS1) --}}
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-white/10 text-white border border-white/20 backdrop-blur-md shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Pemerintah Kampung Sota • Distrik Sota, Kabupaten Merauke</span>
            </div>

            {{-- Judul Utama --}}
            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight drop-shadow-md">
                Profil Resmi Kampung Sota
            </h1>

            {{-- Deskripsi --}}
            <p class="text-white/90 text-sm sm:text-base leading-relaxed max-w-3xl mx-auto font-normal">
                Mengenal lebih dekat beranda terdepan kedaulatan Negara Kesatuan Republik Indonesia di ufuk timur Papua Selatan: pusat ketahanan pangan agrikultur, sentra interaksi perdagangan lintas batas RI–Papua Nugini, dan benteng kelestarian alam sabana Wasur.
            </p>

            {{-- Ringkasan Statistik Singkat --}}
            <div class="pt-4 grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-3xl mx-auto">
                <div class="p-3.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-center">
                    <span class="block text-[11px] font-semibold text-slate-300 uppercase tracking-wider">Luas Wilayah</span>
                    <strong class="text-lg sm:text-xl font-black text-white">669,69 <span class="text-xs font-bold text-teal-300">km²</span></strong>
                </div>
                <div class="p-3.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-center">
                    <span class="block text-[11px] font-semibold text-slate-300 uppercase tracking-wider">Total Penduduk</span>
                    <strong class="text-lg sm:text-xl font-black text-white">1.686 <span class="text-xs font-bold text-teal-300">Jiwa</span></strong>
                </div>
                <div class="p-3.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-center">
                    <span class="block text-[11px] font-semibold text-slate-300 uppercase tracking-wider">Struktur Wilayah</span>
                    <strong class="text-lg sm:text-xl font-black text-white">12 RT <span class="text-xs font-normal text-slate-300">/ 3 RW</span></strong>
                </div>
                <div class="p-3.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 text-center">
                    <span class="block text-[11px] font-semibold text-slate-300 uppercase tracking-wider">Fasilitas Publik</span>
                    <strong class="text-lg sm:text-xl font-black text-white">26 <span class="text-xs font-bold text-teal-300">Titik Terdata</span></strong>
                </div>
            </div>
        </div>
    </section>

    {{-- Container Konten Utama --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">

        {{-- ============================================================
             2. KILAS SEJARAH & NILAI KEHIDUPAN KAMPUNG SOTA (SS1)
             ============================================================ --}}
        <section class="card p-6 sm:p-8 space-y-6 rounded-2xl shadow-sm border border-line bg-white">
            <div class="flex items-center gap-2.5 pb-3 border-b border-line">
                <div class="w-9 h-9 rounded-xl bg-brand/10 text-brand flex items-center justify-center shrink-0 border border-brand/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                        <path d="M6 6h10"/>
                        <path d="M6 10h10"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-ink">Kilas Sejarah &amp; Identitas Wilayah</h2>
                    <p class="text-xs text-muted">Rekam jejak permukiman adat, program perintis, dan transformasi beranda perbatasan negara</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                {{-- Narasi Sejarah --}}
                <div class="lg:col-span-7 space-y-4 text-xs sm:text-sm text-ink/85 leading-relaxed">
                    <p>
                        <strong>Kampung Sota</strong> terletak di Distrik Sota, Kabupaten Merauke, Provinsi Papua Selatan. Pada mulanya, wilayah ini merupakan tanah ulayat dan permukiman leluhur <strong>Masyarakat Adat Suku Kanum</strong> yang hidup harmonis dengan ekosistem hutan sabana Wasur, mengandalkan kearifan lokal berburu secara tradisional, berkebun umbi-umbian, dan menjaga tatanan alam.
                    </p>
                    <p>
                        Seiring berjalannya waktu pada dekade 1980-an, kawasan Sota mulai menerima penempatan warga transmigrasi dari berbagai penjuru nusantara. Terbukanya koridor Jalan Poros Trans-Papua Merauke–Sota mempercepat integrasi sosial ekonomi, menjadikan Sota sebagai ruang temu kultural yang damai antara masyarakat adat Kanum dengan etnis Jawa, Maluku, Nusa Tenggara, Toraja, dan suku Nusantara lainnya.
                    </p>
                    <p>
                        Kini, Kampung Sota telah bermetamorfosis menjadi ikon kebanggaan nasional dengan beroperasinya <strong>Pos Lintas Batas Negara (PLBN) Terpadu Sota</strong> yang diresmikan Presiden RI. Tidak hanya menjadi tapal batas fisik pertahanan negara, Sota adalah episentrum persahabatan diplomatik antarwarga di perbatasan Republik Indonesia dan Papua Nugini (PNG).
                    </p>
                </div>

                {{-- Nilai & Karakteristik Kultural Sota (Ikon Lucide murni) --}}
                <div class="lg:col-span-5 space-y-3.5">
                    <div class="p-4 rounded-xl bg-slate-50 border border-line hover:border-brand/40 transition-colors space-y-1.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0 border border-teal-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                            <h4 class="text-xs sm:text-sm font-bold text-ink">Harmoni &amp; Toleransi Kebinekaan</h4>
                        </div>
                        <p class="text-xs text-muted leading-relaxed pl-9">
                            Masyarakat asli Suku Kanum dan pendatang hidup berdampingan dalam semangat persaudaraan sejati, saling menghormati tradisi adat dan perayaan hari besar keagamaan.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-line hover:border-brand/40 transition-colors space-y-1.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M7 20h10"/>
                                    <path d="M10 20c5.5-2.5.8-6.4 3-10"/>
                                    <path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/>
                                    <path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>
                                </svg>
                            </div>
                            <h4 class="text-xs sm:text-sm font-bold text-ink">Kemandirian Agrikultur Perbatasan</h4>
                        </div>
                        <p class="text-xs text-muted leading-relaxed pl-9">
                            Tanah Sota yang luas dikelola sebagai lumbung pangan lokal melalui budidaya padi, tanaman hortikultura palawija, serta pengolahan minyak kayu putih khas Merauke.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-50 border border-line hover:border-brand/40 transition-colors space-y-1.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0 border border-blue-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                    <polyline points="9 12 11 14 15 10"/>
                                </svg>
                            </div>
                            <h4 class="text-xs sm:text-sm font-bold text-ink">Penjaga Kedaulatan &amp; Tapal Batas</h4>
                        </div>
                        <p class="text-xs text-muted leading-relaxed pl-9">
                            Masyarakat Sota bahu-membahu bersama Satgas Pamtas TNI AD dan Kepolisian menjaga keamanan kawasan perbatasan serta menyambut pelintas batas dengan ramah.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================
             3. VISI & MISI PEMERINTAH KAMPUNG SOTA
             ============================================================ --}}
        <section class="card p-6 sm:p-8 space-y-6 rounded-2xl shadow-sm border border-line bg-white">
            <div class="flex items-center gap-2.5 pb-3 border-b border-line">
                <div class="w-9 h-9 rounded-xl bg-brand/10 text-brand flex items-center justify-center shrink-0 border border-brand/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <circle cx="12" cy="12" r="6"/>
                        <circle cx="12" cy="12" r="2"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-ink">Visi &amp; Misi Kampung Sota</h2>
                    <p class="text-xs text-muted">Arah kebijakan, komitmen pelayanan aparatur, dan target pembangunan berkelanjutan</p>
                </div>
            </div>

            {{-- Kotak Visi --}}
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-900 to-slate-900 text-white p-6 sm:p-8 shadow-md">
                <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-teal-500/20 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 space-y-2 max-w-3xl">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-white/10 text-teal-300 border border-white/15">
                        <span>Visi Pembangunan Kampung Sota</span>
                    </span>
                    <h3 class="text-base sm:text-xl font-extrabold text-white leading-relaxed tracking-wide pt-1">
                        &ldquo;Terwujudnya Kampung Sota yang Maju, Mandiri, Berdaya Saing, dan Sejahtera Berbasis Potensi Pertanian dan Ekonomi Perbatasan yang Berbudaya.&rdquo;
                    </h3>
                    <p class="text-xs text-slate-300">
                        Menegaskan komitmen bahwa kemajuan perbatasan harus bermuara pada peningkatan kesejahteraan nyata setiap keluarga warga Sota tanpa meninggalkan kelestarian adat Kanum.
                    </p>
                </div>
            </div>

            {{-- 4 Pilar Misi --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-5 rounded-xl border border-line bg-paper/60 hover:bg-white hover:border-brand/40 transition-all space-y-2 group">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-brand text-white flex items-center justify-center text-xs font-black shadow-2xs">1</span>
                        <h4 class="text-xs sm:text-sm font-bold text-ink group-hover:text-brand transition-colors">Tata Kelola Pemerintahan Akuntabel</h4>
                    </div>
                    <p class="text-xs text-muted leading-relaxed pl-9">
                        Mewujudkan sistem administrasi kampung yang terbuka, pelayanan kependudukan yang cepat, serta pengelolaan dana desa (APBKam) yang transparan dan dapat dipertanggungjawabkan.
                    </p>
                </div>

                <div class="p-5 rounded-xl border border-line bg-paper/60 hover:bg-white hover:border-brand/40 transition-all space-y-2 group">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs font-black shadow-2xs">2</span>
                        <h4 class="text-xs sm:text-sm font-bold text-ink group-hover:text-blue-600 transition-colors">Penguatan Ekonomi Pertanian &amp; UMKM</h4>
                    </div>
                    <p class="text-xs text-muted leading-relaxed pl-9">
                        Mendukung modernisasi sarana pertanian warga, memfasilitasi pemasaran komoditas palawija dan minyak kayu putih, serta mengoptimalkan peluang pasar perbatasan PLBN.
                    </p>
                </div>

                <div class="p-5 rounded-xl border border-line bg-paper/60 hover:bg-white hover:border-brand/40 transition-all space-y-2 group">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-black shadow-2xs">3</span>
                        <h4 class="text-xs sm:text-sm font-bold text-ink group-hover:text-indigo-600 transition-colors">Pemerataan Infrastruktur Dasar &amp; Sanitasi</h4>
                    </div>
                    <p class="text-xs text-muted leading-relaxed pl-9">
                        Meningkatkan akses jalan permukiman di seluruh koridor (Jalur 1A, Jalur 1B, Poros Trans), perluasan akses air bersih layak minum, elektrifikasi listrik, dan jamban sehat warga.
                    </p>
                </div>

                <div class="p-5 rounded-xl border border-line bg-paper/60 hover:bg-white hover:border-brand/40 transition-all space-y-2 group">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-black shadow-2xs">4</span>
                        <h4 class="text-xs sm:text-sm font-bold text-ink group-hover:text-emerald-600 transition-colors">Pelestarian Adat, Kerukunan &amp; Keamanan</h4>
                    </div>
                    <p class="text-xs text-muted leading-relaxed pl-9">
                        Melestarikan nilai kearifan lokal Suku Kanum, membina toleransi antarumat beragama, dan menjaga ketertiban umum wilayah perbatasan bersama unsur TNI dan Polri.
                    </p>
                </div>
            </div>
        </section>

        {{-- ============================================================
             4. KONDISI GEOGRAFIS & BATAS WILAYAH (SS2)
             ============================================================ --}}
        <section class="card p-6 sm:p-8 space-y-6 rounded-2xl shadow-sm border border-line bg-white">
            <div class="flex items-center gap-2.5 pb-3 border-b border-line">
                <div class="w-9 h-9 rounded-xl bg-brand/10 text-brand flex items-center justify-center shrink-0 border border-brand/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-ink">Kondisi Geografis &amp; Batas Wilayah</h2>
                    <p class="text-xs text-muted">Posisi astronomis, topografi dataran sabana, dan perbatasan wilayah administratif</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
                {{-- Info Ringkas Geografi (WGS84 dihilangkan sesuai SS2) --}}
                <div class="lg:col-span-5 p-5 rounded-xl bg-slate-50 border border-line space-y-4 flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md text-[11px] font-bold bg-brand/10 text-brand border border-brand/20">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="2" y1="12" x2="22" y2="12"/>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                            </svg>
                            <span>Informasi Geografis</span>
                        </div>
                        <h3 class="text-sm font-extrabold text-ink">Karakteristik Fisik Wilayah</h3>
                        <p class="text-xs text-muted leading-relaxed">
                            Kampung Sota secara administratif memiliki luas wilayah sebesar <strong>669,69 km²</strong>, menjadikannya kampung dengan wilayah terluas di Distrik Sota. Berada pada koordinat lintang <strong>-8.4283° LS</strong> dan bujur <strong>141.0180° BT</strong> dengan topografi berupa dataran rendah berketinggian antara 10 – 35 meter di atas permukaan laut (mdpl), didominasi oleh tanah aluvial subur bercampur pasir kuarsa.
                        </p>
                        <p class="text-xs text-muted leading-relaxed">
                            Kawasan ini beriklim tropis monsun dengan musim hujan dan musim kemarau yang tegas. Kampung Sota merupakan zona penyangga utama (<em>buffer zone</em>) <strong>Taman Nasional Wasur</strong> yang kaya akan keanekaragaman flora sabana seperti pohon kayu putih (<em>Melaleuca</em>) dan fauna endemik seperti kangguru tanah (walabi) dan rumah rayap raksasa (<em>Musamus</em>).
                        </p>
                    </div>

                    <div class="pt-3 border-t border-line text-xs font-semibold text-ink flex items-center justify-between">
                        <span>Luas Wilayah: <strong class="text-brand">669,69 km²</strong> (Terluas)</span>
                        <span class="text-brand font-bold">Zona Waktu: WIT (UTC+9)</span>
                    </div>
                </div>

                {{-- Grid Batas 4 Penjuru Mata Angin (Badge Timur disesuaikan sesuai SS2) --}}
                <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    {{-- Utara --}}
                    <div class="p-4 rounded-xl border border-line bg-white shadow-2xs space-y-1.5 hover:border-brand/40 transition-colors">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-ink flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span> Batas Sebelah Utara
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted px-2 py-0.5 rounded bg-slate-100">Utara</span>
                        </div>
                        <p class="text-xs text-ink font-bold">Kampung Erambu &amp; Toray</p>
                        <span class="text-[11px] text-muted block">Berbatasan langsung dengan Kampung Erambu dan Kampung Toray</span>
                    </div>

                    {{-- Timur (Badge Internasional diubah jadi Timur sesuai SS2) --}}
                    <div class="p-4 rounded-xl border border-teal-200 bg-teal-50/40 shadow-2xs space-y-1.5 hover:border-brand transition-colors">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-brand flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Batas Sebelah Timur
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-white px-2 py-0.5 rounded bg-brand">Timur</span>
                        </div>
                        <p class="text-xs text-ink font-bold">Negara Papua Nugini (PNG)</p>
                        <span class="text-[11px] text-muted block">Garis tapal batas negara darat RI–PNG (Western Province)</span>
                    </div>

                    {{-- Selatan --}}
                    <div class="p-4 rounded-xl border border-line bg-white shadow-2xs space-y-1.5 hover:border-brand/40 transition-colors">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-ink flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span> Batas Sebelah Selatan
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted px-2 py-0.5 rounded bg-slate-100">Selatan</span>
                        </div>
                        <p class="text-xs text-ink font-bold">Kampung Rawa Biru</p>
                        <span class="text-[11px] text-muted block">Berbatasan langsung dengan wilayah Kampung Rawa Biru</span>
                    </div>

                    {{-- Barat --}}
                    <div class="p-4 rounded-xl border border-line bg-white shadow-2xs space-y-1.5 hover:border-brand/40 transition-colors">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-ink flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-purple-500"></span> Batas Sebelah Barat
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-muted px-2 py-0.5 rounded bg-slate-100">Barat</span>
                        </div>
                        <p class="text-xs text-ink font-bold">Distrik Merauke</p>
                        <span class="text-[11px] text-muted block">Berbatasan langsung dengan kawasan Distrik Merauke</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================
             5. STRUKTUR PEMERINTAHAN KAMPUNG SOTA TAHUN 2026 (SS3 & SS4)
             Rebuilt sesuai bagan resmi dari SS4
             ============================================================ --}}
        <section class="card p-6 sm:p-8 space-y-8 rounded-2xl shadow-sm border border-line bg-white">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-line gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand flex items-center justify-center shrink-0 border border-brand/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" x2="21" y1="22" y2="22"/>
                            <line x1="6" x2="6" y1="18"/>
                            <line x1="10" x2="10" y1="18"/>
                            <line x1="14" x2="14" y1="18"/>
                            <line x1="18" x2="18" y1="18"/>
                            <polygon points="12 2 20 7 4 7"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-extrabold text-ink">Struktur Pemerintahan &amp; Aparatur Kampung</h2>
                        <p class="text-xs text-muted">Bagan struktur organisasi resmi Pemerintah Kampung Sota Tahun 2026</p>
                    </div>
                </div>

                {{-- Tombol Lihat Dokumen Asli Bagan SS4 --}}
                <button type="button"
                        @click="modalStrukturOpen = true"
                        class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-bold text-brand bg-brand/10 hover:bg-brand hover:text-white rounded-xl transition-all self-start sm:self-auto border border-brand/20 hover:shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                        <circle cx="9" cy="9" r="2"/>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                    </svg>
                    <span>Lihat Bagan Resmi (Arsip)</span>
                </button>
            </div>

            {{-- Kop Bagan Organisasi Resmi --}}
            <div class="py-2.5 px-4 rounded-xl bg-slate-50 border border-line text-center max-w-xl mx-auto">
                <h3 class="text-xs sm:text-sm font-black tracking-wide text-ink uppercase leading-snug">
                    STRUKTUR PEMERINTAHAN KAMPUNG SOTA TAHUN 2026
                </h3>
                <p class="text-[11px] text-muted font-medium mt-0.5">Distrik Sota • Kabupaten Merauke • Papua Selatan</p>
            </div>

            {{-- Organogram Tree Visual Interaktif --}}
            <div class="w-full overflow-x-auto pb-4 pt-2">
                <div class="min-w-[720px] max-w-4xl mx-auto space-y-6">

                    {{-- LEVEL 1: KEPALA KAMPUNG --}}
                    <div class="flex flex-col items-center">
                        <div class="w-72 p-4 rounded-2xl bg-gradient-to-r from-teal-900 to-slate-900 text-white border-2 border-teal-600 shadow-lg text-center space-y-1 relative group hover:scale-[1.02] transition-transform">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-white/20 text-teal-200 border border-white/20">
                                Kepala Kampung
                            </span>
                            <h4 class="text-sm font-black text-white tracking-wide pt-0.5">
                                CONRADA MURNAN
                            </h4>
                            <p class="text-[10px] text-slate-300">Penanggung Jawab Penyelenggaraan Wilayah</p>
                        </div>

                        {{-- Line Connector Vertikal Level 1 ke Level 2 --}}
                        <div class="w-0.5 h-6 bg-slate-400"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-500 -mt-1"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>

                    {{-- LEVEL 2: SEKRETARIS KAMPUNG --}}
                    <div class="flex flex-col items-center">
                        <div class="w-72 p-4 rounded-2xl bg-white border-2 border-blue-500 shadow-md text-center space-y-1 relative group hover:scale-[1.02] transition-transform">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                Sekretaris Kampung
                            </span>
                            <h4 class="text-sm font-black text-ink tracking-wide pt-0.5">
                                NORCE MAHUZE
                            </h4>
                            <p class="text-[10px] text-muted">Koordinator Administrasi &amp; Ketatausahaan</p>
                        </div>

                        {{-- Line Connector Vertikal dari Level 2 ke Percabangan 3 Seksi --}}
                        <div class="w-0.5 h-6 bg-slate-400"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-500 -mt-1"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>

                    {{-- PERCABANGAN HORIZONTAL 3 SEKSI --}}
                    <div class="relative pt-2">
                        {{-- Garis Horizontal Penghubung 3 Kolom --}}
                        <div class="hidden sm:block absolute top-0 left-[16.666%] right-[16.666%] h-0.5 bg-slate-300"></div>

                        {{-- 3 Kolom Seksi & Urusan Pemerintahan --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-3">

                            {{-- KOLOM 1: BIDANG PEMERINTAHAN & KEUANGAN --}}
                            <div class="flex flex-col items-center space-y-4">
                                {{-- Kasi Pemerintahan --}}
                                <div class="w-full p-4 rounded-xl border border-line bg-slate-50/80 shadow-2xs text-center space-y-1 hover:border-brand transition-colors">
                                    <span class="inline-block px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider bg-slate-200 text-slate-700">
                                        Kasi Pemerintahan
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-extrabold text-ink">
                                        JHONI ARAFAT
                                    </h4>
                                    <p class="text-[10px] text-muted">Ketentraman, Kependudukan &amp; Pertanahan</p>
                                </div>

                                {{-- Connector Down --}}
                                <div class="flex flex-col items-center -my-2">
                                    <div class="w-0.5 h-4 bg-slate-300"></div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-400"><polyline points="6 9 12 15 18 9"/></svg>
                                </div>

                                {{-- Kaur Keuangan --}}
                                <div class="w-full p-4 rounded-xl border border-line bg-white shadow-2xs text-center space-y-1 hover:border-brand transition-colors">
                                    <span class="inline-block px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Kaur Keuangan
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-extrabold text-ink">
                                        GULTRUIDA RUMSAUR
                                    </h4>
                                    <p class="text-[10px] text-muted">Perbendaharaan, Kas &amp; Anggaran APBKam</p>
                                </div>
                            </div>

                            {{-- KOLOM 2: BIDANG KESEJAHTERAAN & PEMBANGUNAN --}}
                            <div class="flex flex-col items-center space-y-4">
                                {{-- Kasi Kesejahteraan --}}
                                <div class="w-full p-4 rounded-xl border border-line bg-slate-50/80 shadow-2xs text-center space-y-1 hover:border-brand transition-colors">
                                    <span class="inline-block px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider bg-slate-200 text-slate-700">
                                        Kasi Kesejahteraan
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-extrabold text-ink">
                                        OKTOVIANUS KANGGOPA MBANGGU
                                    </h4>
                                    <p class="text-[10px] text-muted">Kesejahteraan Sosial, Kesehatan &amp; SVI</p>
                                </div>

                                {{-- Connector Down --}}
                                <div class="flex flex-col items-center -my-2">
                                    <div class="w-0.5 h-4 bg-slate-300"></div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-400"><polyline points="6 9 12 15 18 9"/></svg>
                                </div>

                                {{-- Kaur Perencanaan dan Pembangunan --}}
                                <div class="w-full p-4 rounded-xl border border-line bg-white shadow-2xs text-center space-y-1 hover:border-brand transition-colors">
                                    <span class="inline-block px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Kaur Perencanaan &amp; Pembangunan
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-extrabold text-ink">
                                        MARSEL MARTEN NDIKEN
                                    </h4>
                                    <p class="text-[10px] text-muted">Perencanaan Musrenbang &amp; Fisik Desa</p>
                                </div>
                            </div>

                            {{-- KOLOM 3: BIDANG PELAYANAN & OPERATOR SISTEM --}}
                            <div class="flex flex-col items-center space-y-4">
                                {{-- Kasi Pelayanan 1 --}}
                                <div class="w-full p-4 rounded-xl border border-line bg-slate-50/80 shadow-2xs text-center space-y-1 hover:border-brand transition-colors">
                                    <span class="inline-block px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider bg-slate-200 text-slate-700">
                                        Kasi Pelayanan
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-extrabold text-ink">
                                        ABRAHAM F. NDIKEN
                                    </h4>
                                    <p class="text-[10px] text-muted">Pelayanan Umum &amp; Pengaduan Masyarakat</p>
                                </div>

                                {{-- Connector Down --}}
                                <div class="flex flex-col items-center -my-2">
                                    <div class="w-0.5 h-4 bg-slate-300"></div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-400"><polyline points="6 9 12 15 18 9"/></svg>
                                </div>

                                {{-- Kasi Pelayanan 2 --}}
                                <div class="w-full p-4 rounded-xl border border-line bg-white shadow-2xs text-center space-y-1 hover:border-brand transition-colors">
                                    <span class="inline-block px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                                        Kasi Pelayanan
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-extrabold text-ink">
                                        KUSNAENI
                                    </h4>
                                    <p class="text-[10px] text-muted">Registrasi Sipil &amp; Administrasi Warga</p>
                                </div>

                                {{-- Connector Down --}}
                                <div class="flex flex-col items-center -my-2">
                                    <div class="w-0.5 h-4 bg-slate-300"></div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-400"><polyline points="6 9 12 15 18 9"/></svg>
                                </div>

                                {{-- Operator --}}
                                <div class="w-full p-4 rounded-xl border border-line bg-white shadow-2xs text-center space-y-1 hover:border-brand transition-colors">
                                    <span class="inline-block px-2 py-0.5 rounded text-[9px] font-extrabold uppercase tracking-wider bg-slate-200 text-slate-700">
                                        Operator
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-extrabold text-ink">
                                        HARPIYANSA
                                    </h4>
                                    <p class="text-[10px] text-muted">Operator Sistem WebGIS &amp; Data Kependudukan</p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- ============================================================
             6. POTENSI UNGGULAN & 4 SEKTOR STRATEGIS (Ikon Lucide murni)
             ============================================================ --}}
        <section class="card p-6 sm:p-8 space-y-6 rounded-2xl shadow-sm border border-line bg-white">
            <div class="flex items-center gap-2.5 pb-3 border-b border-line">
                <div class="w-9 h-9 rounded-xl bg-brand/10 text-brand flex items-center justify-center shrink-0 border border-brand/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-ink">Potensi Unggulan Kampung Sota</h2>
                    <p class="text-xs text-muted">Empat pilar penggerak ekonomi, kearifan alam, dan interaksi perbatasan</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Sektor 1: Pertanian & Hortikultura --}}
                <div class="p-5 rounded-2xl border border-line bg-paper/50 hover:bg-white hover:border-brand/40 transition-all space-y-3 group shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 border border-emerald-200 group-hover:scale-105 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7 20h10"/>
                            <path d="M10 20c5.5-2.5.8-6.4 3-10"/>
                            <path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/>
                            <path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-extrabold text-ink group-hover:text-brand transition-colors">
                        Lumbung Agrikultur &amp; Minyak Kayu Putih
                    </h3>
                    <p class="text-xs text-muted leading-relaxed">
                        Kampung Sota memiliki hamparan lahan pertanian subur untuk budidaya padi sawah, jagung manis, ubi jalar, sayur-mayur, dan pisang tanduk. Selain itu, kawasan ini terkenal dengan produk khas minyak kayu putih murni hasil penyulingan daun kayu putih alami dari hutan perbatasan.
                    </p>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span class="text-[10px] font-semibold bg-white border border-line px-2 py-0.5 rounded text-ink">Padi &amp; Palawija</span>
                        <span class="text-[10px] font-semibold bg-white border border-line px-2 py-0.5 rounded text-ink">Minyak Kayu Putih</span>
                        <span class="text-[10px] font-semibold bg-white border border-line px-2 py-0.5 rounded text-ink">Pisang Tanduk</span>
                    </div>
                </div>

                {{-- Sektor 2: Perdagangan & Lintas Batas PLBN --}}
                <div class="p-5 rounded-2xl border border-line bg-paper/50 hover:bg-white hover:border-brand/40 transition-all space-y-3 group shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 border border-blue-200 group-hover:scale-105 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/>
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                            <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/>
                            <path d="M2 7h20"/>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-extrabold text-ink group-hover:text-blue-600 transition-colors">
                        Pasar Tradisional &amp; PLBN Terpadu
                    </h3>
                    <p class="text-xs text-muted leading-relaxed">
                        Keberadaan PLBN Sota membuka peluang niaga lintas batas darat. Warga dari negara tetangga Papua Nugini (desa Weam dan sekitarnya) secara berkala menyeberang membawa pas lintas batas untuk berbelanja kebutuhan pokok sehari-hari di pasar perbatasan dan warung warga Sota.
                    </p>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span class="text-[10px] font-semibold bg-white border border-line px-2 py-0.5 rounded text-ink">Pasar Lintas Batas</span>
                        <span class="text-[10px] font-semibold bg-white border border-line px-2 py-0.5 rounded text-ink">Pusat Oleh-oleh</span>
                        <span class="text-[10px] font-semibold bg-white border border-line px-2 py-0.5 rounded text-ink">Pelintas Batas Tradisional</span>
                    </div>
                </div>

                {{-- Sektor 3: Pariwisata & Landmark Sejarah --}}
                <div class="p-5 rounded-2xl border border-line bg-paper/50 hover:bg-white hover:border-brand/40 transition-all space-y-3 group shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 border border-purple-200 group-hover:scale-105 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-extrabold text-ink group-hover:text-purple-600 transition-colors">
                        Destinasi Titik Nol KM &amp; Wisata Sejarah
                    </h3>
                    <p class="text-xs text-muted leading-relaxed">
                        Monumen <strong>Titik Nol Kilometer Merauke – Sabang</strong> dan Tugu Kembar Sabang–Merauke menjadi destinasi wisata sejarah primadona bagi wisatawan domestik maupun mancanegara. Pengunjung dapat menikmati galeri kerajinan anyaman Suku Kanum dan taman rekreasi perbatasan.
                    </p>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span class="text-[10px] font-semibold bg-white border border-line px-2 py-0.5 rounded text-ink">Tugu Titik Nol KM</span>
                        <span class="text-[10px] font-semibold bg-white border border-line px-2 py-0.5 rounded text-ink">Tugu Kembar</span>
                        <span class="text-[10px] font-semibold bg-white border border-line px-2 py-0.5 rounded text-ink">Kerajinan Kanum</span>
                    </div>
                </div>

                {{-- Sektor 4: Konservasi Alam & Ekowisata Wasur --}}
                <div class="p-5 rounded-2xl border border-line bg-paper/50 hover:bg-white hover:border-brand/40 transition-all space-y-3 group shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 border border-amber-200 group-hover:scale-105 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 10v.2A3 3 0 0 1 8.9 16H5a3 3 0 0 1-1-5.8V10a3 3 0 0 1 6 0Z"/>
                            <path d="M7 16v6"/>
                            <path d="M13 19v3"/>
                            <path d="M12 19h8.3a1 1 0 0 0 .7-1.7L18 14h.3a1 1 0 0 0 .7-1.7L16 9h.2a1 1 0 0 0 .8-1.7L13 3l-4 4.3a1 1 0 0 0 .8 1.7H10"/>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-extrabold text-ink group-hover:text-amber-600 transition-colors">
                        Ekowisata Sabana &amp; Sarang Rayap Musamus
                    </h3>
                    <p class="text-xs text-muted leading-relaxed">
                        Menjadi pintu gerbang timur Taman Nasional Wasur (dikenal sebagai <em>Serengeti of Papua</em>), menyuguhkan pesona gundukan rumah rayap raksasa (<em>Musamus</em>) setinggi 3–5 meter, kolam rawa alami habitat burung migran, serta suaka kangguru tanah liar.
                    </p>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span class="text-[10px] font-semibold bg-white border border-line px-2 py-0.5 rounded text-ink">Rumah Musamus</span>
                        <span class="text-[10px] font-semibold bg-white border border-line px-2 py-0.5 rounded text-ink">TN Wasur</span>
                        <span class="text-[10px] font-semibold bg-white border border-line px-2 py-0.5 rounded text-ink">Birdwatching</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================
             7. SARANA & FASILITAS UMUM (26 TITIK TERDATA DI WEBGIS)
             Ikon Lucide murni, tanpa emoticon
             ============================================================ --}}
        <section class="card p-6 sm:p-8 space-y-6 rounded-2xl shadow-sm border border-line bg-white">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-line gap-2">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-brand/10 text-brand flex items-center justify-center shrink-0 border border-brand/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-extrabold text-ink">Sarana &amp; Prasarana Pelayanan Umum</h2>
                        <p class="text-xs text-muted">Sebaran 26 titik fasilitas yang telah terpetakan secara geospasial pada WebGIS SOTA</p>
                    </div>
                </div>
                <a href="/#peta-kampung" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-brand bg-brand/10 hover:bg-brand hover:text-white rounded-lg transition-colors self-start sm:self-auto">
                    <span>Lihat di Peta Interaktif</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-center">
                {{-- Pendidikan --}}
                <div class="p-3.5 rounded-xl bg-slate-50 border border-line space-y-1.5 hover:border-brand/40 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 flex items-center justify-center mx-auto border border-blue-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                    </div>
                    <strong class="text-xs text-ink block font-bold">Pendidikan</strong>
                    <span class="text-[10px] text-muted block">SDN, SMPN 11, SMKN 1</span>
                </div>

                {{-- Kesehatan --}}
                <div class="p-3.5 rounded-xl bg-slate-50 border border-line space-y-1.5 hover:border-brand/40 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 flex items-center justify-center mx-auto border border-rose-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2"/>
                            <path d="M8 12h8"/>
                            <path d="M12 8v8"/>
                        </svg>
                    </div>
                    <strong class="text-xs text-ink block font-bold">Kesehatan</strong>
                    <span class="text-[10px] text-muted block">Pustu Sota, Karantina</span>
                </div>

                {{-- Tempat Ibadah --}}
                <div class="p-3.5 rounded-xl bg-slate-50 border border-line space-y-1.5 hover:border-brand/40 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-600 flex items-center justify-center mx-auto border border-indigo-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3v18"/>
                            <path d="M8 8h8"/>
                            <path d="M4 21h16"/>
                        </svg>
                    </div>
                    <strong class="text-xs text-ink block font-bold">Tempat Ibadah</strong>
                    <span class="text-[10px] text-muted block">5 Gereja &amp; 1 Masjid</span>
                </div>

                {{-- Keamanan --}}
                <div class="p-3.5 rounded-xl bg-slate-50 border border-line space-y-1.5 hover:border-brand/40 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-slate-600/10 text-slate-700 flex items-center justify-center mx-auto border border-slate-600/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <strong class="text-xs text-ink block font-bold">Keamanan</strong>
                    <span class="text-[10px] text-muted block">Satgas Pamtas, Polsek</span>
                </div>

                {{-- Pemerintahan --}}
                <div class="p-3.5 rounded-xl bg-slate-50 border border-line space-y-1.5 hover:border-brand/40 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-teal-600 flex items-center justify-center mx-auto border border-teal-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" x2="21" y1="22" y2="22"/>
                            <line x1="6" x2="6" y1="18"/>
                            <line x1="10" x2="10" y1="18"/>
                            <line x1="14" x2="14" y1="18"/>
                            <line x1="18" x2="18" y1="18"/>
                            <polygon points="12 2 20 7 4 7"/>
                        </svg>
                    </div>
                    <strong class="text-xs text-ink block font-bold">Pemerintahan</strong>
                    <span class="text-[10px] text-muted block">Balai Kampung, PLBN</span>
                </div>

                {{-- Utilitas Publik --}}
                <div class="p-3.5 rounded-xl bg-slate-50 border border-line space-y-1.5 hover:border-brand/40 transition-colors">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center mx-auto border border-amber-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                    </div>
                    <strong class="text-xs text-ink block font-bold">Utilitas Publik</strong>
                    <span class="text-[10px] text-muted block">PLN, SPBU, Kantor Pos</span>
                </div>
            </div>
        </section>


        {{-- ============================================================
             9. CALL TO ACTION: JELAJAHI PETA & HUBUNGI KAMI
             ============================================================ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <a href="/" class="p-6 rounded-2xl bg-gradient-to-br from-teal-900 to-slate-900 text-white border border-teal-800 shadow-md flex items-center justify-between group hover:scale-[1.01] transition-transform">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-teal-300 uppercase tracking-wider block">WebGIS Spasial</span>
                    <h3 class="text-base font-extrabold text-white group-hover:text-teal-200 transition-colors">Eksplorasi Peta Fasilitas Sota</h3>
                    <p class="text-xs text-slate-300">Lihat sebaran 26 titik koordinat fasilitas publik di peta Leaflet.</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-white/10 group-hover:bg-brand group-hover:text-white flex items-center justify-center transition-colors shrink-0 ml-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                </span>
            </a>

            <a href="/kontak" class="p-6 rounded-2xl bg-white text-ink border border-line shadow-sm flex items-center justify-between group hover:border-brand/40 hover:scale-[1.01] transition-all">
                <div class="space-y-1">
                    <span class="text-[11px] font-bold text-brand uppercase tracking-wider block">Pelayanan Warga</span>
                    <h3 class="text-base font-extrabold text-ink group-hover:text-brand transition-colors">Hubungi Balai Kampung Sota</h3>
                    <p class="text-xs text-muted">Alamat kantor, nomor kontak WhatsApp, dan peta satelit lokasi kantor.</p>
                </div>
                <span class="w-10 h-10 rounded-xl bg-slate-100 group-hover:bg-brand group-hover:text-white flex items-center justify-center transition-colors shrink-0 ml-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                </span>
            </a>
        </div>

    </div>

    {{-- ============================================================
         MODAL / LIGHTBOX ARSIP BAGAN RESMI SS4
         ============================================================ --}}
    <div x-show="modalStrukturOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-cloak>
        <div class="relative bg-white rounded-3xl max-w-3xl w-full p-6 shadow-2xl border border-line space-y-4"
             @click.away="modalStrukturOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-line">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo_merauke.png') }}" alt="Merauke" class="w-6 h-8 object-contain">
                    <div>
                        <h4 class="text-sm font-bold text-ink">Arsip Bagan Struktur Pemerintahan Kampung Sota</h4>
                        <p class="text-[11px] text-muted">Dokumen resmi Pemerintah Kampung Sota Tahun 2026</p>
                    </div>
                </div>
                <button @click="modalStrukturOpen = false" class="p-2 text-muted hover:text-ink hover:bg-slate-100 rounded-full transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="rounded-xl overflow-hidden border border-line bg-slate-50 flex items-center justify-center p-2">
                <img src="{{ asset('images/struktur_sota_2026.png') }}" alt="Struktur Pemerintahan Kampung Sota Tahun 2026" class="max-h-[70vh] w-auto object-contain rounded-lg">
            </div>
            <div class="text-right">
                <button @click="modalStrukturOpen = false" class="btn-primary !py-2 !px-5 !text-xs font-bold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
