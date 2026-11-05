@extends('layouts.public')

@section('title', 'Kontak Resmi — Pemerintah Kampung Sota & SOTA-GIS')
@section('meta-description', 'Saluran komunikasi dan kontak resmi Pemerintah Kampung Sota, Distrik Sota, Merauke. Informasi alamat balai kampung, telepon, WhatsApp, email, dan peta lokasi.')

@push('head')
    @vite(['resources/js/infografis-publik.js'])
@endpush

@section('content')
<div class="pt-16 pb-20 bg-paper min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">


        {{-- ============================================================
             BANNER HERO: HUBUNGI KAMI (SS1)
             ============================================================ --}}
        <div class="relative overflow-hidden bg-slate-900 text-white rounded-3xl p-8 sm:p-14 text-center border border-slate-800 shadow-xl">
            {{-- Ambient Glow --}}
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-brand/25 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-0 right-0 w-64 h-64 bg-teal-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 space-y-4 max-w-3xl mx-auto">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold bg-white/10 text-white border border-white/20 backdrop-blur-md shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                    <span>Saluran Komunikasi Terpadu</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                    Hubungi Kami
                </h1>
                <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
                    Ada pertanyaan atau membutuhkan bantuan teknis langsung? Jangan ragu untuk menghubungi kami atau mengunjungi kantor operasional kami secara langsung di Distrik Sota.
                </p>
            </div>
        </div>

        {{-- ============================================================
             GRID KONTEN INFORMASI KONTAK & PETA INTERAKTIF (SS1)
             ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Kolom Kiri: Informasi Kontak (5 Cols) --}}
            <div class="lg:col-span-5 space-y-4">
                <div class="card p-6 sm:p-7 space-y-5 shadow-sm border border-line">
                    <div class="flex items-center gap-3.5 pb-4 border-b border-line">
                        <div class="h-11 w-11 rounded-2xl bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0 border border-teal-500/20">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </div>
                        <div>
                            <h2 class="font-extrabold text-ink text-lg leading-tight">Informasi Kontak</h2>
                            <p class="text-xs text-muted mt-0.5">Saluran komunikasi resmi Pemerintah Kampung Sota</p>
                        </div>
                    </div>

                    <p class="text-xs sm:text-sm text-muted leading-relaxed">
                        Silakan gunakan salah satu saluran komunikasi resmi kami berikut untuk berkonsultasi mengenai keluhan layanan, informasi kependudukan, pemetaan sosial ekonomi, atau potensi wilayah.
                    </p>

                    <div class="space-y-3.5 pt-1">
                        {{-- Item 1: Alamat Kantor Pusat --}}
                        <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-paper border border-line hover:border-brand/40 transition-colors">
                            <div class="h-10 w-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                            <div class="space-y-1 text-xs">
                                <span class="font-bold text-ink block text-xs sm:text-sm">Alamat Kantor Pusat</span>
                                <span class="text-muted leading-relaxed block text-xs">Balai Kantor Kampung Sota, Jl. Poros Trans-Papua, Distrik Sota, Kabupaten Merauke, Papua Selatan (99644).</span>
                            </div>
                        </div>

                        {{-- Item 2: Nomor Telepon & WhatsApp --}}
                        <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-paper border border-line hover:border-brand/40 transition-colors">
                            <div class="h-10 w-10 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0 mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <div class="space-y-1 text-xs">
                                <span class="font-bold text-ink block text-xs sm:text-sm">Nomor Telepon &amp; WhatsApp</span>
                                <span class="text-muted block text-xs font-mono font-medium">(0971) 321456 / +62 812-3456-7890</span>
                                <span class="text-[11px] text-teal-700 font-semibold block">Pelayanan pesan &amp; konsultasi warga</span>
                            </div>
                        </div>

                        {{-- Item 3: Alamat Email Resmi --}}
                        <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-paper border border-line hover:border-brand/40 transition-colors">
                            <div class="h-10 w-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0 mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </div>
                            <div class="space-y-1 text-xs">
                                <span class="font-bold text-ink block text-xs sm:text-sm">Alamat Email Resmi</span>
                                <span class="text-muted block text-xs font-mono">kontak@kampungsota.id</span>
                                <span class="text-[11px] text-muted block">Korespondensi administrasi &amp; kedinasan</span>
                            </div>
                        </div>

                        {{-- Item 4: Jam Operasional Pelayanan --}}
                        <div class="flex items-start gap-3.5 p-4 rounded-2xl bg-paper border border-line hover:border-brand/40 transition-colors">
                            <div class="h-10 w-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div class="space-y-1 text-xs">
                                <span class="font-bold text-ink block text-xs sm:text-sm">Jam Operasional Pelayanan</span>
                                <span class="text-muted block text-xs">Senin – Jumat: 08.00 – 15.00 WIT</span>
                                <span class="text-[11px] text-slate-500 block italic">Sabtu, Minggu &amp; Hari Libur Nasional: Tutup</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Peta Lokasi Kantor / Balai Kampung Sota (7 Cols) --}}
            <div class="lg:col-span-7">
                <div class="card p-5 sm:p-7 space-y-4 shadow-sm border border-line flex flex-col justify-between">
                    {{-- Header Map Card --}}
                    <div class="flex items-center justify-between pb-3 border-b border-line gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="relative flex h-3 w-3 shrink-0">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-teal-600"></span>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm sm:text-base font-extrabold text-ink leading-tight truncate">Lokasi Kantor Balai Kampung Sota</h3>
                                <p class="text-[11px] text-muted truncate mt-0.5">Koordinat: -8.429164, 141.009468</p>
                            </div>
                        </div>
                        <a href="https://maps.app.goo.gl/h8QSBSEPQsRpMHL38" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-brand bg-brand/10 hover:bg-brand hover:text-white rounded-lg transition-colors shrink-0 shadow-2xs">
                            <span>Buka Google Maps</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    </div>

                    {{-- Leaflet Mini-map Canvas --}}
                    <div class="relative w-full rounded-2xl overflow-hidden border border-line bg-slate-100 min-h-[380px] sm:min-h-[440px] shadow-inner">
                        <div id="peta-lokasi-kantor" class="w-full h-full min-h-[380px] sm:min-h-[440px] z-10"></div>
                    </div>

                    <div class="pt-2 text-xs text-muted flex items-center justify-between">
                        <span>Pemerintah Distrik Sota, Kabupaten Merauke</span>
                        <span class="font-bold text-ink">Zona Waktu: WIT (UTC+9)</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
