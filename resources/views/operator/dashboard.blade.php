@extends('layouts.app', ['role' => 'operator', 'userName' => Auth::user()?->name ?? 'Operator'])

@section('title', 'Dashboard Operator — SOTA-GIS')
@section('page-title', 'Dashboard Operator')

@section('content')
<div class="space-y-6">
    {{-- Ucapan Selamat Datang & Subtitle --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-ink">Halo, {{ Auth::user()?->name ?? 'Operator' }}.</h2>
            <p class="text-help mt-0.5">
                <span class="font-bold text-sedang tabular-nums">{{ $totalPending ?? 0 }}</span> data survei warga saat ini sedang menunggu verifikasi Admin.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <div class="flex items-center gap-2 text-xs font-semibold text-muted bg-white px-3 py-1.5 rounded-xl border border-line shadow-2xs">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>{{ $statusSinkronisasi ?? ("Sinkron WIT: " . now("Asia/Jayapura")->translatedFormat("d F Y, H:i") . " WIT") }}</span>
            </div>
            <a href="/operator/data/create" class="hidden sm:inline-flex btn-primary shadow-sm font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Input Data Warga Baru</span>
            </a>
        </div>
    </div>

    {{-- Tiga Kartu Metrik Ringkas Dinamis --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="/operator/data" class="card p-5 border-l-4 border-l-brand hover:shadow-md transition-shadow block">
            <p class="text-xs font-bold uppercase tracking-wider text-muted">Data terinput</p>
            <p class="text-metric text-ink tabular-nums mt-1">{{ number_format($totalData ?? 0) }}</p>
            <p class="text-xs text-muted mt-2">rumah tangga</p>
        </a>
        <a href="/operator/data?status=menunggu_verifikasi" class="card p-5 border-l-4 border-l-sedang hover:shadow-md transition-shadow block">
            <p class="text-xs font-bold uppercase tracking-wider text-muted">Menunggu verifikasi</p>
            <p class="text-metric text-sedang tabular-nums mt-1">{{ number_format($totalPending ?? 0) }}</p>
            <p class="text-xs text-sedang-tx mt-2 flex items-center gap-1">
                <span class="inline-block w-2 h-2 rounded-full bg-sedang"></span>
                <span>perlu diperiksa Admin</span>
            </p>
        </a>
        <a href="/operator/data?status=terverifikasi" class="card p-5 border-l-4 border-l-rendah hover:shadow-md transition-shadow block">
            <p class="text-xs font-bold uppercase tracking-wider text-muted">Terverifikasi</p>
            <p class="text-metric text-rendah tabular-nums mt-1">{{ number_format($totalTerverifikasi ?? 0) }}</p>
            <p class="text-xs text-rendah-tx mt-2 flex items-center gap-1">
                <span class="inline-block w-2 h-2 rounded-full bg-rendah"></span>
                <span>telah disetujui Admin</span>
            </p>
        </a>
    </div>

    {{-- Grid Bawah: Daftar Input Terakhir & Tombol Tambah Cepat --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Card: Input Terakhir (5 Baris Dinamis) --}}
        <div class="lg:col-span-2 card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-line">
                <div>
                    <h3 class="text-section-title text-ink">Input terakhir</h3>
                    <p class="text-help text-xs">Rumah tangga terbaru yang Anda data di lapangan</p>
                </div>
                <a href="/operator/data" class="text-xs font-bold text-brand hover:underline">
                    Lihat semua data saya →
                </a>
            </div>

            <div class="divide-y divide-line text-xs">
                @forelse($inputTerakhir ?? [] as $item)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="space-y-0.5">
                            <p class="font-bold text-ink text-sm">{{ $item->nama_kk }}</p>
                            <p class="text-muted">
                                {{ $item->jalur }} · RT {{ $item->rt ?? '-' }} / RW {{ $item->rw ?? '-' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($item->status_verifikasi === 'terverifikasi')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rendah-bg text-rendah-tx border border-rendah/30">
                                    Terverifikasi
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-sedang-bg text-sedang-tx border border-sedang/30">
                                    Menunggu verifikasi
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-muted italic">
                        Belum ada data survei warga yang diinputkan.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Card: Tombol Aksi Utama Halaman --}}
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

    {{-- TOMBOL MENGAMBANG / FAB KHUSUS MOBILE (<640px) --}}
    <a href="/operator/data/create"
       class="sm:hidden fixed bottom-6 right-6 z-40 h-14 w-14 rounded-full bg-brand text-white shadow-2xl flex items-center justify-center hover:bg-brand-dark transition-all active:scale-95"
       aria-label="Input data warga baru">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    </a>
</div>
@endsection
