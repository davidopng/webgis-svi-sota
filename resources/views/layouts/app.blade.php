<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SOTA-GIS — Sistem pemetaan kerentanan sosial ekonomi masyarakat Kampung Sota, Distrik Sota, Kabupaten Merauke.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
               class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col bg-ink transition-transform duration-200 lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen lg:shrink-0 lg:z-30 no-print"
               @keydown.escape.window="sidebarOpen = false">

            {{-- Logo --}}
            <a href="/" class="flex h-14 items-center gap-3 px-5 border-b border-white/10 shrink-0 no-underline group">
                <img src="{{ asset('images/sotalogo.svg') }}" alt="Logo SOTA-GIS" class="h-9 w-9 rounded-lg object-contain shrink-0 group-hover:scale-105 transition-transform">
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-white tracking-tight leading-tight">SOTA-GIS</span>
                    <span class="text-[10px] font-medium text-white/60">Kampung Sota</span>
                </div>
            </a>

            {{-- Navigasi --}}
            @php
                $activeUser = Auth::user();
                $currentRole = $activeUser?->role ?? ($role ?? 'operator');
                $currentName = $activeUser?->name ?? ($userName ?? 'Pengguna');
                $sidebarPendingCount = \App\Models\DataPenduduk::where('status_verifikasi', 'menunggu_verifikasi')->count();
            @endphp
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1" aria-label="Menu utama">
                @if($currentRole === 'operator')
                    <x-sidebar-link href="/operator" icon="layout-dashboard" :active="request()->is('operator') && !request()->is('operator/*')">
                        Dashboard
                    </x-sidebar-link>
                    <x-sidebar-link href="/operator/data" icon="database" :active="request()->is('operator/data') || (request()->is('operator/data/*') && !request()->is('operator/data/create*'))">
                        Data Saya
                    </x-sidebar-link>
                    <x-sidebar-link href="/operator/data/create" icon="plus-circle" :active="request()->is('operator/data/create*')">
                        Input Data
                    </x-sidebar-link>
                @elseif($currentRole === 'admin')
                    <x-sidebar-link href="/admin" icon="layout-dashboard" :active="request()->is('admin') && !request()->is('admin/*')">
                        Dashboard
                    </x-sidebar-link>
                    <x-sidebar-link href="/admin/data/create" icon="plus-circle" :active="request()->is('admin/data/create*')">
                        Input Data
                    </x-sidebar-link>
                    <x-sidebar-link href="/admin/verifikasi" icon="shield-check" :active="request()->is('admin/verifikasi*')" :badge="$sidebarPendingCount > 0 ? $sidebarPendingCount : null">
                        Antrean Verifikasi
                    </x-sidebar-link>
                    <x-sidebar-link href="/admin/data" icon="database" :active="request()->is('admin/data') || (request()->is('admin/data/*') && !request()->is('admin/data/create*'))">
                        Semua Data
                    </x-sidebar-link>
                    <x-sidebar-link href="/admin/log" icon="scroll-text" :active="request()->is('admin/log*')">
                        Log Pembaruan
                    </x-sidebar-link>
                @elseif($currentRole === 'kepala_kampung')
                    <x-sidebar-link href="/pimpinan" icon="layout-dashboard" :active="request()->is('pimpinan') && !request()->is('pimpinan/*')">
                        Dashboard
                    </x-sidebar-link>
                    <x-sidebar-link href="/pimpinan/laporan" icon="file-down" :active="request()->is('pimpinan/laporan*')">
                        Laporan
                    </x-sidebar-link>
                @endif

                @if($currentRole !== 'operator')
                    <div class="my-3 border-t border-white/10"></div>

                    @php
                        $petaRoute = match($currentRole) {
                            'admin' => '/admin/peta-sebaran',
                            'kepala_kampung' => '/pimpinan/peta-sebaran',
                            default => '/admin/peta-sebaran',
                        };
                        $isPetaActive = request()->is('*/peta-sebaran*') || request()->is('peta-sebaran*');
                    @endphp
                    <x-sidebar-link :href="$petaRoute" icon="map" :active="$isPetaActive">
                        Peta Sebaran
                    </x-sidebar-link>
                @endif
            </nav>

            {{-- Keluar --}}
            <div class="border-t border-white/10 p-3">
                <a href="{{ route('logout.get') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm text-white/70 hover:bg-white/10 hover:text-white transition-colors">
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
            <header class="sticky top-0 z-40 flex h-14 items-center justify-between border-b border-line bg-surface px-4 lg:px-6 no-print">
                {{-- Hamburger mobile --}}
                <button @click="sidebarOpen = !sidebarOpen"
                        class="inline-flex items-center justify-center h-10 w-10 rounded-md text-muted hover:bg-paper hover:text-ink lg:hidden"
                        aria-label="Buka menu">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
                </button>

                <h1 class="text-section-title text-ink truncate">@yield('page-title')</h1>

                {{-- Profil --}}
                <div class="flex items-center gap-3">
                    @php
                        $username = $activeUser?->username ?? '';
                        $name = $currentName;

                        if ($currentRole === 'kepala_kampung' || str_contains(strtolower($name), 'conrada')) {
                            $actorRoleTitle = 'Kepala Kampung Sota';
                            $actorIconType = 'kepala';
                            $iconBg = 'bg-amber-100 text-amber-700 border-amber-200';
                        } elseif ($currentRole === 'admin' || str_contains(strtolower($name), 'jhoni')) {
                            $actorRoleTitle = 'Admin Sistem (Kasi Pemerintahan)';
                            $actorIconType = 'admin';
                            $iconBg = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                        } elseif ($username === 'operator2' || str_contains(strtolower($name), 'kusnaeni')) {
                            $actorRoleTitle = 'Operator Surveyor 2 (Kasi Pelayanan)';
                            $actorIconType = 'operator2';
                            $iconBg = 'bg-indigo-100 text-indigo-700 border-indigo-200';
                        } else {
                            $actorRoleTitle = 'Operator Surveyor 1';
                            $actorIconType = 'operator1';
                            $iconBg = 'bg-blue-100 text-blue-700 border-blue-200';
                        }
                    @endphp
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                @click.outside="open = false"
                                class="flex items-center gap-2.5 rounded-xl border border-line/80 bg-white px-3 py-1.5 text-sm font-semibold text-ink hover:bg-paper hover:border-line transition-all shadow-xs cursor-pointer group">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg border {{ $iconBg }} shadow-2xs group-hover:scale-105 transition-transform">
                                @if($actorIconType === 'kepala')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M3 10h18"/><path d="M5 10v11"/><path d="M9 10v11"/><path d="M15 10v11"/><path d="M19 10v11"/><path d="M12 2l9 6H3z"/></svg>
                                @elseif($actorIconType === 'admin')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                                @elseif($actorIconType === 'operator2')
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                @endif
                            </span>
                            <span class="hidden sm:inline font-bold text-ink text-xs tracking-tight">{{ $name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted transition-transform duration-200" :class="open ? 'rotate-180 text-ink' : ''"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="transform opacity-0 scale-95 -translate-y-1"
                             class="absolute right-0 top-full mt-2 w-64 rounded-xl border border-line bg-surface p-1.5 shadow-xl z-50 divide-y divide-line"
                             x-cloak>
                            <div class="px-3 py-2.5">
                                <p class="text-sm font-bold text-ink leading-tight">{{ $name }}</p>
                                <p class="text-[11px] text-muted mt-1 leading-snug">{{ $actorRoleTitle }}</p>
                            </div>
                            <div class="pt-1">
                                <a href="{{ route('logout.get') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    <span>Keluar</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Konten --}}
            @hasSection('full-content')
                <main id="konten-utama" class="flex-1 relative w-full h-[calc(100vh-56px)] overflow-hidden">
                    @yield('full-content')
                </main>
            @else
                <main id="konten-utama" class="flex-1 p-4 lg:p-6">
                    <div class="mx-auto max-w-[1280px]">
                        {{-- ============================================================
                             BANNER NOTIFIKASI HALAMAN (DESAIN BANNER SEPERTI SS2)
                             ============================================================ --}}
                        @php
                            $flashNotif = session('success') ?? request()->query('notif');
                            $flashError = session('error');
                            $isError = false;
                            $notifText = null;

                            if ($flashError || request()->query('notif') === 'gagal') {
                                $isError = true;
                                $notifText = $flashError ?? 'Data gagal disimpan. Silakan coba lagi.';
                            } else {
                                $notifText = match($flashNotif) {
                                    'simpan', 'Data berhasil disimpan.' => 'Data berhasil disimpan.',
                                    'ubah', 'Data berhasil diubah.' => 'Data berhasil diubah.',
                                    'hapus', 'Data berhasil dihapus.' => 'Data berhasil dihapus.',
                                    'verifikasi', 'Data berhasil diverifikasi.' => 'Data berhasil diverifikasi.',
                                    default => is_string($flashNotif) && strlen($flashNotif) > 1 ? $flashNotif : null,
                                };
                                if (!$notifText && (request()->query('sukses') === '1' || request()->query('baru') === '1')) {
                                    $notifText = 'Data berhasil disimpan.';
                                }
                            }
                        @endphp

                        <div id="page-alert-banner"
                             @if(!$notifText) style="display: none;" @endif
                             class="mb-6 rounded-xl border {{ $isError ? 'border-tinggi/50 bg-tinggi-bg/90' : 'border-rendah/50 bg-rendah-bg/90' }} p-4 shadow-sm flex items-center justify-between gap-3 transition-all duration-300">
                            <div class="flex items-center gap-3">
                                <span id="banner-icon" class="flex-shrink-0 {{ $isError ? 'text-tinggi' : 'text-rendah' }}">
                                    @if($isError)
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                                        </svg>
                                    @endif
                                </span>
                                <div>
                                    <p id="banner-title" class="text-sm font-bold {{ $isError ? 'text-tinggi-tx' : 'text-rendah-tx' }}">{{ $notifText ?? '' }}</p>
                                </div>
                            </div>
                            <button type="button" onclick="document.getElementById('page-alert-banner').style.display='none'" class="p-1 rounded-lg {{ $isError ? 'text-tinggi-tx' : 'text-rendah-tx' }} hover:opacity-70 transition-opacity" aria-label="Tutup">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        @yield('content')
                    </div>
                </main>
            @endif
        </div>
    </div>

    {{-- Toast Notification Container Melayang Global (PRD 13.1) --}}
    <div id="toast-container" class="fixed top-5 right-5 z-[150] flex flex-col gap-2.5 max-sm:left-4 max-sm:right-4 max-sm:top-4 pointer-events-none"></div>

    <script>
        window.sotaToast = function(message, type = 'berhasil') {
            const isSuccess = type === 'berhasil' || type === 'sukses';
            const isWarning = type === 'peringatan';

            // 1. Tampilkan Banner Notifikasi Halaman (Desain seperti ss2)
            const banner = document.getElementById('page-alert-banner');
            const bannerTitle = document.getElementById('banner-title');
            const bannerIcon = document.getElementById('banner-icon');

            if (banner && bannerTitle) {
                bannerTitle.textContent = message;
                if (isSuccess) {
                    banner.className = 'mb-6 rounded-xl border border-rendah/50 bg-rendah-bg/90 p-4 shadow-sm flex items-center justify-between gap-3 transition-all duration-300';
                    if (bannerIcon) {
                        bannerIcon.className = 'flex-shrink-0 text-rendah';
                        bannerIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>';
                    }
                    bannerTitle.className = 'text-sm font-bold text-rendah-tx';
                } else if (isWarning) {
                    banner.className = 'mb-6 rounded-xl border border-sedang/50 bg-sedang-bg/90 p-4 shadow-sm flex items-center justify-between gap-3 transition-all duration-300';
                    if (bannerIcon) {
                        bannerIcon.className = 'flex-shrink-0 text-sedang-tx';
                        bannerIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
                    }
                    bannerTitle.className = 'text-sm font-bold text-sedang-tx';
                } else {
                    banner.className = 'mb-6 rounded-xl border border-tinggi/50 bg-tinggi-bg/90 p-4 shadow-sm flex items-center justify-between gap-3 transition-all duration-300';
                    if (bannerIcon) {
                        bannerIcon.className = 'flex-shrink-0 text-tinggi';
                        bannerIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                    }
                    bannerTitle.className = 'text-sm font-bold text-tinggi-tx';
                }
                banner.style.display = 'flex';
                banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            // 2. Tampilkan juga Toast Notifikasi Mengambang di Pojok Kanan
            const container = document.getElementById('toast-container');
            if (container) {
                const bgClass = isSuccess ? 'bg-[#E4F2E9] border-[#2C7A4B]/40 text-[#1B5233]' : 
                                (isWarning ? 'bg-[#FCF1DA] border-[#E0A32E]/40 text-[#8A5E00]' : 'bg-[#FBE7E4] border-[#B3352C]/40 text-[#8E2820]');
                
                const iconSvg = isSuccess ? 
                    `<svg class="w-5 h-5 text-[#2C7A4B] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>` :
                    (isWarning ? 
                    `<svg class="w-5 h-5 text-[#E0A32E] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>` :
                    `<svg class="w-5 h-5 text-[#B3352C] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`);

                const toastEl = document.createElement('div');
                toastEl.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg max-w-sm transition-all duration-300 transform translate-y-2 opacity-0 ${bgClass}`;
                toastEl.innerHTML = `
                    ${iconSvg}
                    <span class="text-xs sm:text-sm font-semibold flex-1 leading-snug">${message}</span>
                    <button type="button" class="shrink-0 p-1 rounded-lg opacity-70 hover:opacity-100 transition-opacity focus:outline-none" aria-label="Tutup">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                `;

                toastEl.querySelector('button').addEventListener('click', () => {
                    toastEl.classList.add('opacity-0', '-translate-y-2');
                    setTimeout(() => toastEl.remove(), 200);
                });

                container.appendChild(toastEl);
                requestAnimationFrame(() => {
                    toastEl.classList.remove('translate-y-2', 'opacity-0');
                    toastEl.classList.add('translate-y-0', 'opacity-100');
                });

                setTimeout(() => {
                    if (toastEl.parentNode) {
                        toastEl.classList.add('opacity-0', '-translate-y-2');
                        setTimeout(() => toastEl.remove(), 250);
                    }
                }, 4500);
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const notif = params.get('notif');
            const sukses = params.get('sukses');

            if (notif === 'simpan' || sukses === '1' || params.get('baru') === '1') {
                window.sotaToast('Data berhasil disimpan.', 'berhasil');
            } else if (notif === 'ubah') {
                window.sotaToast('Data berhasil diubah.', 'berhasil');
            } else if (notif === 'hapus') {
                window.sotaToast('Data berhasil dihapus.', 'berhasil');
            } else if (notif === 'verifikasi') {
                window.sotaToast('Data berhasil diverifikasi.', 'berhasil');
            } else if (notif === 'gagal') {
                window.sotaToast('Data gagal disimpan. Silakan coba lagi.', 'gagal');
            }
        });
    </script>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.sotaToast("{{ session('success') }}", 'berhasil');
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.sotaToast("{{ session('error') }}", 'gagal');
            });
        </script>
    @endif

    @stack('scripts')
</body>
</html>
