@extends('layouts.app', [
    'role' => 'kepala_kampung',
    'userName' => 'Kepala Kampung Sota'
])

@section('title', 'Dashboard Eksekutif — SOTA-GIS')
@section('page-title', 'Dashboard Kerentanan Sosial Ekonomi')

@push('head')
    @vite(['resources/js/dashboard-pimpinan.js'])
@endpush

@section('content')
<div class="space-y-6">
    {{-- Header Sambutan, Lokasi, Tanggal & Tombol Laporan (PRD 12.1 Wireframe) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-line">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-ink">Kampung Sota, Distrik Sota, Kabupaten Merauke</h2>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-brand/10 text-brand border border-brand/20">
                    Eksekutif
                </span>
            </div>
            <p class="text-help text-xs mt-0.5">
                Data resmi hasil verifikasi lapangan per <strong class="text-ink">4 September 2026</strong>. Digunakan sebagai dasar pertimbangan sasaran bantuan sosial dan intervensi pembangunan kampung.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="/pimpinan/laporan" class="btn-primary !min-h-[42px] !text-xs font-bold shadow-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>⤓ Unduh Laporan Rekapitulasi</span>
            </a>
        </div>
    </div>

    {{-- 4 Kartu Metrik Ringkas (PRD 12.1 Wireframe) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Data --}}
        <x-stat-card
            label="Rumah tangga terdata"
            value="91"
            description="100% data terverifikasi"
            color="brand"
        />

        {{-- Rendah --}}
        <div class="card p-5 border-rendah/30 bg-rendah-bg/20 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-rendah-tx flex items-center gap-1.5">
                    <span class="text-base leading-none">●</span> Kerentanan Rendah
                </span>
                <span class="text-xs font-bold text-rendah-tx bg-white px-2 py-0.5 rounded-full border border-rendah/20 shadow-xs">
                    7,7%
                </span>
            </div>
            <div class="mt-2">
                <p class="text-metric text-rendah font-bold tabular-nums">7</p>
                <p class="text-xs text-muted mt-0.5">rumah tangga sejahtera</p>
            </div>
        </div>

        {{-- Sedang --}}
        <div class="card p-5 border-sedang/30 bg-sedang-bg/20 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-sedang-tx flex items-center gap-1.5">
                    <span class="text-base leading-none">▲</span> Kerentanan Sedang
                </span>
                <span class="text-xs font-bold text-sedang-tx bg-white px-2 py-0.5 rounded-full border border-sedang/20 shadow-xs">
                    65,9%
                </span>
            </div>
            <div class="mt-2">
                <p class="text-metric text-sedang font-bold tabular-nums">60</p>
                <p class="text-xs text-muted mt-0.5">mayoritas populasi warga</p>
            </div>
        </div>

        {{-- Tinggi --}}
        <div class="card p-5 border-tinggi/30 bg-tinggi-bg/20 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-tinggi-tx flex items-center gap-1.5">
                    <span class="text-base leading-none">◆</span> Kerentanan Tinggi
                </span>
                <span class="text-xs font-bold text-tinggi-tx bg-white px-2 py-0.5 rounded-full border border-tinggi/20 shadow-xs">
                    26,4%
                </span>
            </div>
            <div class="mt-2">
                <p class="text-metric text-tinggi font-bold tabular-nums">24</p>
                <p class="text-xs text-muted mt-0.5">prioritas penerima bantuan</p>
            </div>
        </div>
    </div>

    {{-- 2 Grafik Analitik Chart.js (PRD 12.1 & 12.2) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Grafik 1: Pie Chart Persentase Tingkat Kerentanan --}}
        <div class="card space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-line">
                    <div>
                        <h3 class="text-section-title text-ink">Persentase Tingkat Kerentanan</h3>
                        <p class="text-help text-xs">Proporsi kelas kerentanan sosial ekonomi warga Kampung Sota</p>
                    </div>
                </div>

                {{-- Area Canvas Chart.js --}}
                <div class="relative h-60 w-full pt-2">
                    <canvas id="chart-svi-pie"></canvas>
                </div>

                {{-- Legenda & Tabel Angka Setara (Aksesibilitas Sesuai PRD 12.2) --}}
                <div class="pt-4 mt-2 border-t border-line space-y-2">
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="bg-rendah-bg/40 p-2 rounded-lg border border-rendah/20">
                            <span class="font-bold text-rendah-tx block text-sm tabular-nums">7 KK</span>
                            <span class="text-muted text-[11px]">● Rendah (7,7%)</span>
                        </div>
                        <div class="bg-sedang-bg/40 p-2 rounded-lg border border-sedang/20">
                            <span class="font-bold text-sedang-tx block text-sm tabular-nums">60 KK</span>
                            <span class="text-muted text-[11px]">▲ Sedang (65,9%)</span>
                        </div>
                        <div class="bg-tinggi-bg/40 p-2 rounded-lg border border-tinggi/20">
                            <span class="font-bold text-tinggi-tx block text-sm tabular-nums">24 KK</span>
                            <span class="text-muted text-[11px]">◆ Tinggi (26,4%)</span>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-[11px] text-muted italic pt-2 border-t border-line text-center sm:text-left">
                * Semua grafik hanya menghitung data berstatus Terverifikasi.
            </p>
        </div>

        {{-- Grafik 2: Bar Chart Bertumpuk Per Wilayah (Jalur A, Jalur B, Poros Trans) --}}
        <div class="card space-y-4 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-line">
                    <div>
                        <h3 class="text-section-title text-ink">Sebaran Tingkat Kerentanan Per Wilayah</h3>
                        <p class="text-help text-xs">Menjawab jalur mana yang paling mendesak untuk intervensi program</p>
                    </div>
                </div>

                {{-- Area Canvas Chart.js --}}
                <div class="relative h-60 w-full pt-2">
                    <canvas id="chart-wilayah-bar"></canvas>
                </div>

                {{-- Legenda Indikator --}}
                <div class="flex items-center justify-center gap-5 text-xs text-muted pt-2">
                    <span class="flex items-center gap-1.5 font-medium text-ink">
                        <span class="h-3 w-3 rounded bg-rendah"></span> Rendah
                    </span>
                    <span class="flex items-center gap-1.5 font-medium text-ink">
                        <span class="h-3 w-3 rounded bg-sedang"></span> Sedang
                    </span>
                    <span class="flex items-center gap-1.5 font-medium text-ink">
                        <span class="h-3 w-3 rounded bg-tinggi"></span> Tinggi
                    </span>
                </div>

                {{-- Tabel Rincian Angka Setara (PRD 12.2) --}}
                <div class="pt-3 border-t border-line overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="text-muted font-semibold border-b border-line pb-1">
                                <th class="pb-1">Wilayah</th>
                                <th class="pb-1 text-center text-rendah font-bold">Rendah</th>
                                <th class="pb-1 text-center text-sedang font-bold">Sedang</th>
                                <th class="pb-1 text-center text-tinggi font-bold">Tinggi</th>
                                <th class="pb-1 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-ink tabular-nums">
                            <tr>
                                <td class="py-1.5 font-medium">Jalur A</td>
                                <td class="py-1.5 text-center">0</td>
                                <td class="py-1.5 text-center">7</td>
                                <td class="py-1.5 text-center font-bold text-tinggi">8</td>
                                <td class="py-1.5 text-right font-bold">15 KK</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 font-medium">Jalur B</td>
                                <td class="py-1.5 text-center">4</td>
                                <td class="py-1.5 text-center">21</td>
                                <td class="py-1.5 text-center font-bold text-tinggi">8</td>
                                <td class="py-1.5 text-right font-bold">33 KK</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 font-medium">Poros Trans</td>
                                <td class="py-1.5 text-center">3</td>
                                <td class="py-1.5 text-center">32</td>
                                <td class="py-1.5 text-center font-bold text-tinggi">8</td>
                                <td class="py-1.5 text-right font-bold">43 KK</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="text-[11px] text-muted italic pt-2 border-t border-line text-center sm:text-left">
                * Semua grafik hanya menghitung data berstatus Terverifikasi.
            </p>
        </div>
    </div>

    {{-- Peta Sebaran Kerentanan Ringkas (Leaflet 380px Hanya-Baca) (PRD 12.1) --}}
    <div class="card space-y-3 !p-0 overflow-hidden">
        <div class="p-4 border-b border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-section-title text-ink">Peta Sebaran Titik Rumah Tangga</h3>
                <p class="text-help text-xs">Peta spasial sebaran titik tempat tinggal warga terverifikasi di Kampung Sota</p>
            </div>
            <a href="/peta" target="_blank" class="text-xs font-bold text-brand hover:underline flex items-center gap-1">
                <span>Buka peta interaktif penuh</span>
                <span>→</span>
            </a>
        </div>

        {{-- Container Peta Ringkas Leaflet (380px) --}}
        <div class="relative w-full h-[380px] bg-paper">
            <div id="peta-ringkas-pimpinan" class="w-full h-full z-0"></div>

            {{-- Legenda Mengambang di Peta Ringkas --}}
            <div class="absolute bottom-3 left-3 z-[400] bg-surface/95 backdrop-blur-md rounded-lg shadow-md border border-line px-3 py-2 text-xs flex items-center gap-4">
                <span class="flex items-center gap-1.5 text-ink font-semibold">
                    <span class="h-3 w-3 rounded-full bg-rendah border border-white"></span> Rendah
                </span>
                <span class="flex items-center gap-1.5 text-ink font-semibold">
                    <span class="text-sedang font-bold leading-none text-sm">▲</span> Sedang
                </span>
                <span class="flex items-center gap-1.5 text-ink font-semibold">
                    <span class="text-tinggi font-bold leading-none text-sm">◆</span> Tinggi
                </span>
            </div>
        </div>
    </div>

    {{-- Tabel Rumah Tangga Kerentanan Tinggi (Prioritas Bansos) (PRD 12.1) --}}
    <div class="card overflow-hidden !p-0 shadow-sm">
        <div class="p-4 border-b border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-section-title text-ink">Rumah Tangga Kerentanan Tinggi</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-tinggi-bg text-tinggi-tx border border-tinggi/20">
                        24 KK Prioritas
                    </span>
                </div>
                <p class="text-help text-xs mt-0.5">Daftar kepala keluarga dengan skor kerentanan tertinggi (skor 21–27) untuk prioritas program bantuan sosial.</p>
            </div>
            <a href="/pimpinan/laporan" class="text-xs font-bold text-brand hover:underline">
                Lihat laporan lengkap (24 KK) →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-paper border-b border-line text-xs font-semibold uppercase tracking-wider text-muted">
                    <tr>
                        <th class="py-3 px-4">Nama Kepala Keluarga</th>
                        <th class="py-3 px-4">Jalur / Wilayah</th>
                        <th class="py-3 px-4">Alamat Detail</th>
                        <th class="py-3 px-4 text-center">Total Skor SVI</th>
                        <th class="py-3 px-4">Klasifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    <tr class="hover:bg-paper/40 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-ink">Fransiskus Samkakai</td>
                        <td class="py-3.5 px-4 text-xs font-medium">Jalur A</td>
                        <td class="py-3.5 px-4 text-xs text-muted">RT 02 / RW 01 No. 09</td>
                        <td class="py-3.5 px-4 text-center font-bold text-tinggi tabular-nums">25</td>
                        <td class="py-3.5 px-4">
                            <x-badge-klasifikasi tingkat="tinggi" />
                        </td>
                    </tr>
                    <tr class="hover:bg-paper/40 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-ink">Dominikus Mejai</td>
                        <td class="py-3.5 px-4 text-xs font-medium">Jalur A</td>
                        <td class="py-3.5 px-4 text-xs text-muted">RT 01 / RW 01 No. 07</td>
                        <td class="py-3.5 px-4 text-center font-bold text-tinggi tabular-nums">24</td>
                        <td class="py-3.5 px-4">
                            <x-badge-klasifikasi tingkat="tinggi" />
                        </td>
                    </tr>
                    <tr class="hover:bg-paper/40 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-ink">Petrus Gebze</td>
                        <td class="py-3.5 px-4 text-xs font-medium">Jalur A</td>
                        <td class="py-3.5 px-4 text-xs text-muted">RT 01 / RW 01 No. 04</td>
                        <td class="py-3.5 px-4 text-center font-bold text-tinggi tabular-nums">23</td>
                        <td class="py-3.5 px-4">
                            <x-badge-klasifikasi tingkat="tinggi" />
                        </td>
                    </tr>
                    <tr class="hover:bg-paper/40 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-ink">Markus Balagaize</td>
                        <td class="py-3.5 px-4 text-xs font-medium">Jalur B</td>
                        <td class="py-3.5 px-4 text-xs text-muted">RT 04 / RW 02 No. 18</td>
                        <td class="py-3.5 px-4 text-center font-bold text-tinggi tabular-nums">22</td>
                        <td class="py-3.5 px-4">
                            <x-badge-klasifikasi tingkat="tinggi" />
                        </td>
                    </tr>
                    <tr class="hover:bg-paper/40 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-ink">Theresia Yolmen</td>
                        <td class="py-3.5 px-4 text-xs font-medium">Poros Trans</td>
                        <td class="py-3.5 px-4 text-xs text-muted">RT 11 / RW 03 No. 02</td>
                        <td class="py-3.5 px-4 text-center font-bold text-tinggi tabular-nums">21</td>
                        <td class="py-3.5 px-4">
                            <x-badge-klasifikasi tingkat="tinggi" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
