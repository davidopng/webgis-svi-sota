@extends('layouts.app', [
    'role' => 'admin',
    'userName' => 'Administrator'
])

@section('title', 'Semua Data Warga — SOTA-GIS')
@section('page-title', 'Semua Data Warga')

@section('content')
<div x-data="{
    filterWilayah: 'semua',
    filterStatus: 'semua',
    searchQuery: '',
    page: 1,
    perPage: 10,

    // Modal Hapus State
    modalHapus: false,
    selectedItem: null,
    konfirmasiNama: '',

    openModalHapus(item) {
        this.selectedItem = item;
        this.konfirmasiNama = '';
        this.modalHapus = true;
    },

    eksekusiHapus() {
        if (!this.selectedItem || this.konfirmasiNama !== this.selectedItem.nama) return;
        const targetId = this.selectedItem.id;
        this.dataWarga = this.dataWarga.filter(d => d.id !== targetId);
        this.modalHapus = false;
        alert('Data ' + this.selectedItem.nama + ' berhasil dihapus.');
    },

    // 15 Responden SOTA-GIS
    dataWarga: [
        { id: 1, nama: 'Petrus Gebze', jalur: 'Jalur A', alamat: 'RT 01 / RW 01 No. 04', skor: 23, klas: 'tinggi', status: 'terverifikasi', operator: 'Yosep M.' },
        { id: 2, nama: 'Fransiskus Samkakai', jalur: 'Jalur A', alamat: 'RT 02 / RW 01 No. 09', skor: 25, klas: 'tinggi', status: 'terverifikasi', operator: 'Maria K.' },
        { id: 3, nama: 'Dominikus Mejai', jalur: 'Jalur A', alamat: 'RT 01 / RW 01 No. 07', skor: 24, klas: 'tinggi', status: 'terverifikasi', operator: 'Yosep M.' },
        { id: 4, nama: 'Kornelis Basik-Basik', jalur: 'Jalur A', alamat: 'RT 03 / RW 01 No. 15', skor: 19, klas: 'sedang', status: 'terverifikasi', operator: 'Yosep M.' },
        { id: 5, nama: 'Yosep Mahuze', jalur: 'Jalur A', alamat: 'RT 02 / RW 01 No. 02', skor: 18, klas: 'sedang', status: 'terverifikasi', operator: 'Maria K.' },
        { id: 6, nama: 'Yohanis Kaize', jalur: 'Jalur B', alamat: 'RT 03 / RW 01 No. 12', skor: 20, klas: 'sedang', status: 'menunggu_verifikasi', operator: 'Maria K.' },
        { id: 7, nama: 'Agustina Mahuze', jalur: 'Jalur B', alamat: 'RT 05 / RW 02 No. 08', skor: 16, klas: 'sedang', status: 'terverifikasi', operator: 'Yosep M.' },
        { id: 8, nama: 'Markus Balagaize', jalur: 'Jalur B', alamat: 'RT 04 / RW 02 No. 18', skor: 22, klas: 'tinggi', status: 'terverifikasi', operator: 'Maria K.' },
        { id: 9, nama: 'Bonaventura Ndiken', jalur: 'Jalur B', alamat: 'RT 06 / RW 02 No. 03', skor: 14, klas: 'rendah', status: 'terverifikasi', operator: 'Yosep M.' },
        { id: 10, nama: 'Simon Kaize', jalur: 'Jalur B', alamat: 'RT 06 / RW 02 No. 14', skor: 13, klas: 'rendah', status: 'terverifikasi', operator: 'Maria K.' },
        { id: 11, nama: 'Siti Rahayu', jalur: 'Poros Trans', alamat: 'RT 07 / RW 02 No. 05', skor: 13, klas: 'rendah', status: 'menunggu_verifikasi', operator: 'Maria K.' },
        { id: 12, nama: 'Albertina Kwipalo', jalur: 'Poros Trans', alamat: 'RT 08 / RW 02 No. 11', skor: 19, klas: 'sedang', status: 'terverifikasi', operator: 'Yosep M.' },
        { id: 13, nama: 'Kornelis Ndiken', jalur: 'Poros Trans', alamat: 'RT 09 / RW 03 No. 27', skor: 18, klas: 'sedang', status: 'menunggu_verifikasi', operator: 'Maria K.' },
        { id: 14, nama: 'Theresia Yolmen', jalur: 'Poros Trans', alamat: 'RT 11 / RW 03 No. 02', skor: 21, klas: 'tinggi', status: 'terverifikasi', operator: 'Yosep M.' },
        { id: 15, nama: 'Paulus Mahuze', jalur: 'Poros Trans', alamat: 'RT 12 / RW 03 No. 19', skor: 17, klas: 'sedang', status: 'terverifikasi', operator: 'Maria K.' }
    ],

    get filteredData() {
        return this.dataWarga.filter(d => {
            const matchWilayah = this.filterWilayah === 'semua' || d.jalur === this.filterWilayah;
            const matchStatus = this.filterStatus === 'semua' || d.status === this.filterStatus;
            const matchQuery = !this.searchQuery ||
                d.nama.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                d.alamat.toLowerCase().includes(this.searchQuery.toLowerCase());

            return matchWilayah && matchStatus && matchQuery;
        });
    },

    resetFilter() {
        this.filterWilayah = 'semua';
        this.filterStatus = 'semua';
        this.searchQuery = '';
    }
}" class="space-y-6">

    {{-- Header Halaman & Aksi Ekspor / Tambah (PRD 11.4) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-line">
        <div>
            <h2 class="text-xl font-bold text-ink">Semua Data Warga Terdata</h2>
            <p class="text-help text-xs mt-0.5">
                Basis data terpadu seluruh responden survei kerentanan sosial ekonomi di Kampung Sota (Jalur A, Jalur B, dan Poros Trans).
            </p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Tombol Ekspor Laporan (Admin berhak ekspor - PRD 11.4) --}}
            <a href="/pimpinan/laporan" class="btn-secondary !min-h-[40px] !text-xs font-semibold flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Ekspor Laporan</span>
            </a>
            {{-- Tombol Tambah Data Warga (PRD 11.4) --}}
            <a href="/operator/data/create" class="btn-primary !min-h-[40px] !text-xs font-bold flex items-center gap-1.5 shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>＋ Tambah Data Warga</span>
            </a>
        </div>
    </div>

    {{-- Filter Bar Interaktif (Wilayah, Status & Pencarian) --}}
    <div class="card p-4 space-y-3">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            {{-- Filter Wilayah --}}
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <span class="text-muted font-semibold mr-1">Wilayah:</span>
                <button type="button"
                        @click="filterWilayah = 'semua'"
                        :class="filterWilayah === 'semua' ? 'bg-brand text-white font-bold' : 'bg-paper text-muted hover:text-ink border border-line'"
                        class="px-3 py-1.5 rounded-lg transition-colors">
                    Semua
                </button>
                <button type="button"
                        @click="filterWilayah = 'Jalur A'"
                        :class="filterWilayah === 'Jalur A' ? 'bg-brand text-white font-bold' : 'bg-paper text-muted hover:text-ink border border-line'"
                        class="px-3 py-1.5 rounded-lg transition-colors">
                    Jalur A
                </button>
                <button type="button"
                        @click="filterWilayah = 'Jalur B'"
                        :class="filterWilayah === 'Jalur B' ? 'bg-brand text-white font-bold' : 'bg-paper text-muted hover:text-ink border border-line'"
                        class="px-3 py-1.5 rounded-lg transition-colors">
                    Jalur B
                </button>
                <button type="button"
                        @click="filterWilayah = 'Poros Trans'"
                        :class="filterWilayah === 'Poros Trans' ? 'bg-brand text-white font-bold' : 'bg-paper text-muted hover:text-ink border border-line'"
                        class="px-3 py-1.5 rounded-lg transition-colors">
                    Poros Trans
                </button>
            </div>

            {{-- Filter Status & Kotak Cari --}}
            <div class="flex flex-wrap items-center gap-3">
                <select x-model="filterStatus" class="input-select !min-h-[38px] !py-1 text-xs font-medium w-full sm:w-44">
                    <option value="semua">Semua Status</option>
                    <option value="terverifikasi">Terverifikasi</option>
                    <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                </select>

                <div class="w-full sm:w-56">
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Cari nama kepala keluarga..."
                           class="input-text !min-h-[38px] !py-1 text-xs">
                </div>

                <button type="button"
                        x-show="filterWilayah !== 'semua' || filterStatus !== 'semua' || searchQuery !== ''"
                        @click="resetFilter"
                        class="text-xs text-tinggi font-bold hover:underline py-1 px-2">
                    Reset
                </button>
            </div>
        </div>
    </div>

    {{-- Tabel Semua Data --}}
    <div class="card overflow-hidden !p-0 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-paper border-b border-line text-xs font-semibold uppercase tracking-wider text-muted">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Kepala Keluarga</th>
                        <th class="py-3.5 px-4">Jalur / Wilayah</th>
                        <th class="py-3.5 px-4">Alamat Detail</th>
                        <th class="py-3.5 px-4 text-center">Total Skor</th>
                        <th class="py-3.5 px-4">Klasifikasi</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    <template x-for="(item, idx) in filteredData" :key="item.id">
                        <tr class="hover:bg-paper/40 transition-colors"
                            :class="item.status === 'menunggu_verifikasi' ? 'border-l-[3px] border-l-sedang bg-amber-50/20' : ''">
                            <td class="py-3.5 px-4 text-center text-xs text-muted tabular-nums font-mono" x-text="idx + 1"></td>
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-ink text-xs" x-text="item.nama"></p>
                                <span class="text-[11px] text-muted" x-text="'Diinput oleh: ' + item.operator"></span>
                            </td>
                            <td class="py-3.5 px-4 text-xs font-medium" x-text="item.jalur"></td>
                            <td class="py-3.5 px-4 text-xs text-muted" x-text="item.alamat"></td>
                            <td class="py-3.5 px-4 text-center font-bold tabular-nums text-xs" x-text="item.skor"></td>
                            <td class="py-3.5 px-4">
                                <template x-if="item.klas === 'tinggi'">
                                    <x-badge-klasifikasi tingkat="tinggi" />
                                </template>
                                <template x-if="item.klas === 'sedang'">
                                    <x-badge-klasifikasi tingkat="sedang" />
                                </template>
                                <template x-if="item.klas === 'rendah'">
                                    <x-badge-klasifikasi tingkat="rendah" />
                                </template>
                            </td>
                            <td class="py-3.5 px-4">
                                <template x-if="item.status === 'terverifikasi'">
                                    <x-badge-status status="terverifikasi" />
                                </template>
                                <template x-if="item.status === 'menunggu_verifikasi'">
                                    <x-badge-status status="menunggu_verifikasi" />
                                </template>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2 text-xs">
                                    <template x-if="item.status === 'menunggu_verifikasi'">
                                        <a href="/admin/verifikasi" class="btn-primary !min-h-[30px] !py-1 !px-2.5 !text-[11px] font-bold">
                                            Periksa
                                        </a>
                                    </template>
                                    <a :href="'/admin/data/' + item.id + '/edit'" class="p-1 text-muted hover:text-brand" title="Ubah data">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </a>
                                    <button type="button" @click="openModalHapus(item)" class="p-1 text-muted hover:text-tinggi" title="Hapus data">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Keadaan Kosong (Empty State saat Pencarian / Filter Tidak Menemukan Hasil) --}}
        <div x-show="filteredData.length === 0" class="p-8 text-center" style="display: none;">
            <x-empty-state
                icon="search"
                title="Tidak ada data yang cocok"
                description="Tidak ditemukan data warga pada filter atau kata kunci pencarian yang Anda masukkan."
            />
            <div class="mt-2">
                <button type="button" @click="resetFilter" class="btn-secondary !text-xs !min-h-[38px]">
                    Atur Ulang Pencarian &amp; Filter
                </button>
            </div>
        </div>

        {{-- Footer Paginasi --}}
        <div class="p-4 border-t border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs text-muted">
            <span>
                Menampilkan <strong class="text-ink" x-text="filteredData.length"></strong> dari <strong class="text-ink" x-text="dataWarga.length"></strong> total data warga
            </span>
            <div class="flex items-center gap-1 self-end sm:self-auto">
                <button class="px-2.5 py-1 rounded bg-paper border border-line text-muted cursor-not-allowed" disabled>‹</button>
                <button class="px-2.5 py-1 rounded bg-brand text-white font-bold">1</button>
                <button class="px-2.5 py-1 rounded bg-paper border border-line text-muted cursor-not-allowed" disabled>›</button>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL KONFIRMASI HAPUS PERMANEN (TYPE-TO-CONFIRM) (PRD 11.3)
         ============================================================ --}}
    <div x-show="modalHapus"
         x-transition.opacity
         class="fixed inset-0 z-50 bg-ink/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;"
         x-cloak
         @keydown.escape.window="modalHapus = false">

        <div @click.outside="modalHapus = false"
             class="bg-surface rounded-xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-tinggi/30">
            <div class="flex items-center gap-3 text-tinggi">
                <div class="h-10 w-10 rounded-full bg-tinggi-bg flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-ink">Konfirmasi Hapus Data</h3>
                    <p class="text-xs text-muted">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
                </div>
            </div>

            <p class="text-xs text-ink leading-relaxed">
                Anda akan menghapus berkas data survei atas nama:
                <strong class="text-tinggi block text-sm mt-0.5" x-text="selectedItem ? selectedItem.nama : ''"></strong>
            </p>

            <div class="space-y-1.5 pt-1">
                <label class="block text-xs font-semibold text-muted">
                    Ketik nama kepala keluarga di bawah untuk mengonfirmasi:
                </label>
                <input type="text"
                       x-model="konfirmasiNama"
                       :placeholder="selectedItem ? selectedItem.nama : ''"
                       class="input-text !min-h-[40px] text-xs font-medium">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-line">
                <button type="button" @click="modalHapus = false" class="btn-secondary !text-xs !min-h-[38px]">
                    Batal
                </button>
                <button type="button"
                        @click="eksekusiHapus"
                        :disabled="!selectedItem || konfirmasiNama !== selectedItem.nama"
                        class="btn-danger !text-xs !min-h-[38px] px-4 font-bold disabled:opacity-40 disabled:cursor-not-allowed">
                    Hapus Permanen
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
