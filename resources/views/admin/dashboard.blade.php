@extends('layouts.app', [
    'role' => 'admin',
    'userName' => 'Administrator',
    'verifikasiCount' => 7
])

@section('title', 'Dashboard Admin — SOTA-GIS')
@section('page-title', 'Dashboard Administrator')

@section('content')
<div class="space-y-6">
    {{-- Header Sambutan & Keterangan --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-ink">Halo, Administrator.</h2>
            <p class="text-help mt-0.5">Ada <strong class="text-sedang font-bold">7 data survei baru</strong> yang membutuhkan verifikasi kelayakan sebelum tampil di peta publik.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="/admin/data/create" class="btn-secondary !min-h-[42px] !text-xs font-semibold">
                ＋ Tambah Data
            </a>
            <a href="/admin/verifikasi" class="btn-primary !min-h-[42px] !text-xs font-bold shadow-sm">
                Buka Antrean Verifikasi (7) →
            </a>
        </div>
    </div>

    {{-- 4 Kartu Metrik Utama (PRD 11.1 Wireframe) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Data --}}
        <x-stat-card
            label="Total data warga"
            value="91"
            description="rumah tangga terdata"
            color="brand"
            href="/admin/data"
        />

        {{-- Card 2: Menunggu Verifikasi (Pintu Masuk Utama Halaman Ini - PRD 11.1) --}}
        <a href="/admin/verifikasi"
           class="card flex flex-col justify-between p-5 bg-sedang-bg/30 border-sedang/40 hover:border-sedang hover:shadow-md transition-all group">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-sedang-tx">Menunggu Verifikasi</p>
                <p class="text-metric text-sedang tabular-nums mt-1">7</p>
            </div>
            <div class="flex items-center justify-between text-xs font-bold text-sedang-tx pt-2 border-t border-sedang/20 mt-2">
                <span>Periksa antrean</span>
                <span class="group-hover:translate-x-1 transition-transform">→</span>
            </div>
        </a>

        {{-- Card 3: Terverifikasi --}}
        <x-stat-card
            label="Terverifikasi"
            value="84"
            description="● aktif di peta publik"
            color="rendah"
            href="/admin/data"
        />

        {{-- Card 4: Kerentanan Tinggi --}}
        <x-stat-card
            label="Kerentanan tinggi"
            value="24"
            description="◆ 26,4% prioritas bansos"
            color="tinggi"
            href="/admin/data"
        />
    </div>

    {{-- Grid 2 Kolom: Antrean Verifikasi Terbaru & Sebaran Kerentanan --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Antrean Verifikasi Terbaru (5 Baris) --}}
        <div class="lg:col-span-2 card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-line">
                <div>
                    <h3 class="text-section-title text-ink">Antrean verifikasi terbaru</h3>
                    <p class="text-help text-xs">Data masuk dari kader lapangan yang perlu disandingkan dengan foto rumah</p>
                </div>
                <a href="/admin/verifikasi" class="text-xs font-bold text-brand hover:underline">
                    Buka antrean verifikasi →
                </a>
            </div>

            <div class="divide-y divide-line text-xs">
                {{-- Baris 1: Yohanis Kaize --}}
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="h-11 w-11 rounded-lg bg-paper border border-line flex items-center justify-center text-muted font-bold text-base flex-shrink-0">
                            🏠
                        </div>
                        <div class="space-y-0.5">
                            <p class="font-bold text-ink text-sm">Yohanis Kaize</p>
                            <p class="text-muted">Jalur B · RT 03/RW 01 · Maria K. <span class="text-slate-400 font-normal">(1 jam lalu)</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-badge-klasifikasi tingkat="sedang" />
                        <a href="/admin/verifikasi" class="btn-primary !min-h-[32px] !py-1 !px-3 !text-xs font-bold">
                            Periksa
                        </a>
                    </div>
                </div>

                {{-- Baris 2: Siti Rahayu --}}
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="h-11 w-11 rounded-lg bg-paper border border-line flex items-center justify-center text-muted font-bold text-base flex-shrink-0">
                            🏠
                        </div>
                        <div class="space-y-0.5">
                            <p class="font-bold text-ink text-sm">Siti Rahayu</p>
                            <p class="text-muted">Poros Trans · RT 07/RW 02 · Maria K. <span class="text-slate-400 font-normal">(2 jam lalu)</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-badge-klasifikasi tingkat="rendah" />
                        <a href="/admin/verifikasi" class="btn-primary !min-h-[32px] !py-1 !px-3 !text-xs font-bold">
                            Periksa
                        </a>
                    </div>
                </div>

                {{-- Baris 3: Petrus Gebze --}}
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="h-11 w-11 rounded-lg bg-paper border border-line flex items-center justify-center text-muted font-bold text-base flex-shrink-0">
                            🏠
                        </div>
                        <div class="space-y-0.5">
                            <p class="font-bold text-ink text-sm">Petrus Gebze</p>
                            <p class="text-muted">Jalur A · RT 01/RW 01 · Yosep M. <span class="text-slate-400 font-normal">(3 jam lalu)</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-badge-klasifikasi tingkat="tinggi" />
                        <a href="/admin/verifikasi" class="btn-primary !min-h-[32px] !py-1 !px-3 !text-xs font-bold">
                            Periksa
                        </a>
                    </div>
                </div>

                {{-- Baris 4: Kornelis Ndiken --}}
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="h-11 w-11 rounded-lg bg-paper border border-line flex items-center justify-center text-muted font-bold text-base flex-shrink-0">
                            🏠
                        </div>
                        <div class="space-y-0.5">
                            <p class="font-bold text-ink text-sm">Kornelis Ndiken</p>
                            <p class="text-muted">Poros Trans · RT 09/RW 03 · Maria K. <span class="text-slate-400 font-normal">(4 jam lalu)</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-badge-klasifikasi tingkat="sedang" />
                        <a href="/admin/verifikasi" class="btn-primary !min-h-[32px] !py-1 !px-3 !text-xs font-bold">
                            Periksa
                        </a>
                    </div>
                </div>

                {{-- Baris 5: Dominikus Mejai --}}
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="h-11 w-11 rounded-lg bg-paper border border-line flex items-center justify-center text-muted font-bold text-base flex-shrink-0">
                            🏠
                        </div>
                        <div class="space-y-0.5">
                            <p class="font-bold text-ink text-sm">Dominikus Mejai</p>
                            <p class="text-muted">Jalur A · RT 01/RW 01 · Yosep M. <span class="text-slate-400 font-normal">(5 jam lalu)</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-badge-klasifikasi tingkat="tinggi" />
                        <a href="/admin/verifikasi" class="btn-primary !min-h-[32px] !py-1 !px-3 !text-xs font-bold">
                            Periksa
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sebaran Kerentanan Ringkas (Pie Visual) --}}
        <div class="card space-y-4 flex flex-col justify-between">
            <div>
                <div class="border-b border-line pb-3">
                    <h3 class="text-section-title text-ink">Sebaran kerentanan</h3>
                    <p class="text-help text-xs">Proporsi dari total 91 rumah tangga terverifikasi</p>
                </div>

                <div class="space-y-4 py-4">
                    {{-- Rendah --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-1.5 font-medium text-ink">
                                <span class="text-rendah font-bold text-sm">●</span> Kerentanan Rendah
                            </span>
                            <span class="font-bold text-ink tabular-nums">7 <span class="text-muted font-normal">(7,7%)</span></span>
                        </div>
                        <div class="w-full bg-paper h-2.5 rounded-full overflow-hidden border border-line/40">
                            <div class="bg-rendah h-full rounded-full" style="width: 7.7%"></div>
                        </div>
                    </div>

                    {{-- Sedang --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-1.5 font-medium text-ink">
                                <span class="text-sedang font-bold text-sm">▲</span> Kerentanan Sedang
                            </span>
                            <span class="font-bold text-ink tabular-nums">60 <span class="text-muted font-normal">(65,9%)</span></span>
                        </div>
                        <div class="w-full bg-paper h-2.5 rounded-full overflow-hidden border border-line/40">
                            <div class="bg-sedang h-full rounded-full" style="width: 65.9%"></div>
                        </div>
                    </div>

                    {{-- Tinggi --}}
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-1.5 font-medium text-ink">
                                <span class="text-tinggi font-bold text-sm">◆</span> Kerentanan Tinggi
                            </span>
                            <span class="font-bold text-ink tabular-nums">24 <span class="text-muted font-normal">(26,4%)</span></span>
                        </div>
                        <div class="w-full bg-paper h-2.5 rounded-full overflow-hidden border border-line/40">
                            <div class="bg-tinggi h-full rounded-full" style="width: 26.4%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-line text-xs text-muted flex items-center justify-between">
                <span>Total: <strong>91 KK</strong></span>
                <a href="/admin/data" class="text-brand font-bold hover:underline">Lihat rekapitulasi →</a>
            </div>
        </div>
    </div>
</div>
@endsection
