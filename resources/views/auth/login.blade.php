@extends('layouts.auth')

@section('content')
<div class="card space-y-6">
    {{-- Header Logo & Judul --}}
    <div class="text-center space-y-2">
        <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-brand text-lg font-bold text-white mb-1 shadow-sm">
            S
        </div>
        <h1 class="text-page-title text-ink font-bold">Masuk ke SOTA-GIS</h1>
        <p class="text-help text-sm">Sistem Pemetaan Kerentanan Sosial Ekonomi Kampung Sota</p>
    </div>

    {{-- Alert Error Dummy (disembunyikan secara default, bisa di-toggle atau muncul jika error) --}}
    <div x-data="{ showError: false }" x-cloak>
        <template x-if="showError">
            <div class="rounded-md bg-tinggi-bg border border-tinggi/30 p-3 text-sm text-tinggi-tx flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>Username atau kata sandi tidak sesuai.</span>
            </div>
        </template>
    </div>

    {{-- Form Masuk --}}
    <form action="/login" method="POST" class="space-y-4" @submit.prevent="
        // Arahkan sesuai dummy demo role
        if ($refs.username.value === 'admin') window.location.href = '/admin';
        else if ($refs.username.value === 'pimpinan' || $refs.username.value === 'kepala') window.location.href = '/pimpinan';
        else window.location.href = '/operator';
    ">
        @csrf
        <div>
            <label for="username" class="block text-label text-ink mb-1.5">Username</label>
            <input type="text" id="username" name="username" x-ref="username" required
                   class="input-text" placeholder="Masukkan username" value="operator">
        </div>

        <div x-data="{ showPass: false }">
            <label for="password" class="block text-label text-ink mb-1.5">Kata sandi</label>
            <div class="relative">
                <input :type="showPass ? 'text' : 'password'" id="password" name="password" required
                       class="input-text pr-10" placeholder="Masukkan kata sandi" value="password">
                <button type="button" @click="showPass = !showPass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-ink transition-colors p-1"
                        aria-label="Lihat atau sembunyikan kata sandi">
                    <svg x-show="!showPass" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="showPass" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x-cloak><path d="m9.88 9.88 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/><path d="M2 2l20 20"/></svg>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between text-sm py-1">
            <label class="flex items-center gap-2 cursor-pointer text-muted">
                <input type="checkbox" name="remember" class="rounded border-line text-brand focus:ring-brand">
                <span>Ingat saya</span>
            </label>
        </div>

        <button type="submit" class="btn-primary w-full py-3">
            Masuk
        </button>

        <div class="p-3 bg-paper rounded-md border border-line text-xs text-muted space-y-1">
            <p class="font-medium text-ink">Akun Demo (Tahap Casing):</p>
            <p>• Operator: ketik <code>operator</code> → Masuk</p>
            <p>• Admin: ketik <code>admin</code> → Masuk</p>
            <p>• Pimpinan: ketik <code>pimpinan</code> → Masuk</p>
        </div>
    </form>

    <div class="pt-2 border-t border-line text-center">
        <a href="/" class="text-sm font-medium text-brand hover:underline inline-flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Kembali ke peta publik
        </a>
    </div>
</div>
@endsection
