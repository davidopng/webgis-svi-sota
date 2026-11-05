@extends('layouts.app', [
    'role' => 'admin',
    'userName' => Auth::user()?->name ?? 'Jhoni Arafat'
])

@section('title', 'Semua Data Warga — SOTA-GIS')
@section('page-title', 'Semua Data Warga')

@php
    $dataWargaJson = json_encode(($dataWarga ?? collect([]))->map(fn($item) => [
        'id' => $item->id,
        'nama' => $item->nama_kk,
        'nama_kk' => $item->nama_kk,
        'jalur' => $item->jalur,
        'alamat' => $item->alamat ?? (($item->rt ?? '') . ' / ' . ($item->rw ?? '')),
        'patokan' => $item->patokan ?? '',
        'skor' => $item->total_skor,
        'skor_rata_rata' => number_format($item->total_skor / 9, 2, ',', '.'),
        'klas' => $item->klasifikasi,
        'klasifikasi' => $item->klasifikasi,
        'status' => $item->status_verifikasi,
        'operator' => $item->nama_penginput,
        'waktu_ringkas' => $item->created_at ? $item->created_at->diffForHumans() : '-',
        'latitude' => (float)$item->latitude,
        'longitude' => (float)$item->longitude,
        'dinding' => $item->jenis_dinding ?? '-',
        'lantai' => $item->jenis_lantai ?? '-',
        'foto_rumah' => $item->foto_rumah,
        'pendapatan' => $item->pendapatan ?? '-',
        'pekerjaan' => $item->pekerjaan ?? '-',
        'pendidikan' => $item->pendidikan ?? '-',
        'jumlah_anggota_keluarga' => $item->jumlah_anggota_keluarga ?? 4,
        'fasilitas_sanitasi' => $item->fasilitas_sanitasi ?? '-',
        'sumber_air' => $item->sumber_air ?? '-',
        'pendapatan_tambahan' => $item->pendapatan_tambahan ?? '-',
        'kepemilikan_lahan' => $item->kepemilikan_lahan ?? '-',
        'svi_items' => $item->svi_items,
    ])->values());
@endphp

@section('content')
<script>
    function adminDataWarga() {
        const rawList = {!! $dataWargaJson !!};
        return {
            statusFilter: 'semua',
            jalurFilter: 'semua',
            klasifikasiFilter: 'semua',
            searchQuery: '',
            selectedItem: null,
            detailItem: null,
            modalDetail: false,
            modalHapus: false,
            modalFotoZoom: false,
            fotoZoomSrc: '',
            fotoZoom: false,
            fotoRotation: 0,
            konfirmasiNama: '',
            dataWarga: rawList,

            openModalDetail(item) {
                this.detailItem = item;
                this.fotoZoom = false;
                this.fotoRotation = 0;
                this.modalDetail = true;
            },
            bukaDetail(item) {
                this.openModalDetail(item);
            },
            openModalHapus(item) {
                this.selectedItem = item;
                this.konfirmasiNama = '';
                this.modalHapus = true;
            },
            konfirmasiHapus(item) {
                this.openModalHapus(item);
            },
            async eksekusiHapus() {
                if (!this.selectedItem) return;
                const target = this.selectedItem;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                try {
                    const res = await fetch('/admin/data/' + target.id, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'Gagal menghapus data.');
                    }

                    this.dataWarga = this.dataWarga.filter(d => d.id !== target.id);
                    this.modalHapus = false;
                    if (window.sotaToast) { window.sotaToast('Data berhasil dihapus.', 'berhasil'); } else { alert('Data berhasil dihapus.'); }
                } catch (err) {
                    console.error('Delete error:', err);
                    if (window.sotaToast) { window.sotaToast('Data gagal dihapus. Silakan coba lagi.', 'gagal'); } else { alert('Data gagal dihapus. Silakan coba lagi.'); }
                }
            },
            async verifikasiDariDetail(item) {
                if (!item) return;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                try {
                    const res = await fetch('/admin/data/' + item.id + '/verifikasi', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'Gagal memverifikasi data.');
                    }

                    item.status = 'terverifikasi';
                    this.modalDetail = false;
                    if (window.sotaToast) { window.sotaToast('Data berhasil diverifikasi.', 'berhasil'); } else { alert('Data berhasil diverifikasi.'); }
                } catch (err) {
                    console.error('Verifikasi error:', err);
                    if (window.sotaToast) { window.sotaToast('Data gagal disimpan. Silakan coba lagi.', 'gagal'); } else { alert('Data gagal disimpan. Silakan coba lagi.'); }
                }
            },
            getDetailSviItems(item) {
                if (!item) return [];
                return item.svi_items && item.svi_items.length > 0 ? item.svi_items : [
                    { no: 1, label: 'Pendapatan', jawaban: item.pendapatan || '-', skor: 2 },
                    { no: 2, label: 'Pekerjaan', jawaban: item.pekerjaan || '-', skor: 2 },
                    { no: 3, label: 'Pendidikan', jawaban: item.pendidikan || '-', skor: 2 },
                    { no: 4, label: 'Anggota keluarga', jawaban: (item.jumlah_anggota_keluarga || 4) + ' orang', skor: 2 },
                    { no: 5, label: 'Fisik bangunan', jawaban: (item.dinding || '-') + ' + ' + (item.lantai || '-'), skor: 2 },
                    { no: 6, label: 'Fasilitas sanitasi', jawaban: item.fasilitas_sanitasi || '-', skor: 2 },
                    { no: 7, label: 'Sumber air', jawaban: item.sumber_air || '-', skor: 2 },
                    { no: 8, label: 'Pendapatan tambahan', jawaban: item.pendapatan_tambahan || '-', skor: 2 },
                    { no: 9, label: 'Kepemilikan lahan', jawaban: item.kepemilikan_lahan || '-', skor: 2 },
                ];
            },
            get filteredData() {
                return this.dataWarga.filter(d => {
                    const matchStatus = this.statusFilter === 'semua' || d.status === this.statusFilter;
                    const matchJalur = this.jalurFilter === 'semua' ||
                        d.jalur === this.jalurFilter ||
                        (this.jalurFilter === 'Jalur A' && (d.jalur === 'Jalur A' || (d.jalur && d.jalur.includes('A')))) ||
                        (this.jalurFilter === 'Jalur B' && (d.jalur === 'Jalur B' || (d.jalur && d.jalur.includes('B')))) ||
                        (this.jalurFilter === 'Poros Trans' && (d.jalur && d.jalur.includes('Poros'))) ||
                        (this.jalurFilter === 'Poros Sota PLBN' && (d.jalur && d.jalur.includes('PLBN')));
                    const matchKlas = this.klasifikasiFilter === 'semua' || d.klas === this.klasifikasiFilter || d.klasifikasi === this.klasifikasiFilter;
                    const query = this.searchQuery ? this.searchQuery.toLowerCase().trim() : '';
                    const matchQuery = !query ||
                        (d.nama && d.nama.toLowerCase().includes(query)) ||
                        (d.nama_kk && d.nama_kk.toLowerCase().includes(query)) ||
                        (d.alamat && d.alamat.toLowerCase().includes(query));

                    return matchStatus && matchJalur && matchKlas && matchQuery;
                });
            },
            // Paginasi
            perPage: 10,
            currentPage: 1,

            get totalPages() {
                return Math.max(1, Math.ceil(this.filteredData.length / this.perPage));
            },

            get paginatedData() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredData.slice(start, start + this.perPage);
            },

            get pageNumbers() {
                const pages = [];
                const total = this.totalPages;
                const current = this.currentPage;
                for (let i = 1; i <= total; i++) {
                    if (i === 1 || i === total || (i >= current - 1 && i <= current + 1)) {
                        pages.push(i);
                    } else if (pages[pages.length - 1] !== '...') {
                        pages.push('...');
                    }
                }
                return pages;
            },

            goToPage(p) {
                if (p === '...') return;
                if (p >= 1 && p <= this.totalPages) {
                    this.currentPage = p;
                }
            },

            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                }
            },

            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            },

            resetFilter() {
                this.statusFilter = 'semua';
                this.jalurFilter = 'semua';
                this.klasifikasiFilter = 'semua';
                this.searchQuery = '';
                this.currentPage = 1;
            }
        };
    }

    if (window.Alpine) {
        Alpine.data('adminDataWarga', adminDataWarga);
    } else {
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminDataWarga', adminDataWarga);
        });
    }
</script>

<div x-data="adminDataWarga()" class="space-y-6">

    {{-- Header Halaman & Aksi Ekspor / Tambah (PRD 11.4) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-line">
        <div>
            <h2 class="text-xl font-bold text-ink">Semua Data Warga Terdata</h2>
            <p class="text-help text-xs mt-0.5">
                Basis data terpadu seluruh responden survei kerentanan sosial ekonomi di Kampung Sota (Jalur A, Jalur B, dan Poros Trans).
            </p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Tombol Tambah Data Warga --}}
            <a href="/admin/data/create" class="btn-primary !min-h-[40px] !text-xs font-bold flex items-center gap-1.5 shadow-xs">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Data Warga</span>
            </a>
        </div>
    </div>

    {{-- Filter Bar Lengkap (Sesuai Referensi Antrean Verifikasi) --}}
    <div class="card p-4 space-y-3">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            {{-- Tabs Filter Status --}}
            <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                        @click="statusFilter = 'semua'; currentPage = 1"
                        :class="statusFilter === 'semua' ? 'bg-brand text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                        class="rounded-md px-3 py-1.5 text-xs transition-colors">
                    Semua (<span x-text="dataWarga.length"></span>)
                </button>
                <button type="button"
                        @click="statusFilter = 'menunggu_verifikasi'; currentPage = 1"
                        :class="statusFilter === 'menunggu_verifikasi' ? 'bg-sedang text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                        class="rounded-md px-3 py-1.5 text-xs transition-colors">
                    Menunggu Verifikasi (<span x-text="dataWarga.filter(x => x.status === 'menunggu_verifikasi').length"></span>)
                </button>
                <button type="button"
                        @click="statusFilter = 'terverifikasi'; currentPage = 1"
                        :class="statusFilter === 'terverifikasi' ? 'bg-rendah text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                        class="rounded-md px-3 py-1.5 text-xs transition-colors">
                    Terverifikasi (<span x-text="dataWarga.filter(x => x.status === 'terverifikasi').length"></span>)
                </button>
            </div>

            {{-- Input Cari Nama KK --}}
            <div class="w-full md:w-72 relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text"
                       x-model="searchQuery"
                       @input="currentPage = 1"
                       class="input-text pl-9 !min-h-[38px] !py-1 text-xs"
                       placeholder="Cari nama kepala keluarga…">
            </div>
        </div>

        {{-- Dropdown Filter Jalur & Klasifikasi --}}
        <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-line text-xs">
            <div class="flex items-center gap-2">
                <span class="text-muted font-medium">Jalur:</span>
                <select x-model="jalurFilter" @change="currentPage = 1" class="input-select !min-h-[32px] !py-1 !text-xs !w-auto">
                    <option value="semua">Semua Wilayah</option>
                    <option value="Jalur A">Jalur A</option>
                    <option value="Jalur B">Jalur B</option>
                    <option value="Poros Trans">Poros Trans</option>
                    <option value="Poros Sota PLBN">Poros Sota PLBN</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-muted font-medium">Klasifikasi:</span>
                <select x-model="klasifikasiFilter" @change="currentPage = 1" class="input-select !min-h-[32px] !py-1 !text-xs !w-auto">
                    <option value="semua">Semua Tingkat</option>
                    <option value="rendah">Kerentanan Rendah</option>
                    <option value="sedang">Kerentanan Sedang</option>
                    <option value="tinggi">Kerentanan Tinggi</option>
                </select>
            </div>

            <button type="button"
                    @click="resetFilter"
                    class="text-brand font-semibold hover:underline ml-auto">
                Atur ulang filter
            </button>
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
                    <template x-for="(item, idx) in paginatedData" :key="item.id">
                        <tr class="hover:bg-paper/40 transition-colors"
                            :class="item.status === 'menunggu_verifikasi' ? 'border-l-[3px] border-l-sedang bg-amber-50/20' : ''">
                            <td class="py-3.5 px-4 text-center text-xs text-muted tabular-nums font-mono" x-text="(currentPage - 1) * perPage + idx + 1"></td>
                            <td class="py-3.5 px-4">
                                <p class="font-bold text-ink text-xs" x-text="item.nama"></p>
                                <span class="text-[11px] text-muted" x-text="'Diinput oleh: ' + item.operator"></span>
                            </td>
                            <td class="py-3.5 px-4 text-xs font-medium" x-text="item.jalur"></td>
                            <td class="py-3.5 px-4 text-xs text-muted">
                                <span class="text-ink font-medium" x-text="item.alamat"></span>
                                <template x-if="item.patokan">
                                    <span class="block text-[11px] text-muted italic mt-0.5" x-text="'Patokan: ' + item.patokan"></span>
                                </template>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-bold tabular-nums text-xs text-ink block" x-text="item.skor"></span>
                                <span class="text-[10px] text-muted block tabular-nums" x-text="'(x̄ ' + (item.skor / 9).toFixed(2).replace('.', ',') + ')'"></span>
                            </td>
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
                                        <a :href="'/admin/verifikasi?id=' + item.id" class="btn-primary !min-h-[30px] !py-1 !px-2.5 !text-[11px] font-bold">
                                            Periksa
                                        </a>
                                    </template>
                                    {{-- Tombol Lihat (Icon Mata) untuk data terverifikasi (SS2) --}}
                                    <template x-if="item.status === 'terverifikasi'">
                                        <button type="button"
                                                @click="openModalDetail(item)"
                                                class="p-1 text-muted hover:text-brand transition-colors"
                                                title="Lihat detail data warga">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
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
                Menampilkan <strong class="text-ink" x-text="paginatedData.length"></strong> dari <strong class="text-ink" x-text="filteredData.length"></strong> total data warga
                <template x-if="totalPages > 1">
                    <span class="text-[11px] text-muted ml-1">(Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages"></span>)</span>
                </template>
            </span>
            <div class="flex items-center gap-1 self-end sm:self-auto" x-show="totalPages > 1">
                <button type="button"
                        @click="prevPage()"
                        :disabled="currentPage === 1"
                        :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-paper' : 'hover:bg-paper active:scale-95 text-ink font-bold'"
                        class="px-2.5 py-1 rounded border border-line text-xs transition-all">
                    ‹
                </button>
                <template x-for="p in pageNumbers" :key="p">
                    <button type="button"
                            @click="goToPage(p)"
                            :disabled="p === '...'"
                            :class="p === currentPage ? 'bg-brand text-white font-bold' : (p === '...' ? 'cursor-default text-muted border-none bg-transparent' : 'bg-paper text-ink hover:bg-slate-200')"
                            class="min-w-[28px] h-7 px-2 rounded border border-line text-xs transition-colors flex items-center justify-center font-semibold"
                            x-text="p">
                    </button>
                </template>
                <button type="button"
                        @click="nextPage()"
                        :disabled="currentPage === totalPages"
                        :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-paper' : 'hover:bg-paper active:scale-95 text-ink font-bold'"
                        class="px-2.5 py-1 rounded border border-line text-xs transition-all">
                    ›
                </button>
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

            <p class="text-sm text-ink leading-relaxed">
                Apakah Anda yakin ingin menghapus data survei atas nama:
                <strong class="text-tinggi block text-base font-bold mt-1" x-text="selectedItem ? (selectedItem.nama || selectedItem.nama_kk) : ''"></strong>
            </p>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-line">
                <button type="button" @click="modalHapus = false" class="btn-secondary !text-xs !min-h-[38px] !px-4">
                    Batal
                </button>
                <button type="button"
                        @click="eksekusiHapus"
                        class="btn-danger !text-xs !min-h-[38px] !px-5 font-bold">
                    Hapus
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL LIHAT DETAIL DATA WARGA TERVERIFIKASI (SS2 & SS3)
         - Layout 2 kolom seimbang (SS4 Improvement)
         - Tombol aksi diganti "Tutup" (Membedakan Semua Data vs Antrean)
         ============================================================ --}}
    <div x-show="modalDetail"
         x-transition.opacity
         class="fixed inset-0 z-50 bg-ink/75 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5"
         style="display: none;"
         x-cloak
         role="dialog"
         aria-modal="true"
         @keydown.escape.window="modalDetail = false">

        <div @click.outside="modalDetail = false"
             class="card max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl overflow-hidden border border-line">

            {{-- Header Modal: Nama KK & Status (Dinamis) --}}
            <div class="px-6 py-4 border-b border-line flex items-center justify-between gap-3 bg-paper/80 flex-shrink-0">
                <div class="flex items-center gap-2.5">
                    <h3 class="text-base font-bold text-ink">
                        Detail Data — <span class="text-brand" x-text="detailItem ? (detailItem.nama || detailItem.nama_kk) : ''"></span>
                    </h3>
                    <template x-if="detailItem && detailItem.status === 'terverifikasi'">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-rendah-bg text-rendah-tx border border-rendah/30">
                            ● Terverifikasi
                        </span>
                    </template>
                    <template x-if="detailItem && detailItem.status === 'menunggu_verifikasi'">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold bg-sedang-bg text-sedang-tx border border-sedang/30">
                            ⏳ Menunggu Verifikasi
                        </span>
                    </template>
                </div>

                <button type="button" @click="modalDetail = false"
                        class="h-8 w-8 rounded-lg text-muted hover:text-ink hover:bg-paper flex items-center justify-center transition-colors"
                        aria-label="Tutup modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Badan Modal: Dua Kolom 50/50 Seimbang (SS4 Improvement) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 overflow-y-auto p-6 gap-6 flex-1">
                {{-- ============================================================
                     KOLOM KIRI: BUKTI VISUAL (Foto Bangunan & Peta Titik Koordinat)
                     Diberi padding kanan (lg:pr-6) dan batas garis (lg:border-r)
                     agar seimbang dan tidak menempel rapat ke garis tengah
                     ============================================================ --}}
                <div class="space-y-5 lg:pr-6 lg:border-r border-line">
                    {{-- Foto Bangunan Rumah --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-muted">Foto Bangunan Rumah</p>
                            <div class="flex items-center gap-1">
                                <button type="button"
                                        @click="fotoZoom = !fotoZoom"
                                        class="btn-secondary !min-h-[28px] !py-0.5 !px-2.5 !text-[11px]">
                                    <span x-text="fotoZoom ? 'Normal' : '🔍 Perbesar'"></span>
                                </button>
                                <button type="button"
                                        @click="fotoRotation = (fotoRotation + 90) % 360"
                                        class="btn-secondary !min-h-[28px] !py-0.5 !px-2.5 !text-[11px]">
                                    ↻ Putar
                                </button>
                            </div>
                        </div>

                        <div class="aspect-video bg-paper rounded-xl border border-line flex flex-col items-center justify-center text-muted p-2 overflow-hidden relative shadow-inner">
                            <template x-if="detailItem && detailItem.foto_rumah">
                                <div class="w-full h-full flex items-center justify-center overflow-hidden">
                                    <img :src="detailItem.foto_rumah"
                                         alt="Foto Rumah Responden"
                                         class="w-full h-full object-contain transition-transform duration-300 select-none pointer-events-none"
                                         :style="`transform: rotate(${fotoRotation}deg) scale(${fotoZoom ? 1.5 : 1});`">
                                </div>
                            </template>

                            <template x-if="!detailItem || !detailItem.foto_rumah">
                                <div class="w-full h-full flex flex-col items-center justify-center transition-transform duration-300"
                                     :style="`transform: rotate(${fotoRotation}deg) scale(${fotoZoom ? 1.4 : 1});`">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="mb-2 text-muted/60"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    <p class="text-xs font-bold text-ink">Foto Rumah Responden</p>
                                    <p class="text-[11px] text-muted mt-0.5">Tampak depan seluruh bangunan terlihat jelas</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Peta Titik Koordinat Rumah --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-muted">Peta Titik Koordinat</p>
                            <span class="text-xs font-bold text-ink tabular-nums"
                                  x-text="detailItem ? (detailItem.latitude + ', ' + detailItem.longitude) : ''"></span>
                        </div>

                        <div class="h-48 bg-paper rounded-xl border border-line flex flex-col items-center justify-center p-4 text-center relative shadow-inner">
                            <div class="h-8 w-8 rounded-full bg-brand text-white flex items-center justify-center shadow-lg border-2 border-white mb-2">
                                ⌖
                            </div>
                            <p class="text-xs font-bold text-ink">Lokasi Rumah di Kampung Sota</p>
                            <p class="text-[11px] text-rendah-tx font-bold mt-1 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Titik berada di dalam wilayah permukiman Kampung Sota</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ============================================================
                     KOLOM KANAN: DATA SURVEI & 9 VARIABEL SVI (PRD 11.3)
                     ============================================================ --}}
                <div class="space-y-4 text-xs">
                    {{-- 1. Identitas Rumah Tangga --}}
                    <div class="space-y-1 pb-3 border-b border-line">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted mb-2">Identitas Kepala Keluarga</p>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-muted block">Nama KK:</span>
                                <strong class="text-ink text-sm" x-text="detailItem ? (detailItem.nama || detailItem.nama_kk) : ''"></strong>
                            </div>
                            <div>
                                <span class="text-muted block">Jalur / Wilayah:</span>
                                <strong class="text-ink" x-text="detailItem ? detailItem.jalur : ''"></strong>
                            </div>
                            <div>
                                <span class="text-muted block">Alamat Detail:</span>
                                <span class="text-ink font-medium" x-text="detailItem ? detailItem.alamat : ''"></span>
                                <template x-if="detailItem && detailItem.patokan">
                                    <span class="block text-[11px] text-muted italic mt-0.5" x-text="'Patokan: ' + detailItem.patokan"></span>
                                </template>
                            </div>
                            <div>
                                <span class="text-muted block">Penginput:</span>
                                <span class="text-ink" x-text="detailItem ? (detailItem.operator + ' · ' + detailItem.waktu_ringkas) : ''"></span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Blok Khusus: Periksa Kesesuaian Terhadap Foto (PRD 11.3) --}}
                    <div class="bg-paper p-3.5 rounded-xl border border-line space-y-2">
                        <div class="flex items-center gap-1.5 text-sedang-tx font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>⚠️ Periksa Kesesuaian Terhadap Foto Rumah</span>
                        </div>
                        <p class="text-muted text-[11px]">Pastikan jawaban kader di bawah cocok dengan kondisi fisik di foto:</p>

                        <div class="grid grid-cols-2 gap-3 pt-1 font-medium">
                            <div class="bg-surface p-2.5 rounded-lg border border-line">
                                <span class="text-muted block text-[10px] uppercase">Dinding:</span>
                                <strong class="text-ink text-xs" x-text="detailItem ? detailItem.dinding : 'Kayu'"></strong>
                            </div>
                            <div class="bg-surface p-2.5 rounded-lg border border-line">
                                <span class="text-muted block text-[10px] uppercase">Lantai:</span>
                                <strong class="text-ink text-xs" x-text="detailItem ? detailItem.lantai : 'Semen'"></strong>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Jawaban 9 Variabel SVI --}}
                    <div class="space-y-1.5">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Jawaban 9 Variabel SVI</p>
                        <div class="border border-line rounded-xl divide-y divide-line overflow-hidden bg-surface">
                            <template x-if="detailItem">
                                <template x-for="varItem in getDetailSviItems(detailItem)" :key="varItem.no">
                                    <div class="p-2.5 flex items-center justify-between gap-3 hover:bg-paper/40">
                                        <div class="min-w-0">
                                            <span class="font-bold text-ink" x-text="varItem.no + '. ' + varItem.label"></span>
                                            <p class="text-muted truncate text-[11px]" x-text="varItem.jawaban"></p>
                                        </div>
                                        <span class="flex-shrink-0 font-bold text-ink bg-paper border border-line px-2 py-0.5 rounded text-[11px] tabular-nums"
                                              x-text="'Skor ' + varItem.skor"></span>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>

                    {{-- 4. Blok Skor Read-Only (PRD 11.3) --}}
                    <div class="bg-paper p-3.5 rounded-xl border border-line space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-muted font-medium">Total Skor SVI:</span>
                            <span class="font-bold text-ink text-base tabular-nums" x-text="detailItem ? detailItem.skor + ' (skala 9–27)' : ''"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted font-medium">Skor Rata-Rata:</span>
                            <span class="font-bold text-ink tabular-nums" x-text="detailItem ? detailItem.skor_rata_rata : ''"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted font-medium">Klasifikasi:</span>
                            <template x-if="detailItem && (detailItem.klas === 'tinggi' || detailItem.klasifikasi === 'tinggi')">
                                <x-badge-klasifikasi tingkat="tinggi" />
                            </template>
                            <template x-if="detailItem && (detailItem.klas === 'sedang' || detailItem.klasifikasi === 'sedang')">
                                <x-badge-klasifikasi tingkat="sedang" />
                            </template>
                            <template x-if="detailItem && (detailItem.klas === 'rendah' || detailItem.klasifikasi === 'rendah')">
                                <x-badge-klasifikasi tingkat="rendah" />
                            </template>
                        </div>
                        <p class="text-[11px] text-muted italic pt-1 border-t border-line">
                            * Skor dihitung otomatis oleh sistem berdasarkan rumus additive scoring dan tidak dapat diubah manual.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Footer Modal --}}
            <div class="px-6 py-3.5 border-t border-line bg-paper/60 flex flex-col sm:flex-row items-center justify-between gap-3 flex-shrink-0">
                <div class="flex items-center gap-2 text-xs text-muted">
                    <template x-if="detailItem && detailItem.status === 'terverifikasi'">
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rendah-tx bg-rendah-bg border border-rendah/30 px-2.5 py-0.5 rounded-full">
                            ● Terverifikasi
                        </span>
                    </template>
                    <template x-if="detailItem && detailItem.status === 'menunggu_verifikasi'">
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-sedang-tx bg-sedang-bg border border-sedang/30 px-2.5 py-0.5 rounded-full">
                            ⏳ Menunggu Verifikasi
                        </span>
                    </template>
                    <span>Data survei kependudukan Kampung Sota.</span>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <template x-if="detailItem && detailItem.status === 'menunggu_verifikasi'">
                        <button type="button"
                                @click="verifikasiDariDetail(detailItem)"
                                class="btn-primary !min-h-[38px] !py-1 !px-5 !text-xs font-bold shadow-md">
                            ✓ Verifikasi Sekarang
                        </button>
                    </template>
                    <button type="button"
                            @click="modalDetail = false"
                            class="btn-secondary !min-h-[38px] !py-1 !px-6 !text-xs font-bold shadow-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
