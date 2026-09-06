@extends('layouts.app', ['role' => 'operator', 'userName' => 'Maria K.'])

@section('title', 'Dashboard Operator — SOTA-GIS')
@section('page-title', 'Dashboard Operator')

@section('content')
<div class="space-y-6">
    {{-- Ucapan Selamat Datang & Subtitle (PRD 10.2 Wireframe) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-ink">Halo, Maria.</h2>
            <p class="text-help mt-0.5">3 data survei warga saat ini sedang menunggu verifikasi Admin.</p>
        </div>
        <a href="/operator/data/create" class="hidden sm:inline-flex btn-primary shadow-sm font-bold">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>＋ Input Data Warga Baru</span>
        </a>
    </div>

    {{-- Tiga Kartu Metrik Ringkas (PRD 10.2 Wireframe) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-stat-card
            label="Data terinput"
            value="27"
            description="rumah tangga"
            color="brand"
            href="/operator/data"
        />
        <x-stat-card
            label="Menunggu verifikasi"
            value="3"
            description="● perlu diperiksa Admin"
            color="sedang"
            href="/operator/data?status=menunggu_verifikasi"
        />
        <x-stat-card
            label="Terverifikasi"
            value="24"
            description="● tampil di peta publik"
            color="rendah"
            href="/operator/data?status=terverifikasi"
        />
    </div>

    {{-- Grid Bawah: Daftar Input Terakhir & Tombol Tambah Cepat --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Card: Input Terakhir (5 Baris Sesuai PRD 10.2) --}}
        <div class="lg:col-span-2 card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-line">
                <div>
                    <h3 class="text-section-title text-ink">Input terakhir</h3>
                    <p class="text-help text-xs">5 rumah tangga terbaru yang Anda data di lapangan</p>
                </div>
                <a href="/operator/data" class="text-xs font-bold text-brand hover:underline">
                    Lihat semua data saya →
                </a>
            </div>

            <div class="divide-y divide-line text-xs">
                {{-- Baris 1: Yohanis Kaize --}}
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="space-y-0.5">
                        <p class="font-bold text-ink text-sm">Yohanis Kaize</p>
                        <p class="text-muted">Jalur B · RT 03 / RW 01</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-1.5 sm:gap-3 text-right">
                        <x-badge-klasifikasi tingkat="sedang" />
                        <x-badge-status status="menunggu_verifikasi" />
                    </div>
                </div>

                {{-- Baris 2: Siti Rahayu --}}
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="space-y-0.5">
                        <p class="font-bold text-ink text-sm">Siti Rahayu</p>
                        <p class="text-muted">Poros Trans · RT 07 / RW 02</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-1.5 sm:gap-3 text-right">
                        <x-badge-klasifikasi tingkat="rendah" />
                        <x-badge-status status="terverifikasi" />
                    </div>
                </div>

                {{-- Baris 3: Petrus Gebze --}}
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="space-y-0.5">
                        <p class="font-bold text-ink text-sm">Petrus Gebze</p>
                        <p class="text-muted">Jalur A · RT 01 / RW 01</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-1.5 sm:gap-3 text-right">
                        <x-badge-klasifikasi tingkat="tinggi" />
                        <x-badge-status status="terverifikasi" />
                    </div>
                </div>

                {{-- Baris 4: Agustina Mahuze --}}
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="space-y-0.5">
                        <p class="font-bold text-ink text-sm">Agustina Mahuze</p>
                        <p class="text-muted">Jalur B · RT 04 / RW 02</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-1.5 sm:gap-3 text-right">
                        <x-badge-klasifikasi tingkat="rendah" />
                        <x-badge-status status="terverifikasi" />
                    </div>
                </div>

                {{-- Baris 5: Kornelis Ndiken --}}
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="space-y-0.5">
                        <p class="font-bold text-ink text-sm">Kornelis Ndiken</p>
                        <p class="text-muted">Poros Trans · RT 09 / RW 03</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-end sm:items-center gap-1.5 sm:gap-3 text-right">
                        <x-badge-klasifikasi tingkat="sedang" />
                        <x-badge-status status="menunggu_verifikasi" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Card: Tombol Aksi Utama Halaman (PRD 10.2) --}}
        <div class="card flex flex-col justify-between p-6 bg-brand/[0.04] border-brand/20">
            <div class="space-y-3">
                <div class="h-12 w-12 rounded-xl bg-brand text-white flex items-center justify-center shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                </div>
                <h3 class="text-section-title text-ink">Input Data Warga Baru</h3>
                <p class="text-body text-muted text-xs leading-relaxed">
                    Sedang berada di lapangan bersama warga responden Kampung Sota? Buka formulir survei 3 bagian lengkap dengan koordinat GPS dan foto bangunan rumah.
                </p>
            </div>

            <div class="pt-6">
                <a href="/operator/data/create" class="btn-primary w-full !min-h-[48px] shadow-md font-bold text-center">
                    ＋ Mulai Input Data Baru
                </a>
            </div>
        </div>
    </div>

    {{-- ============================================================
         TOMBOL MENGAMBANG / FAB KHUSUS MOBILE (<640px) — PRD 10.2
         ============================================================ --}}
    <a href="/operator/data/create"
       class="sm:hidden fixed bottom-6 right-6 z-40 h-14 w-14 rounded-full bg-brand text-white shadow-2xl flex items-center justify-center hover:bg-brand-dark transition-all active:scale-95"
       aria-label="Input data warga baru">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    </a>
</div>
@endsection
