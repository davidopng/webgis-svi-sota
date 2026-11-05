@extends('layouts.app', [
    'role' => 'admin',
    'userName' => Auth::user()?->name ?? 'Admin'
])

@section('title', 'Dashboard Admin — SOTA-GIS')
@section('page-title', 'Dashboard Admin')

@php
    $terverifikasiCount = $terverifikasiCount ?? 0;
    $menungguCount = $menungguCount ?? 0;
    $totalWarga = $totalWarga ?? 0;
    $tinggiCount = $tinggiCount ?? 0;
    $sedangCount = $sedangCount ?? 0;
    $rendahCount = $rendahCount ?? 0;

    $persenTinggi = $terverifikasiCount > 0 ? number_format(($tinggiCount / $terverifikasiCount) * 100, 1, ',', '.') : '0,0';
    $persenSedang = $terverifikasiCount > 0 ? number_format(($sedangCount / $terverifikasiCount) * 100, 1, ',', '.') : '0,0';
    $persenRendah = $terverifikasiCount > 0 ? number_format(($rendahCount / $terverifikasiCount) * 100, 1, ',', '.') : '0,0';
@endphp

@section('content')
<div class="space-y-6">
    {{-- Header Sambutan & Keterangan --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-ink">Halo, {{ Auth::user()?->name ?? 'Admin' }}.</h2>
            <p class="text-help mt-0.5">
                Ada <strong class="text-sedang font-bold">{{ $menungguCount }} data survei baru</strong> yang membutuhkan verifikasi kelayakan sebelum dapat diakses pada peta sebaran.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="/admin/data/create" class="btn-secondary !min-h-[42px] !text-xs font-semibold">
                ＋ Tambah Data
            </a>
            <a href="/admin/verifikasi" class="btn-primary !min-h-[42px] !text-xs font-bold shadow-sm flex items-center gap-1.5">
                <span>Buka Antrean Verifikasi ({{ $menungguCount }})</span>
                <span>→</span>
            </a>
        </div>
    </div>

    {{-- 4 Kartu Metrik Utama --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Data --}}
        <a href="/admin/data" class="card flex flex-col gap-2 cursor-pointer hover:border-brand/30 transition-colors">
            <p class="text-label text-muted">Total data warga</p>
            <p class="text-metric text-brand tabular-nums">{{ number_format($totalWarga) }}</p>
            <p class="text-help">rumah tangga terdata</p>
        </a>

        {{-- Card 2: Menunggu Verifikasi --}}
        <a href="/admin/verifikasi"
           class="card flex flex-col justify-between p-5 bg-sedang-bg/30 border-sedang/40 hover:border-sedang hover:shadow-md transition-all group">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-sedang-tx">Menunggu Verifikasi</p>
                <p class="text-metric text-sedang tabular-nums mt-1">{{ number_format($menungguCount) }}</p>
            </div>
            <div class="flex items-center justify-between text-xs font-bold text-sedang-tx pt-2 border-t border-sedang/20 mt-2">
                <span>Periksa antrean</span>
                <span class="group-hover:translate-x-1 transition-transform">→</span>
            </div>
        </a>

        {{-- Card 3: Terverifikasi --}}
        <a href="/admin/data" class="card flex flex-col gap-2 cursor-pointer hover:border-brand/30 transition-colors">
            <p class="text-label text-muted">Terverifikasi</p>
            <p class="text-metric text-rendah tabular-nums">{{ number_format($terverifikasiCount) }}</p>
            <p class="text-help">● aktif di peta sebaran</p>
        </a>

        {{-- Card 4: Kerentanan Tinggi --}}
        <a href="/admin/data" class="card flex flex-col gap-2 cursor-pointer hover:border-brand/30 transition-colors">
            <p class="text-label text-muted">Kerentanan tinggi</p>
            <p class="text-metric text-tinggi tabular-nums">{{ number_format($tinggiCount) }}</p>
            <p class="text-help">● {{ $persenTinggi }}% prioritas intervensi</p>
        </a>
    </div>

    {{-- Kartu Analisis Otomatis & Rekomendasi Kebijakan SVI --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900 to-teal-950 border border-teal-500/30 p-5 sm:p-6 shadow-xl shadow-teal-950/20 text-slate-100">
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-teal-500/20 pb-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-teal-500/20 text-teal-300 border border-teal-500/30 shadow-xs backdrop-blur-sm">
                        <span>✨</span>
                        <span>Analisis Otomatis SVI</span>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                        Analisis Otomatis &amp; Rekomendasi Kebijakan SVI
                    </h3>
                </div>
                <div class="flex items-center gap-2 text-[11px] text-teal-200/80 bg-teal-950/60 px-3 py-1.5 rounded-lg border border-teal-500/20 self-start sm:self-auto">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>{{ $statusSinkronisasi ?? ("Sinkron WIT: " . now("Asia/Jayapura")->translatedFormat("d F Y, H:i") . " WIT") }}</span>
                </div>
            </div>

            <div class="prose prose-invert max-w-none">
                {!! $analisisAI ?? '' !!}
            </div>
        </div>
    </div>

    {{-- Grid 2 Kolom: Antrean Verifikasi Terbaru & Sebaran Kerentanan --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Antrean Verifikasi Terbaru --}}
        <div class="lg:col-span-2 card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-line">
                <div>
                    <h3 class="text-section-title text-ink">Antrean verifikasi terbaru</h3>
                    <p class="text-help text-xs">Data masuk dari surveyor lapangan yang perlu disandingkan dengan foto rumah</p>
                </div>
                <a href="/admin/verifikasi" class="text-xs font-bold text-brand hover:underline">
                    Buka antrean verifikasi →
                </a>
            </div>

            <div class="divide-y divide-line text-xs">
                @forelse($antreanTerbaru as $item)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="h-11 w-11 rounded-lg bg-paper border border-line flex items-center justify-center text-muted font-bold text-base flex-shrink-0">
                                🏠
                            </div>
                            <div class="space-y-0.5">
                                <p class="font-bold text-ink text-sm">{{ $item->nama_kk }}</p>
                                <p class="text-muted">
                                    {{ $item->jalur }} · RT {{ $item->rt ?? '-' }}/RW {{ $item->rw ?? '-' }} · {{ $item->nama_penginput }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @php
                                $badgeCls = match($item->klasifikasi) {
                                    'tinggi' => 'bg-tinggi-bg text-tinggi-tx border border-tinggi/30',
                                    'sedang' => 'bg-sedang-bg text-sedang-tx border border-sedang/30',
                                    default => 'bg-rendah-bg text-rendah-tx border border-rendah/30'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeCls }}">
                                <span>●</span>
                                <span class="capitalize">Kerentanan {{ ucfirst($item->klasifikasi) }}</span>
                            </span>
                            <a href="/admin/verifikasi?id={{ $item->id }}" class="btn-primary !min-h-[32px] !py-1 !px-3 !text-xs font-bold">
                                Periksa
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-muted italic">
                        Tidak ada antrean verifikasi data baru saat ini.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Sebaran Kerentanan Ringkas --}}
        <div class="card space-y-4 flex flex-col justify-between">
            <div>
                <div class="border-b border-line pb-3">
                    <h3 class="text-section-title text-ink">Sebaran kerentanan</h3>
                    <p class="text-help text-xs">Proporsi dari total {{ $terverifikasiCount }} rumah tangga terverifikasi</p>
                </div>

                <div class="space-y-4 py-4">
                    {{-- Rendah --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-1.5 font-medium text-ink">
                                <span class="text-rendah font-bold text-sm">●</span> Kerentanan Rendah
                            </span>
                            <span class="font-bold text-ink tabular-nums">
                                {{ $rendahCount }} <span class="text-muted font-normal">({{ $persenRendah }}%)</span>
                            </span>
                        </div>
                        <div class="w-full bg-paper h-2.5 rounded-full overflow-hidden border border-line/40">
                            <div class="bg-rendah h-full rounded-full transition-all duration-300" style="width: {{ $terverifikasiCount > 0 ? ($rendahCount / $terverifikasiCount) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    {{-- Sedang --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-1.5 font-medium text-ink">
                                <span class="text-sedang font-bold text-sm">●</span> Kerentanan Sedang
                            </span>
                            <span class="font-bold text-ink tabular-nums">
                                {{ $sedangCount }} <span class="text-muted font-normal">({{ $persenSedang }}%)</span>
                            </span>
                        </div>
                        <div class="w-full bg-paper h-2.5 rounded-full overflow-hidden border border-line/40">
                            <div class="bg-sedang h-full rounded-full transition-all duration-300" style="width: {{ $terverifikasiCount > 0 ? ($sedangCount / $terverifikasiCount) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    {{-- Tinggi --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-1.5 font-medium text-ink">
                                <span class="text-tinggi font-bold text-sm">●</span> Kerentanan Tinggi
                            </span>
                            <span class="font-bold text-ink tabular-nums">
                                {{ $tinggiCount }} <span class="text-muted font-normal">({{ $persenTinggi }}%)</span>
                            </span>
                        </div>
                        <div class="w-full bg-paper h-2.5 rounded-full overflow-hidden border border-line/40">
                            <div class="bg-tinggi h-full rounded-full transition-all duration-300" style="width: {{ $terverifikasiCount > 0 ? ($tinggiCount / $terverifikasiCount) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-line text-xs text-muted flex items-center justify-between">
                <span>Total: <strong>{{ $terverifikasiCount }} KK</strong></span>
                <a href="/admin/data" class="text-brand font-bold hover:underline">Lihat rekapitulasi →</a>
            </div>
        </div>
    </div>
</div>
@endsection
