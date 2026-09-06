<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SOTA-GIS — Sistem pemetaan kerentanan sosial ekonomi masyarakat Kampung Sota, Distrik Sota, Kabupaten Merauke.">
    <title>@yield('title', 'SOTA-GIS — Pemetaan Kerentanan Sosial Ekonomi Kampung Sota')</title>

    {{-- Plus Jakarta Sans (PRD 6.3) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-paper" x-data="{ sidebarOpen: false }">
    {{-- Lompat ke konten utama (PRD 15) --}}
    <a href="#konten-utama" class="skip-link">Lompat ke konten utama</a>

    <div class="flex min-h-screen">
        {{-- ============================================================
             SIDEBAR — 240px, latar ink, teks putih (PRD 10.1)
             Di mobile: laci geser
             ============================================================ --}}
        {{-- Overlay mobile --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-ink/50 lg:hidden"
             x-cloak></div>

        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col bg-ink transition-transform duration-200 lg:translate-x-0 lg:static lg:z-auto"
               @keydown.escape.window="sidebarOpen = false">

            {{-- Logo --}}
            <div class="flex h-14 items-center gap-2 px-5 border-b border-white/10">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-brand text-xs font-bold text-white">S</span>
                <span class="text-base font-bold text-white tracking-tight">SOTA-GIS</span>
            </div>

            {{-- Navigasi --}}
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1" aria-label="Menu utama">
                @if(($role ?? 'operator') === 'operator')
                    <x-sidebar-link href="/operator" icon="layout-dashboard" :active="request()->is('operator') && !request()->is('operator/*')">
                        Dashboard
                    </x-sidebar-link>
                    <x-sidebar-link href="/operator/data" icon="database" :active="request()->is('operator/data*')">
                        Data Saya
                    </x-sidebar-link>
                    <x-sidebar-link href="/operator/data/create" icon="plus-circle" :active="request()->is('operator/data/create')">
                        Input Data
                    </x-sidebar-link>
                @elseif(($role ?? '') === 'admin')
                    <x-sidebar-link href="/admin" icon="layout-dashboard" :active="request()->is('admin') && !request()->is('admin/*')">
                        Dashboard
                    </x-sidebar-link>
                    <x-sidebar-link href="/admin/verifikasi" icon="shield-check" :active="request()->is('admin/verifikasi*')" :badge="$verifikasiCount ?? null">
                        Antrean Verifikasi
                    </x-sidebar-link>
                    <x-sidebar-link href="/admin/data" icon="database" :active="request()->is('admin/data*')">
                        Semua Data
                    </x-sidebar-link>
                    <x-sidebar-link href="/admin/log" icon="scroll-text" :active="request()->is('admin/log*')">
                        Log Pembaruan
                    </x-sidebar-link>
                @elseif(($role ?? '') === 'kepala_kampung')
                    <x-sidebar-link href="/pimpinan" icon="layout-dashboard" :active="request()->is('pimpinan') && !request()->is('pimpinan/*')">
                        Dashboard
                    </x-sidebar-link>
                    <x-sidebar-link href="/pimpinan/laporan" icon="file-down" :active="request()->is('pimpinan/laporan*')">
                        Laporan
                    </x-sidebar-link>
                @endif

                <div class="my-3 border-t border-white/10"></div>

                <x-sidebar-link href="/" icon="map" :active="false">
                    Peta publik
                </x-sidebar-link>
            </nav>

            {{-- Keluar --}}
            <div class="border-t border-white/10 p-3">
                <a href="/login" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm text-white/70 hover:bg-white/10 hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Keluar
                </a>
            </div>
        </aside>

        {{-- ============================================================
             AREA UTAMA
             ============================================================ --}}
        <div class="flex flex-1 flex-col min-w-0">
            {{-- Topbar --}}
            <header class="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-line bg-surface px-4 lg:px-6">
                {{-- Hamburger mobile --}}
                <button @click="sidebarOpen = !sidebarOpen"
                        class="inline-flex items-center justify-center h-10 w-10 rounded-md text-muted hover:bg-paper hover:text-ink lg:hidden"
                        aria-label="Buka menu">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                </button>

                <h1 class="text-section-title text-ink truncate">@yield('page-title')</h1>

                {{-- Profil --}}
                <div class="flex items-center gap-3">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                @click.outside="open = false"
                                class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-ink hover:bg-paper transition-colors">
                            <span class="hidden sm:inline">{{ $userName ?? 'Pengguna' }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open" x-transition
                             class="absolute right-0 top-full mt-1 w-48 rounded-lg border border-line bg-surface p-1 shadow-[var(--shadow-modal)]"
                             x-cloak>
                            <div class="px-3 py-2 border-b border-line mb-1">
                                <p class="text-sm font-medium text-ink">{{ $userName ?? 'Pengguna' }}</p>
                                <p class="text-xs text-muted capitalize">{{ str_replace('_', ' ', $role ?? 'operator') }}</p>
                            </div>
                            <a href="/login" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm text-tinggi hover:bg-tinggi-bg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Konten --}}
            <main id="konten-utama" class="flex-1 p-4 lg:p-6">
                <div class="mx-auto max-w-[1280px]">
                    {{-- Toast container --}}
                    <div id="toast-container" class="fixed top-4 right-4 z-[100] flex flex-col gap-2 max-sm:left-4 max-sm:right-4 max-sm:top-0"></div>

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
