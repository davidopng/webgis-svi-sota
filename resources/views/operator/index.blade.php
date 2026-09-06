@extends('layouts.app', ['role' => 'operator', 'userName' => 'Maria K.'])

@section('title', 'Daftar Data Saya — SOTA-GIS')
@section('page-title', 'Daftar Data Saya')

@section('content')
<div x-data="{
    // State Filter & Cari
    statusFilter: 'semua', // 'semua' | 'menunggu_verifikasi' | 'terverifikasi'
    searchQuery: '',
    toastSukses: false,

    // Data 10 Sampel Milik Operator Maria K.
    dataList: [
        {
            id: 1,
            nama_kk: 'Yohanis Kaize',
            jalur: 'Jalur B',
            alamat: 'RT 03 / RW 01 No. 12',
            skor: 20,
            skor_rata_rata: '2,22',
            klasifikasi: 'sedang',
            status: 'menunggu_verifikasi',
            diinput_pada: '04 Sep 2026, 14:32'
        },
        {
            id: 2,
            nama_kk: 'Kornelis Ndiken',
            jalur: 'Poros Trans',
            alamat: 'RT 09 / RW 03 No. 27',
            skor: 18,
            skor_rata_rata: '2,00',
            klasifikasi: 'sedang',
            status: 'menunggu_verifikasi',
            diinput_pada: '04 Sep 2026, 11:20'
        },
        {
            id: 3,
            nama_kk: 'Albertina Kwipalo',
            jalur: 'Poros Trans',
            alamat: 'RT 08 / RW 02 No. 11',
            skor: 19,
            skor_rata_rata: '2,11',
            klasifikasi: 'sedang',
            status: 'menunggu_verifikasi',
            diinput_pada: '04 Sep 2026, 09:45'
        },
        {
            id: 4,
            nama_kk: 'Siti Rahayu',
            jalur: 'Poros Trans',
            alamat: 'RT 07 / RW 02 No. 18',
            skor: 13,
            skor_rata_rata: '1,44',
            klasifikasi: 'rendah',
            status: 'terverifikasi',
            diinput_pada: '03 Sep 2026, 16:15'
        },
        {
            id: 5,
            nama_kk: 'Petrus Gebze',
            jalur: 'Jalur A',
            alamat: 'RT 01 / RW 01 No. 04',
            skor: 23,
            skor_rata_rata: '2,56',
            klasifikasi: 'tinggi',
            status: 'terverifikasi',
            diinput_pada: '03 Sep 2026, 14:10'
        },
        {
            id: 6,
            nama_kk: 'Agustina Mahuze',
            jalur: 'Jalur B',
            alamat: 'RT 02 / RW 01 No. 15',
            skor: 11,
            skor_rata_rata: '1,22',
            klasifikasi: 'rendah',
            status: 'terverifikasi',
            diinput_pada: '03 Sep 2026, 10:30'
        },
        {
            id: 7,
            nama_kk: 'Markus Balagaize',
            jalur: 'Jalur B',
            alamat: 'RT 04 / RW 02 No. 08',
            skor: 22,
            skor_rata_rata: '2,44',
            klasifikasi: 'tinggi',
            status: 'terverifikasi',
            diinput_pada: '02 Sep 2026, 15:40'
        },
        {
            id: 8,
            nama_kk: 'Barnabas Kaize',
            jalur: 'Jalur B',
            alamat: 'RT 03 / RW 01 No. 22',
            skor: 16,
            skor_rata_rata: '1,78',
            klasifikasi: 'sedang',
            status: 'terverifikasi',
            diinput_pada: '02 Sep 2026, 13:10'
        },
        {
            id: 9,
            nama_kk: 'Yakobus Gebze',
            jalur: 'Poros Trans',
            alamat: 'RT 06 / RW 02 No. 19',
            skor: 14,
            skor_rata_rata: '1,56',
            klasifikasi: 'rendah',
            status: 'terverifikasi',
            diinput_pada: '01 Sep 2026, 16:00'
        },
        {
            id: 10,
            nama_kk: 'Paulina Mahuze',
            jalur: 'Poros Trans',
            alamat: 'RT 10 / RW 03 No. 31',
            skor: 17,
            skor_rata_rata: '1,89',
            klasifikasi: 'sedang',
            status: 'terverifikasi',
            diinput_pada: '01 Sep 2026, 11:20'
        }
    ],

    init() {
        // Cek jika ada query parameter status
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status')) {
            this.statusFilter = urlParams.get('status');
        }
    },

    get filteredData() {
        return this.dataList.filter(item => {
            const matchStatus = this.statusFilter === 'semua' || item.status === this.statusFilter;
            const matchSearch = item.nama_kk.toLowerCase().includes(this.searchQuery.toLowerCase().trim()) ||
                                item.alamat.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
            return matchStatus && matchSearch;
        });
    }
}" class="space-y-6">

    {{-- Header & Tombol Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-ink">Data Hasil Survei Anda</h2>
            <p class="text-help mt-0.5">Daftar rumah tangga yang Anda data di Jalur A, Jalur B, dan Poros Trans.</p>
        </div>
        <a href="/operator/data/create" class="btn-primary self-start sm:self-auto font-bold shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>＋ Input Data Warga Baru</span>
        </a>
    </div>

    {{-- Filter Tab & Kotak Pencarian --}}
    <div class="card p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        {{-- Tabs Filter Status --}}
        <div class="flex flex-wrap items-center gap-2">
            <button type="button"
                    @click="statusFilter = 'semua'"
                    :class="statusFilter === 'semua' ? 'bg-brand text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                    class="rounded-md px-3 py-1.5 text-xs transition-colors">
                Semua (27)
            </button>
            <button type="button"
                    @click="statusFilter = 'menunggu_verifikasi'"
                    :class="statusFilter === 'menunggu_verifikasi' ? 'bg-sedang text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                    class="rounded-md px-3 py-1.5 text-xs transition-colors">
                Menunggu Verifikasi (3)
            </button>
            <button type="button"
                    @click="statusFilter = 'terverifikasi'"
                    :class="statusFilter === 'terverifikasi' ? 'bg-rendah text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                    class="rounded-md px-3 py-1.5 text-xs transition-colors">
                Terverifikasi (24)
            </button>
        </div>

        {{-- Input Pencarian Nama KK --}}
        <div class="w-full sm:w-64 relative">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text"
                   x-model="searchQuery"
                   class="input-text pl-9 !min-h-[38px] !py-1 text-xs"
                   placeholder="Cari nama kepala keluarga…">
        </div>
    </div>

    {{-- ============================================================
         TABEL DESKTOP & TABLET (≥640px) — PRD 10.3
         ============================================================ --}}
    <div class="card overflow-hidden !p-0 hidden sm:block shadow-sm">
        <template x-if="filteredData.length > 0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-paper border-b border-line text-xs font-semibold uppercase tracking-wider text-muted">
                        <tr>
                            <th class="py-3.5 px-4">Nama Kepala Keluarga</th>
                            <th class="py-3.5 px-4">Jalur / Wilayah</th>
                            <th class="py-3.5 px-4 text-center">Total Skor</th>
                            <th class="py-3.5 px-4">Klasifikasi</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-ink">
                        <template x-for="item in filteredData" :key="item.id">
                            <tr class="hover:bg-paper/40 transition-colors"
                                :class="item.status === 'menunggu_verifikasi' ? 'border-l-4 border-l-sedang' : ''">
                                {{-- Nama KK & Alamat --}}
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-ink" x-text="item.nama_kk"></p>
                                    <p class="text-xs text-muted" x-text="item.alamat"></p>
                                </td>

                                {{-- Jalur --}}
                                <td class="py-3.5 px-4 text-xs font-medium" x-text="item.jalur"></td>

                                {{-- Total Skor (Tabular Nums) --}}
                                <td class="py-3.5 px-4 text-center font-bold tabular-nums" x-text="item.skor"></td>

                                {{-- Klasifikasi Badge --}}
                                <td class="py-3.5 px-4">
                                    <template x-if="item.klasifikasi === 'tinggi'">
                                        <x-badge-klasifikasi tingkat="tinggi" />
                                    </template>
                                    <template x-if="item.klasifikasi === 'sedang'">
                                        <x-badge-klasifikasi tingkat="sedang" />
                                    </template>
                                    <template x-if="item.klasifikasi === 'rendah'">
                                        <x-badge-klasifikasi tingkat="rendah" />
                                    </template>
                                </td>

                                {{-- Status Verifikasi --}}
                                <td class="py-3.5 px-4">
                                    <template x-if="item.status === 'menunggu_verifikasi'">
                                        <x-badge-status status="menunggu_verifikasi" />
                                    </template>
                                    <template x-if="item.status === 'terverifikasi'">
                                        <x-badge-status status="terverifikasi" />
                                    </template>
                                </td>

                                {{-- Aksi: Ubah HANYA jika Menunggu Verifikasi (PRD 10.3) --}}
                                <td class="py-3.5 px-4 text-right">
                                    <template x-if="item.status === 'menunggu_verifikasi'">
                                        <a :href="'/operator/data/' + item.id + '/edit'"
                                           class="btn-secondary !min-h-[34px] !py-1 !px-3 !text-xs font-semibold hover:border-brand hover:text-brand">
                                            Ubah
                                        </a>
                                    </template>
                                    <template x-if="item.status === 'terverifikasi'">
                                        <div class="inline-flex flex-col items-end">
                                            <a :href="'/operator/data/' + item.id + '/edit'"
                                               class="text-xs font-bold text-muted hover:text-ink underline">
                                                Lihat
                                            </a>
                                            <span class="text-[10px] text-muted whitespace-nowrap">
                                                Sudah diverifikasi
                                            </span>
                                        </div>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        {{-- Empty State Bila Pencarian / Filter Kosong --}}
        <template x-if="filteredData.length === 0">
            <x-empty-state
                title="Tidak ada data ditemukan"
                description="Tidak ada data warga yang sesuai dengan kata kunci pencarian atau filter status yang dipilih."
                action-text="Atur Ulang Filter"
                action-url="/operator/data"
                icon="search"
            />
        </template>

        {{-- Keterangan Footer Tabel --}}
        <div class="p-4 border-t border-line text-xs text-muted flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <span>Menampilkan <strong class="text-ink" x-text="filteredData.length"></strong> data warga</span>
            <span>* Baris dengan garis kuning di sebelah kiri menandakan status <strong>Menunggu Verifikasi</strong>.</span>
        </div>
    </div>

    {{-- ============================================================
         KARTU BERTUMPUK KHUSUS MOBILE (<640px) — PRD 10.3 & 14
         ============================================================ --}}
    <div class="space-y-3 sm:hidden">
        <template x-for="item in filteredData" :key="item.id">
            <div class="card space-y-3 p-4 shadow-sm"
                 :class="item.status === 'menunggu_verifikasi' ? 'border-l-4 border-l-sedang' : ''">
                {{-- Header Kartu: Nama KK & Status --}}
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h4 class="font-bold text-ink text-sm" x-text="item.nama_kk"></h4>
                        <p class="text-help text-xs" x-text="item.jalur + ' · ' + item.alamat"></p>
                    </div>
                    <template x-if="item.status === 'menunggu_verifikasi'">
                        <x-badge-status status="menunggu_verifikasi" />
                    </template>
                    <template x-if="item.status === 'terverifikasi'">
                        <x-badge-status status="terverifikasi" />
                    </template>
                </div>

                {{-- Detail Skor & Klasifikasi --}}
                <div class="flex items-center justify-between border-t border-line pt-2.5 text-xs">
                    <div class="flex items-center gap-2">
                        <template x-if="item.klasifikasi === 'tinggi'">
                            <x-badge-klasifikasi tingkat="tinggi" />
                        </template>
                        <template x-if="item.klasifikasi === 'sedang'">
                            <x-badge-klasifikasi tingkat="sedang" />
                        </template>
                        <template x-if="item.klasifikasi === 'rendah'">
                            <x-badge-klasifikasi tingkat="rendah" />
                        </template>
                        <span class="text-muted">Skor: <strong class="text-ink tabular-nums" x-text="item.skor"></strong></span>
                    </div>

                    {{-- Tombol Aksi Mobile --}}
                    <div>
                        <template x-if="item.status === 'menunggu_verifikasi'">
                            <a :href="'/operator/data/' + item.id + '/edit'"
                               class="btn-secondary !min-h-[36px] !py-1 !px-3.5 !text-xs font-semibold">
                                Ubah Data
                            </a>
                        </template>
                        <template x-if="item.status === 'terverifikasi'">
                            <span class="text-[11px] text-muted italic">
                                Terverifikasi Admin
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        {{-- Empty State Mobile --}}
        <template x-if="filteredData.length === 0">
            <x-empty-state
                title="Tidak ada data ditemukan"
                description="Coba ubah kata kunci pencarian atau filter status."
                action-text="Atur Ulang"
                action-url="/operator/data"
                icon="search"
            />
        </template>
    </div>
</div>
@endsection
