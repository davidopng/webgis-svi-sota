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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-paper" x-data="{ mobileMenu: false }">

    {{-- ============================================================
         NAVBAR PUBLIK — 56px, putih 92% + blur (PRD 8.1)
         ============================================================ --}}
    <header class="fixed inset-x-0 top-0 z-50 flex h-14 items-center justify-between border-b border-line bg-white/92 px-4 backdrop-blur-md lg:px-6">
        {{-- Logo --}}
        <a href="/" class="flex items-center gap-2 text-ink no-underline">
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-brand text-xs font-bold text-white">S</span>
            <span class="text-base font-bold tracking-tight">SOTA-GIS</span>
            <span class="hidden text-sm font-normal text-muted sm:inline">Pemetaan Kerentanan Sosial Ekonomi Kampung Sota</span>
        </a>

        {{-- Menu desktop --}}
        <nav class="hidden items-center gap-1 md:flex" aria-label="Navigasi utama">
            <a href="/tentang"
               class="rounded-md px-3 py-2 text-sm font-medium text-muted hover:bg-paper hover:text-ink transition-colors {{ request()->is('tentang') ? 'text-ink bg-paper' : '' }}">
                Tentang
            </a>
            <a href="/login"
               class="btn-primary ml-2 !min-h-[36px] !py-1.5 !px-4 !text-sm">
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
         class="fixed inset-x-0 top-14 z-40 border-b border-line bg-surface p-4 shadow-[var(--shadow-modal)] md:hidden"
         x-cloak>
        <nav class="flex flex-col gap-1" aria-label="Navigasi utama mobile">
            <a href="/tentang" class="rounded-md px-3 py-2.5 text-sm font-medium text-ink hover:bg-paper transition-colors">Tentang</a>
            <a href="/login" class="rounded-md px-3 py-2.5 text-sm font-medium text-brand hover:bg-paper transition-colors">Masuk</a>
        </nav>
    </div>

    {{-- ============================================================
         KONTEN — di bawah navbar
         ============================================================ --}}
    <main id="konten-utama">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
