@extends('layouts.app', [
    'role' => 'kepala_kampung',
    'userName' => 'Kepala Kampung Sota'
])

@section('title', 'Dokumentasi Metodologi SVI — SOTA-GIS')
@section('page-title', 'Metodologi Social Vulnerability Index (SVI)')

@section('content')
<div class="space-y-6">

    {{-- Hero Header --}}
    <div class="card bg-surface border border-line p-6 sm:p-8 space-y-3 shadow-sm">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-brand/10 text-brand border border-brand/20">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>Dokumentasi Ilmiah &amp; Metodologi</span>
        </div>
        <h2 class="text-xl sm:text-2xl font-extrabold text-ink tracking-tight">
            Identifikasi &amp; Pemetaan Kerentanan Sosial Ekonomi Masyarakat Kampung Sota
        </h2>
        <p class="text-body text-ink/80 leading-relaxed text-xs sm:text-sm max-w-4xl">
            SOTA-GIS merupakan platform Sistem Informasi Geografis (WebGIS) yang mentransformasikan data sensus sosial ekonomi rumah tangga di wilayah perbatasan Republik Indonesia dan Papua Nugini menjadi sebaran spasial digital interaktif guna penentuan sasaran program bantuan sosial pemerintah kampung secara objektif dan berkeadilan.
        </p>
    </div>

    {{-- Bagian 1: Konteks Wilayah Kampung Sota --}}
    <div class="card space-y-4">
        <h3 class="text-section-title text-ink border-b border-line pb-2 flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-paper text-brand font-bold text-xs border border-line">1</span>
            Konteks Wilayah &amp; Urgensi Sistem
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="bg-paper p-4 rounded-xl border border-line space-y-1">
                <span class="text-muted block font-semibold">Wilayah Administratif</span>
                <strong class="text-ink text-sm block">Kampung Sota</strong>
                <span class="text-muted block text-[11px]">Distrik Sota, Kabupaten Merauke, Provinsi Papua Selatan</span>
            </div>
            <div class="bg-paper p-4 rounded-xl border border-line space-y-1">
                <span class="text-muted block font-semibold">Populasi Penduduk</span>
                <strong class="text-ink text-sm block">1.686 Jiwa (91 KK Terdata)</strong>
                <span class="text-muted block text-[11px]">Tersebar di 12 RT, 3 RW, dan 3 koridor utama permukiman</span>
            </div>
            <div class="bg-paper p-4 rounded-xl border border-line space-y-1">
                <span class="text-muted block font-semibold">Koridor Permukiman</span>
                <strong class="text-ink text-sm block">Jalur A, Jalur B, &amp; Poros Trans</strong>
                <span class="text-muted block text-[11px]">Zona permukiman inti di garis perbatasan darat RI - PNG</span>
            </div>
        </div>
        <p class="text-body text-ink text-xs sm:text-sm leading-relaxed">
            Selama ini pendataan sosial ekonomi di Kampung Sota masih berjalan dalam format arsip dokumen teks berlembar-lembar. Akibatnya, pemerintah kampung dan perangkat distrik mengalami kesulitan dalam melihat secara cepat <em>di mana sebenarnya kantong-kantong keluarga rentan terkonsentrasi</em>. WebGIS SVI ini memetakan unit analisis terkecil tingkat keluarga (Kepala Keluarga) ke dalam koordinat spasial global WGS84.
        </p>
    </div>

    {{-- Bagian 2: Kerangka Metode SVI & Additive Scoring --}}
    <div class="card space-y-4">
        <h3 class="text-section-title text-ink border-b border-line pb-2 flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-paper text-brand font-bold text-xs border border-line">2</span>
            Metode Social Vulnerability Index (SVI)
        </h3>
        <p class="text-body text-ink text-xs sm:text-sm leading-relaxed">
            Tingkat kerentanan setiap rumah tangga dihitung menggunakan pendekatan <strong>Additive Scoring</strong> (penjumlahan skor tertimbang). Masing-masing dari 9 variabel sosial ekonomi diberikan skor diskrit bernilai <strong>1 (kondisi baik / sejahtera)</strong>, <strong>2 (kondisi menengah)</strong>, hingga <strong>3 (kondisi paling rentan)</strong>.
        </p>

        {{-- Formula Box --}}
        <div class="bg-brand/5 border border-brand/20 p-4 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold text-brand uppercase tracking-wider block">Formula Total Skor SVI</span>
                <p class="font-mono text-sm font-bold text-ink mt-0.5">
                    Total Skor = V<sub>1</sub> + V<sub>2</sub> + V<sub>3</sub> + V<sub>4</sub> + V<sub>5</sub> + V<sub>6</sub> + V<sub>7</sub> + V<sub>8</sub> + V<sub>9</sub>
                </p>
                <span class="text-[11px] text-muted block mt-1">Rentang skor teoritis: minimum 9 (paling sejahtera) hingga maksimum 27 (paling rentan).</span>
            </div>
            <div class="px-3 py-2 bg-surface rounded-lg border border-line text-center">
                <span class="text-[11px] text-muted block">Skala Penilaian</span>
                <strong class="text-ink text-sm font-bold">9 — 27</strong>
            </div>
        </div>
    </div>

    {{-- Bagian 3: Rincian 9 Variabel Sosial Ekonomi --}}
    <div class="card space-y-4 !p-0 overflow-hidden">
        <div class="p-6 border-b border-line">
            <h3 class="text-section-title text-ink flex items-center gap-2">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-paper text-brand font-bold text-xs border border-line">3</span>
                Sembilan Variabel Penentu Kerentanan (Tabel 3.2)
            </h3>
            <p class="text-help text-xs mt-1">
                Instrumen kuesioner baku yang dicatat kader lapangan tanpa menampilkan angka skor untuk menjaga objektivitas.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-paper border-b border-line text-xs font-semibold uppercase text-muted">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4 w-48">Variabel</th>
                        <th class="py-3 px-4">Pertanyaan Lapangan</th>
                        <th class="py-3 px-4 w-52">Gradasi Kondisi (Baik → Rentan)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    <tr>
                        <td class="py-3 px-4 text-center font-bold text-muted">1</td>
                        <td class="py-3 px-4 font-bold text-ink">Pendapatan Bulanan</td>
                        <td class="py-3 px-4 text-xs text-muted">Berapa rata-rata total pendapatan keluarga dalam satu bulan?</td>
                        <td class="py-3 px-4 text-xs">
                            <div><span class="text-rendah font-bold">1:</span> ≥ Rp2.500.000 / bln</div>
                            <div><span class="text-sedang font-bold">2:</span> Rp1.000.000 – Rp2.499.999</div>
                            <div><span class="text-tinggi font-bold">3:</span> &lt; Rp1.000.000 / bln</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-center font-bold text-muted">2</td>
                        <td class="py-3 px-4 font-bold text-ink">Pekerjaan Utama</td>
                        <td class="py-3 px-4 text-xs text-muted">Apa pekerjaan atau mata pencaharian utama kepala keluarga?</td>
                        <td class="py-3 px-4 text-xs">
                            <div><span class="text-rendah font-bold">1:</span> PNS/TNI/POLRI/Karyawan tetap</div>
                            <div><span class="text-sedang font-bold">2:</span> Petani/Peternak/Pedagang</div>
                            <div><span class="text-tinggi font-bold">3:</span> Tidak bekerja/Buruh harian</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-center font-bold text-muted">3</td>
                        <td class="py-3 px-4 font-bold text-ink">Tingkat Pendidikan</td>
                        <td class="py-3 px-4 text-xs text-muted">Apa jenjang pendidikan formal tertinggi di dalam rumah tangga ini?</td>
                        <td class="py-3 px-4 text-xs">
                            <div><span class="text-rendah font-bold">1:</span> Perguruan Tinggi (D3/S1/S2)</div>
                            <div><span class="text-sedang font-bold">2:</span> Tamat SMA / sederajat</div>
                            <div><span class="text-tinggi font-bold">3:</span> SD / SMP / Tidak tamat</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-center font-bold text-muted">4</td>
                        <td class="py-3 px-4 font-bold text-ink">Jumlah Anggota Keluarga</td>
                        <td class="py-3 px-4 text-xs text-muted">Berapa jumlah orang yang tinggal dan menjadi tanggungan di rumah ini?</td>
                        <td class="py-3 px-4 text-xs">
                            <div><span class="text-rendah font-bold">1:</span> 1 – 2 orang</div>
                            <div><span class="text-sedang font-bold">2:</span> 3 – 4 orang</div>
                            <div><span class="text-tinggi font-bold">3:</span> Lebih dari 4 orang</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-center font-bold text-muted">5</td>
                        <td class="py-3 px-4 font-bold text-ink">Fisik Bangunan</td>
                        <td class="py-3 px-4 text-xs text-muted">Material dinding dan lantai utama (komposit terpadu).</td>
                        <td class="py-3 px-4 text-xs">
                            <div><span class="text-rendah font-bold">1:</span> Tembok + Keramik/Ubin</div>
                            <div><span class="text-sedang font-bold">2:</span> Kayu/Tembok + Semen</div>
                            <div><span class="text-tinggi font-bold">3:</span> Kayu + Tanah/Papan</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-center font-bold text-muted">6</td>
                        <td class="py-3 px-4 font-bold text-ink">Fasilitas Sanitasi</td>
                        <td class="py-3 px-4 text-xs text-muted">Di mana anggota keluarga biasanya buang air besar?</td>
                        <td class="py-3 px-4 text-xs">
                            <div><span class="text-rendah font-bold">1:</span> MCK pribadi / jamban sehat</div>
                            <div><span class="text-sedang font-bold">2:</span> MCK umum / jamban berbagi</div>
                            <div><span class="text-tinggi font-bold">3:</span> Tidak memiliki MCK</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-center font-bold text-muted">7</td>
                        <td class="py-3 px-4 font-bold text-ink">Sumber Air Bersih</td>
                        <td class="py-3 px-4 text-xs text-muted">Dari mana sumber air utama untuk minum, memasak, dan mencuci?</td>
                        <td class="py-3 px-4 text-xs">
                            <div><span class="text-rendah font-bold">1:</span> PDAM / Ledeng</div>
                            <div><span class="text-sedang font-bold">2:</span> Sumur bor / Air galon</div>
                            <div><span class="text-tinggi font-bold">3:</span> Sumur gali / tadah hujan</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-center font-bold text-muted">8</td>
                        <td class="py-3 px-4 font-bold text-ink">Pendapatan Tambahan</td>
                        <td class="py-3 px-4 text-xs text-muted">Apakah keluarga memiliki sumber pemasukan lain di luar pekerjaan utama?</td>
                        <td class="py-3 px-4 text-xs">
                            <div><span class="text-rendah font-bold">1:</span> Usaha sampingan / warung / kebun</div>
                            <div><span class="text-sedang font-bold">2:</span> Bantuan pemerintah saja</div>
                            <div><span class="text-tinggi font-bold">3:</span> Tidak ada pendapatan lain</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4 text-center font-bold text-muted">9</td>
                        <td class="py-3 px-4 font-bold text-ink">Kepemilikan Lahan</td>
                        <td class="py-3 px-4 text-xs text-muted">Apa status kepemilikan rumah dan lahan yang ditempati saat ini?</td>
                        <td class="py-3 px-4 text-xs">
                            <div><span class="text-rendah font-bold">1:</span> Milik sendiri / warisan keluarga</div>
                            <div><span class="text-sedang font-bold">2:</span> Pinjam pakai / sewa / garapan</div>
                            <div><span class="text-tinggi font-bold">3:</span> Menumpang / tidak punya lahan</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bagian 4: Klasifikasi Equal Interval & Simbologi Peta --}}
    <div class="card space-y-4">
        <h3 class="text-section-title text-ink border-b border-line pb-2 flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-paper text-brand font-bold text-xs border border-line">4</span>
            Metode Klasifikasi Equal Interval &amp; Simbologi Peta
        </h3>
        <p class="text-body text-ink text-xs sm:text-sm leading-relaxed">
            Klasifikasi tingkat kerentanan ditentukan menggunakan metode <em>Equal Interval</em> yang membagi rentang skor keseluruhan menjadi 3 kelas dengan interval lebar yang sama:
        </p>

        {{-- Rumus Interval --}}
        <div class="bg-paper p-3 rounded-lg border border-line font-mono text-xs text-ink flex items-center justify-between">
            <span>Interval (i) = (Skor Maksimum - Skor Minimum) / 3 = (27 - 9) / 3 = <strong>6</strong></span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
            {{-- Kartu Kelas Rendah --}}
            <div class="border border-rendah/30 bg-rendah-bg/20 rounded-xl p-4 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="h-4 w-4 rounded-full bg-rendah border-2 border-white shadow-xs"></span>
                    <h4 class="font-bold text-rendah-tx text-sm">Kerentanan Rendah</h4>
                </div>
                <div class="text-xs space-y-1">
                    <div>Total Skor: <strong class="text-ink">9 – 14</strong></div>
                    <div>Rata-rata Skor: <strong class="text-ink">1,00 – &lt;1,67</strong></div>
                    <div>Penanda Peta: <span class="font-semibold text-rendah-tx">Lingkaran Hijau ●</span></div>
                    <p class="text-[11px] text-muted pt-1">Rumah tangga berkecukupan mandiri dengan risiko kerentanan minimal.</p>
                </div>
            </div>

            {{-- Kartu Kelas Sedang --}}
            <div class="border border-sedang/30 bg-sedang-bg/20 rounded-xl p-4 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-sedang font-bold leading-none text-base">●</span>
                    <h4 class="font-bold text-sedang-tx text-sm">Kerentanan Sedang</h4>
                </div>
                <div class="text-xs space-y-1">
                    <div>Total Skor: <strong class="text-ink">15 – 20</strong></div>
                    <div>Rata-rata Skor: <strong class="text-ink">1,67 – &lt;2,33</strong></div>
                    <div>Penanda Peta: <span class="font-semibold text-sedang-tx">Lingkaran Kuning ●</span></div>
                    <p class="text-[11px] text-muted pt-1">Mayoritas populasi dengan keterbatasan menengah pada salah satu aset dasar.</p>
                </div>
            </div>

            {{-- Kartu Kelas Tinggi --}}
            <div class="border border-tinggi/30 bg-tinggi-bg/20 rounded-xl p-4 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="text-tinggi font-bold leading-none text-base">●</span>
                    <h4 class="font-bold text-tinggi-tx text-sm">Kerentanan Tinggi</h4>
                </div>
                <div class="text-xs space-y-1">
                    <div>Total Skor: <strong class="text-ink">21 – 27</strong></div>
                    <div>Rata-rata Skor: <strong class="text-ink">2,33 – 3,00</strong></div>
                    <div>Penanda Peta: <span class="font-semibold text-tinggi-tx">Lingkaran Merah ●</span></div>
                    <p class="text-[11px] text-muted pt-1">Prioritas penerima intervensi program bantuan sosial kampung dan dinas terkait.</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
