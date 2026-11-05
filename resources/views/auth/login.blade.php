@extends('layouts.auth')

@section('title', 'Masuk ke Sistem — SOTA-GIS')

@section('content')
<div class="card space-y-6">
    {{-- Header Logo & Judul --}}
    <div class="text-center space-y-2">
        <a href="/" class="inline-block group mb-1">
            <img src="{{ asset('images/sotalogo.svg') }}" alt="Logo SOTA-GIS" class="h-14 w-14 mx-auto rounded-2xl object-contain shadow-md group-hover:scale-105 transition-transform">
        </a>
        <h1 class="text-xl sm:text-2xl text-ink font-black tracking-tight">Masuk ke SOTA-GIS</h1>
        <p class="text-muted text-xs sm:text-sm">Sistem Informasi Geografis Pemetaan Kerentanan Sosial Ekonomi Kampung Sota</p>
    </div>

    {{-- Alert Status Berhasil Logout / Notifikasi --}}
    @if (session('status'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-3.5 text-xs font-semibold text-emerald-800 flex items-center gap-2.5 shadow-2xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- Alert Error Validasi / Login Gagal --}}
    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 p-3.5 text-xs font-semibold text-rose-800 flex items-start gap-2.5 shadow-2xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <div class="space-y-0.5">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Form Masuk Real Backend --}}
    <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label for="username" class="block text-xs font-bold text-ink mb-1.5">Username (ID)</label>
            <input type="text"
                   id="username"
                   name="username"
                   value="{{ old('username', '') }}"
                   required
                   autofocus
                   class="input-text text-xs sm:text-sm !py-2.5 @error('username') border-rose-400 focus:ring-rose-200 @enderror"
                   placeholder="Masukkan username atau ID akun Anda">
        </div>

        <div x-data="{ showPass: false }">
            <label for="password" class="block text-xs font-bold text-ink mb-1.5">Kata Sandi</label>
            <div class="relative">
                <input :type="showPass ? 'text' : 'password'"
                       id="password"
                       name="password"
                       required
                       class="input-text text-xs sm:text-sm !py-2.5 pr-10 @error('password') border-rose-400 focus:ring-rose-200 @enderror"
                       placeholder="Masukkan kata sandi">
                <button type="button" @click="showPass = !showPass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-ink transition-colors p-1"
                        aria-label="Lihat atau sembunyikan kata sandi">
                    <svg x-show="!showPass" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    <svg x-show="showPass" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-cloak>
                        <path d="m9.88 9.88 4.24 4.24"/>
                        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                        <line x1="2" y1="2" x2="22" y2="22"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between text-xs py-1">
            <label class="flex items-center gap-2 cursor-pointer text-muted font-medium hover:text-ink select-none">
                <input type="checkbox" name="remember" class="rounded border-line text-brand focus:ring-brand">
                <span>Ingat saya</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full !py-2.5 !text-sm font-bold shadow-md hover:scale-[1.01] active:scale-[0.99] transition-transform">
            Masuk ke Sistem
        </button>
    </form>

    <div class="pt-2 border-t border-line text-center">
        <a href="/" class="text-xs font-bold text-brand hover:underline inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 19-7-7 7-7"/>
                <path d="M19 12H5"/>
            </svg>
            <span>Kembali Ke Halaman Utama</span>
        </a>
    </div>
</div>
@endsection
