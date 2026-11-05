<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta-description', 'SOTA-GIS — Peta sebaran kerentanan sosial ekonomi masyarakat Kampung Sota, Distrik Sota, Kabupaten Merauke.')">
    <title>@yield('title', 'SOTA-GIS — Pemetaan Kerentanan Sosial Ekonomi Kampung Sota')</title>

    {{-- Plus Jakarta Sans (PRD 6.3) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    {{-- Favicon Browser --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logosmall.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-paper" x-data="{ mobileMenu: false }">

    {{-- ============================================================
         NAVBAR PUBLIK — 56px, putih 92% + blur (PRD 8.1)
         ============================================================ --}}
    <header class="fixed inset-x-0 top-0 z-[1000] flex h-16 items-center justify-between border-b border-line bg-white/95 px-4 backdrop-blur-md shadow-xs lg:px-8">
        {{-- Logo & Identitas Wilayah (SS1) --}}
        <a href="/" class="flex items-center gap-3 text-ink no-underline group">
            <img src="{{ asset('images/sotalogo.svg') }}" alt="Logo SOTA-GIS" class="h-12 w-12 rounded-xl object-contain shadow-xs shrink-0 group-hover:scale-105 transition-transform">
            <div class="flex flex-col">
                <span class="text-base font-extrabold tracking-tight text-ink group-hover:text-brand transition-colors leading-tight">SOTA-GIS</span>
                <span class="text-[11px] font-medium text-muted">Sistem Informasi Geografis Kampung Sota</span>
            </div>
        </a>

        {{-- Menu desktop (Home, Profil Kampung, Galeri, Kontak, Masuk) --}}
        <nav class="hidden items-center gap-2 md:flex" aria-label="Navigasi utama">
            <a href="/"
               class="rounded-lg px-3.5 py-2 text-sm font-semibold transition-colors {{ request()->is('/') ? 'text-brand font-bold bg-brand/10' : 'text-muted hover:bg-paper hover:text-ink' }}">
                Home
            </a>
            <a href="/tentang"
               class="rounded-lg px-3.5 py-2 text-sm font-semibold transition-colors {{ request()->is('tentang') ? 'text-brand font-bold bg-brand/10' : 'text-muted hover:bg-paper hover:text-ink' }}">
                Profil Kampung
            </a>
            <a href="/kontak"
               class="rounded-lg px-3.5 py-2 text-sm font-semibold transition-colors {{ request()->is('kontak') ? 'text-brand font-bold bg-brand/10' : 'text-muted hover:bg-paper hover:text-ink' }}">
                Kontak
            </a>
            <a href="/login"
               class="btn-primary ml-3 !min-h-[38px] !py-2 !px-5 !text-xs font-bold shadow-xs">
                Masuk
            </a>
        </nav>

        {{-- Hamburger mobile --}}
        <button @click="mobileMenu = !mobileMenu"
                class="inline-flex items-center justify-center h-10 w-10 rounded-md text-muted hover:bg-paper hover:text-ink md:hidden"
                aria-label="Buka menu navigasi">
            <svg x-show="!mobileMenu" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
            <svg x-show="mobileMenu" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-cloak><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
    </header>

    {{-- Menu mobile dropdown --}}
    <div x-show="mobileMenu"
         x-transition:enter="transition duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         @click.outside="mobileMenu = false"
         class="fixed inset-x-0 top-16 z-40 border-b border-line bg-surface p-4 shadow-[var(--shadow-modal)] md:hidden"
         x-cloak>
        <nav class="flex flex-col gap-1" aria-label="Navigasi utama mobile">
            <a href="/" class="rounded-md px-3 py-2.5 text-sm font-medium {{ request()->is('/') ? 'text-brand font-bold bg-brand/10' : 'text-ink hover:bg-paper' }} transition-colors">Home</a>
            <a href="/tentang" class="rounded-md px-3 py-2.5 text-sm font-medium {{ request()->is('tentang') ? 'text-brand font-bold bg-brand/10' : 'text-ink hover:bg-paper' }} transition-colors">Profil Kampung</a>
            <a href="/kontak" class="rounded-md px-3 py-2.5 text-sm font-medium {{ request()->is('kontak') ? 'text-brand font-bold bg-brand/10' : 'text-ink hover:bg-paper' }} transition-colors">Kontak</a>
            <a href="/login" class="rounded-md px-3 py-2.5 text-sm font-bold text-brand hover:bg-brand/10 transition-colors">Masuk</a>
        </nav>
    </div>

    {{-- ============================================================
         KONTEN — di bawah navbar
         ============================================================ --}}
    <main id="konten-utama">
        @yield('content')
    </main>

    {{-- ============================================================
         FOOTER PUBLIK — W-FULL, THEME GELAP (BG-SLATE-900 TEXT-WHITE)
         ============================================================ --}}
    <footer class="w-full bg-slate-900 text-white border-t border-slate-800 mt-auto">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            {{-- Bagian Atas Footer (Grid 2 Kolom) --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-16 pb-12">
                {{-- Kolom 1 (Kiri): Identitas SOTA-GIS --}}
                <div class="md:col-span-7 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/sotalogo.svg') }}" alt="Logo SOTA-GIS" class="h-11 w-11 rounded-xl object-contain shadow-sm shrink-0">
                        <div>
                            <span class="text-lg font-black tracking-wider text-white">SOTA-GIS</span>
                            <span class="block text-[11px] font-medium text-slate-400">Sistem Informasi Geografis Kampung Sota</span>
                        </div>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-lg">
                        Portal Sistem Informasi Geografis dan Pemetaan Demografi Kampung Sota, Distrik Sota, Kabupaten Merauke, Provinsi Papua Selatan.
                    </p>
                </div>

                {{-- Kolom 2 (Kanan): Kontak & Operasional --}}
                <div class="md:col-span-5 space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-200">KONTAK &amp; OPERASIONAL</h4>
                    <div class="space-y-3 text-xs sm:text-sm text-slate-400">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-teal-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Balai Kampung Sota, Jl. Trans Papua, Kab. Merauke</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-teal-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>(0971) 321456 / +62 812-3456-7890</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-teal-400 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>kontak@kampungsota.id</span>
                        </div>
                        <div class="flex items-start gap-2.5 pt-1">
                            <svg class="w-4 h-4 text-teal-400 mt-0.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <div>Senin – Jumat: 08.00 – 15.00 WIT</div>
                                <div class="text-[11px] text-slate-500 italic">Sabtu, Minggu &amp; Hari Libur Nasional: Tutup</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian Bawah Footer (Bottom Bar) --}}
            <div class="pt-8 border-t border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                <div>
                    &copy; 2026 Pemerintah Kampung Sota. Hak Cipta Dilindungi.
                </div>
                <div class="flex items-center gap-4">
                    <a href="/login" class="text-teal-400 hover:text-teal-300 font-semibold transition-colors">
                        Akses Portal Petugas
                    </a>
                    <span class="text-slate-700">•</span>
                    <span class="text-slate-400 font-medium">Pemerintah Kampung Sota</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
