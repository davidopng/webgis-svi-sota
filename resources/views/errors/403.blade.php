<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak | SOTA-GIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-paper font-sans text-ink min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-surface border border-line rounded-2xl p-6 sm:p-8 shadow-xl text-center space-y-6">
        {{-- Ikon Peringatan 403 --}}
        <div class="mx-auto h-20 w-20 rounded-2xl bg-sedang/15 border border-sedang/30 flex items-center justify-center text-sedang">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>

        {{-- Judul & Pesan --}}
        <div class="space-y-2">
            <span class="inline-block px-3 py-1 rounded-full text-xs font-mono font-bold bg-sedang-bg text-sedang-tx border border-sedang/30">
                ERROR 403 · FORBIDDEN
            </span>
            <h1 class="text-xl sm:text-2xl font-bold text-ink">403 — Akses Ditolak</h1>
            <p class="text-xs sm:text-sm text-tinggi-tx bg-tinggi-bg/70 p-2.5 rounded-xl border border-tinggi/20 font-medium leading-relaxed">
                {{ $exception?->getMessage() ?: 'Akses Ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.' }}
            </p>
        </div>

        {{-- Informasi Akun Aktif --}}
        @auth
            <div class="p-3.5 rounded-xl bg-paper border border-line text-xs text-left flex items-center justify-between">
                <div>
                    <span class="text-muted block text-[11px]">Sedang Masuk Sebagai:</span>
                    <span class="font-bold text-ink">{{ Auth::user()->name }}</span>
                </div>
                <span class="px-2.5 py-1 rounded text-[11px] font-bold uppercase tracking-wider
                    @if(Auth::user()->role === 'admin') bg-brand/10 text-brand border border-brand/20
                    @elseif(Auth::user()->role === 'kepala_kampung') bg-rendah/15 text-rendah border border-rendah/30
                    @else bg-blue-500/10 text-blue-600 border border-blue-500/20 @endif">
                    {{ Auth::user()->role }}
                </span>
            </div>
        @endauth

        {{-- Tombol Tindakan Cepat --}}
        <div class="flex flex-col gap-2.5 pt-2">
            @auth
                @php
                    $dashboardUrl = match(Auth::user()->role) {
                        'admin' => '/admin',
                        'kepala_kampung', 'pimpinan' => '/pimpinan',
                        default => '/operator',
                    };
                @endphp
                <a href="{{ $dashboardUrl }}" class="btn-primary w-full !min-h-[44px] justify-center font-bold shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span>Kembali ke Dashboard</span>
                </a>
                <a href="{{ route('logout.get') }}" class="btn-secondary w-full !min-h-[44px] justify-center font-semibold text-muted hover:text-ink hover:border-slate-300 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Ganti Akun</span>
                </a>
            @else
                <a href="/login" class="btn-primary w-full !min-h-[44px] justify-center font-bold shadow-md">
                    <span>Masuk ke Akun Anda</span>
                </a>
                <a href="/" class="btn-secondary w-full !min-h-[44px] justify-center font-semibold text-muted hover:text-ink">
                    <span>Beranda Publik</span>
                </a>
            @endauth
        </div>
    </div>
</body>
</html>
