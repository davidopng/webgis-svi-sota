@extends('layouts.app', [
    'role' => 'admin',
    'userName' => Auth::user()?->name ?? 'Jhoni Arafat'
])

@section('title', 'Antrean Verifikasi — SOTA-GIS')
@section('page-title', 'Antrean Verifikasi Data')

@php
    $antreanListJson = json_encode(($antreanList ?? collect([]))->map(fn($item) => [
        'id' => $item->id,
        'nama_kk' => $item->nama_kk,
        'jalur' => $item->jalur,
        'alamat' => $item->alamat ?? (($item->rt ?? '') . ' / ' . ($item->rw ?? '')),
        'patokan' => $item->patokan ?? '',
        'operator' => $item->nama_penginput,
        'waktu_input' => $item->created_at ? $item->created_at->format('d M Y, H:i') . ' WIT' : '-',
        'waktu_ringkas' => $item->created_at ? $item->created_at->diffForHumans() : '-',
        'status' => $item->status_verifikasi,
        'latitude' => (float)$item->latitude,
        'longitude' => (float)$item->longitude,
        'lokasi_valid' => true,
        'skor' => $item->total_skor,
        'skor_rata_rata' => number_format($item->total_skor / 9, 2, ',', '.'),
        'klasifikasi' => $item->klasifikasi,
        'klasifikasi_label' => 'Kerentanan ' . ucfirst($item->klasifikasi),
        'dinding' => $item->jenis_dinding ?? '-',
        'lantai' => $item->jenis_lantai ?? '-',
        'foto_rumah' => $item->foto_rumah,
        'svi_items' => $item->svi_items,
    ])->values());
@endphp

@section('content')
<script>
    function adminVerifikasi() {
        const rawList = {!! $antreanListJson !!};
        return {
            statusFilter: 'menunggu_verifikasi',
            jalurFilter: 'semua',
            klasifikasiFilter: 'semua',
            searchQuery: '',
            selectedId: null,
            drawerOpen: false,
            tabAktif: 'svi',
            catatanTolak: '',
            modalKonfirmasiTolak: false,
            modalFotoZoom: false,
            fotoZoomSrc: '',
            modalPetaDetail: false,
            toastVisible: false,
            toastMessage: '',
            toastType: 'sukses',
            currentIndex: 0,
            fotoZoom: false,
            fotoRotation: 0,
            modalValidasi: false,
            hapusTarget: null,
            konfirmasiNamaInput: '',
            modalHapus: false,
            antreanList: rawList,

            init() {
                const urlParams = new URLSearchParams(window.location.search);
                const reqId = urlParams.get('id');
                if (reqId) {
                    const idx = this.filteredAntrean.findIndex(i => String(i.id) === String(reqId));
                    if (idx !== -1) {
                        this.currentIndex = idx;
                        this.modalValidasi = true;
                    }
                }
            },

            showToast(msg, type = 'sukses') {
                this.toastMessage = msg;
                this.toastType = type;
                this.toastVisible = true;
                setTimeout(() => { this.toastVisible = false; }, 6000);
            },

            get filteredAntrean() {
                return this.antreanList.filter(item => {
                    const matchStatus = this.statusFilter === 'semua' || item.status === this.statusFilter;
                    const matchJalur = this.jalurFilter === 'semua' || item.jalur === this.jalurFilter;
                    const matchKlas = this.klasifikasiFilter === 'semua' || item.klasifikasi === this.klasifikasiFilter;
                    const q = this.searchQuery ? this.searchQuery.toLowerCase().trim() : '';
                    const matchQuery = !q ||
                                       (item.nama_kk && item.nama_kk.toLowerCase().includes(q)) ||
                                       (item.alamat && item.alamat.toLowerCase().includes(q));
                    return matchStatus && matchJalur && matchKlas && matchQuery;
                });
            },

            get currentItem() {
                return this.filteredAntrean[this.currentIndex] || null;
            },

            // Paginasi
            perPage: 10,
            currentPage: 1,

            get totalPages() {
                return Math.max(1, Math.ceil(this.filteredAntrean.length / this.perPage));
            },

            get paginatedAntrean() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredAntrean.slice(start, start + this.perPage);
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

            bukaModalPeriksa(itemOrIndex) {
                if (typeof itemOrIndex === 'object' && itemOrIndex !== null) {
                    const idx = this.filteredAntrean.findIndex(i => i.id === itemOrIndex.id);
                    this.currentIndex = idx !== -1 ? idx : 0;
                } else if (typeof itemOrIndex === 'number') {
                    const globalIdx = (this.currentPage - 1) * this.perPage + itemOrIndex;
                    this.currentIndex = (globalIdx < this.filteredAntrean.length) ? globalIdx : itemOrIndex;
                }
                this.fotoZoom = false;
                this.fotoRotation = 0;
                this.modalValidasi = true;
            },

            navSebelumnya() {
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                    this.fotoZoom = false;
                    this.fotoRotation = 0;
                }
            },

            navBerikutnya() {
                if (this.currentIndex < this.filteredAntrean.length - 1) {
                    this.currentIndex++;
                    this.fotoZoom = false;
                    this.fotoRotation = 0;
                }
            },

            // Aksi Verifikasi & Tampilkan di Peta
            async verifikasiItem() {
                if (!this.currentItem) return;
                const target = this.currentItem;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                try {
                    const res = await fetch('/admin/data/' + target.id + '/verifikasi', {
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

                    target.status = 'terverifikasi';
                    target.status_verifikasi = 'terverifikasi';

                    // Perbarui status di localStorage
                    if (window.SotaStorage) {
                        window.SotaStorage.updateStatus(target.id, 'terverifikasi');
                    }

                    this.modalValidasi = false;
                    this.showToast('Data berhasil diverifikasi.', 'sukses'); if (window.sotaToast) window.sotaToast('Data berhasil diverifikasi.', 'berhasil');
                } catch (err) {
                    console.error('Verifikasi error:', err);
                    this.showToast('Data gagal disimpan. Silakan coba lagi.', 'gagal'); if (window.sotaToast) window.sotaToast('Data gagal disimpan. Silakan coba lagi.', 'gagal');
                }
            },

            // Pemicu Modal Hapus Kedua
            bukaModalHapus(item) {
                this.hapusTarget = item;
                this.konfirmasiNamaInput = '';
                this.modalHapus = true;
            },

            async konfirmasiHapusPermanen() {
                if (!this.hapusTarget) return;
                // Langsung hapus tanpa ketik nama

                const nama = this.hapusTarget.nama_kk;
                const targetId = this.hapusTarget.id;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                try {
                    const res = await fetch('/admin/data/' + targetId, {
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

                    // Hapus dari localStorage jika ada
                    if (window.SotaStorage) {
                        window.SotaStorage.delete(targetId);
                    }

                    this.antreanList = this.antreanList.filter(x => x.id !== targetId);
                    this.modalHapus = false;
                    this.modalValidasi = false;
                    this.showToast('Data berhasil dihapus.', 'sukses'); if (window.sotaToast) window.sotaToast('Data berhasil dihapus.', 'berhasil');
                } catch (err) {
                    console.error('Delete error:', err);
                    this.showToast('Data gagal disimpan. Silakan coba lagi.', 'gagal'); if (window.sotaToast) window.sotaToast('Data gagal disimpan. Silakan coba lagi.', 'gagal');
                }
            }
        };
    }

    if (window.Alpine) {
        Alpine.data('adminVerifikasi', adminVerifikasi);
    } else {
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminVerifikasi', adminVerifikasi);
        });
    }
</script>

<div x-data="adminVerifikasi()" class="space-y-6">

    {{-- Toast Notification Melayang --}}
    <div x-show="toastVisible"
         x-transition
         class="fixed top-4 right-4 z-[100] max-w-sm"
         style="display: none;"
         x-cloak>
        <x-toast ::type="toastType" ::message="toastMessage" />
    </div>

    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-ink">Antrean Verifikasi Data</h2>
            <p class="text-help mt-0.5">Pemeriksaan kelengkapan 9 variabel SVI, kesesuaian fisik bangunan dengan foto, dan keabsahan titik koordinat.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-sedang-bg text-sedang-tx border border-sedang/30">
                <span class="h-2 w-2 rounded-full bg-sedang animate-pulse"></span>
                <span x-text="antreanList.filter(x => x.status === 'menunggu_verifikasi').length + ' data menunggu'"></span>
            </span>
        </div>
    </div>

    {{-- Filter Bar Lengkap (PRD 11.2 Wireframe) --}}
    <div class="card p-4 space-y-3">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            {{-- Tabs Filter Status --}}
            <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                        @click="statusFilter = 'semua'; currentPage = 1"
                        :class="statusFilter === 'semua' ? 'bg-brand text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                        class="rounded-md px-3 py-1.5 text-xs transition-colors">
                    Semua (<span x-text="antreanList.length"></span>)
                </button>
                <button type="button"
                        @click="statusFilter = 'menunggu_verifikasi'; currentPage = 1"
                        :class="statusFilter === 'menunggu_verifikasi' ? 'bg-sedang text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                        class="rounded-md px-3 py-1.5 text-xs transition-colors">
                    Menunggu Verifikasi (<span x-text="antreanList.filter(x => x.status === 'menunggu_verifikasi').length"></span>)
                </button>
                <button type="button"
                        @click="statusFilter = 'terverifikasi'; currentPage = 1"
                        :class="statusFilter === 'terverifikasi' ? 'bg-rendah text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                        class="rounded-md px-3 py-1.5 text-xs transition-colors">
                    Terverifikasi (<span x-text="antreanList.filter(x => x.status === 'terverifikasi').length"></span>)
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
                    @click="statusFilter = 'semua'; jalurFilter = 'semua'; klasifikasiFilter = 'semua'; searchQuery = ''; currentPage = 1"
                    class="text-brand font-semibold hover:underline ml-auto">
                Atur ulang filter
            </button>
        </div>
    </div>

    {{-- ============================================================
         TABEL ANTREAN VERIFIKASI (PRD 11.2)
         ============================================================ --}}
    <div class="card overflow-hidden !p-0 shadow-sm">
        <template x-if="filteredAntrean.length > 0">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-paper border-b border-line text-xs font-semibold uppercase tracking-wider text-muted">
                        <tr>
                            <th class="py-3.5 px-4 w-16">Foto</th>
                            <th class="py-3.5 px-4">Nama KK &amp; Penginput</th>
                            <th class="py-3.5 px-4">Jalur</th>
                            <th class="py-3.5 px-4 text-center">Skor</th>
                            <th class="py-3.5 px-4">Klasifikasi</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-ink">
                        <template x-for="(item, index) in paginatedAntrean" :key="item.id">
                            <tr class="hover:bg-paper/40 transition-colors"
                                :class="item.status === 'menunggu_verifikasi' ? 'border-l-[3px] border-l-sedang bg-sedang/[0.01]' : ''">
                                {{-- Thumbnail Foto 48px Persegi (PRD 11.2) --}}
                                <td class="py-3 px-4">
                                    <button type="button"
                                            @click="bukaModalPeriksa(item)"
                                            title="Klik untuk melihat foto dan periksa data"
                                            class="h-12 w-12 rounded-lg bg-paper border border-line flex items-center justify-center text-muted font-bold text-base hover:border-brand transition-colors cursor-pointer overflow-hidden shadow-inner">
                                        🏠
                                    </button>
                                </td>

                                {{-- Nama KK & Operator Penginput --}}
                                <td class="py-3 px-4">
                                    <p class="font-bold text-ink" x-text="item.nama_kk"></p>
                                    <p class="text-xs text-muted">
                                        <span x-text="item.alamat"></span>
                                        <template x-if="item.patokan">
                                            <span class="italic text-muted/80" x-text="' (' + item.patokan + ')'"></span>
                                        </template>
                                        <span x-text="' · ' + item.operator"></span>
                                    </p>
                                </td>

                                {{-- Jalur --}}
                                <td class="py-3 px-4 text-xs font-medium" x-text="item.jalur"></td>

                                {{-- Skor SVI (Tabular) --}}
                                <td class="py-3 px-4 text-center font-bold tabular-nums" x-text="item.skor"></td>

                                {{-- Klasifikasi Badge --}}
                                <td class="py-3 px-4">
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
                                <td class="py-3 px-4">
                                    <template x-if="item.status === 'menunggu_verifikasi'">
                                        <x-badge-status status="menunggu_verifikasi" />
                                    </template>
                                    <template x-if="item.status === 'terverifikasi'">
                                        <x-badge-status status="terverifikasi" />
                                    </template>
                                </td>

                                {{-- Aksi: Periksa / Lihat + Menu ⋮ --}}
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                @click="bukaModalPeriksa(item)"
                                                class="btn-primary !min-h-[34px] !py-1 !px-3.5 !text-xs font-bold shadow-sm"
                                                :class="item.status === 'terverifikasi' ? '!bg-paper !text-ink hover:!bg-line/60 border border-line shadow-none' : ''">
                                            <span x-text="item.status === 'menunggu_verifikasi' ? 'Periksa' : 'Lihat'"></span>
                                        </button>

                                        {{-- Menu Dropdown ⋮ --}}
                                        <x-tabel-aksi>
                                            <a :href="'/admin/data/' + item.id + '/edit'"
                                               class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors text-ink hover:bg-paper">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/></svg>
                                                <span>Revisi data</span>
                                            </a>
                                            <button type="button"
                                                    @click="bukaModalHapus(item)"
                                                    class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors text-tinggi hover:bg-tinggi-bg">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                                <span>Hapus data</span>
                                            </button>
                                        </x-tabel-aksi>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        {{-- Empty State Jika Tidak Ada Antrean --}}
        <template x-if="filteredAntrean.length === 0">
            <x-empty-state
                title="Tidak ada antrean verifikasi"
                description="Semua data warga telah diperiksa dan berstatus terverifikasi, atau tidak sesuai dengan penyaring saat ini."
                action-text="Lihat Semua Data"
                action-url="/admin/data"
                icon="shield"
            />
        </template>

        {{-- Paginasi Footer Interaktif Sesuai PRD 11.2 --}}
        <div class="p-4 border-t border-line flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-muted">
            <div>
                Menampilkan <strong class="text-ink" x-text="paginatedAntrean.length"></strong> dari <strong class="text-ink" x-text="filteredAntrean.length"></strong> data
                <template x-if="totalPages > 1">
                    <span class="text-[11px] text-muted ml-1">(Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages"></span>)</span>
                </template>
            </div>

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
         MODAL VALIDASI BERDAMPINGAN (PRD 11.3) — LEBAR 1100px (50/50)
         ============================================================ --}}
    <div x-show="modalValidasi"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-98"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-98"
         class="fixed inset-0 z-50 overflow-y-auto bg-ink/70 flex items-center justify-center p-3 sm:p-5"
         style="display: none;"
         x-cloak>

        <div @click.outside="modalValidasi = false"
             class="bg-surface rounded-2xl shadow-2xl max-w-[1100px] w-full max-h-[92vh] flex flex-col overflow-hidden border border-line">

            {{-- Header Modal: Nama KK & Status --}}
            <div class="px-6 py-4 border-b border-line flex items-center justify-between bg-paper/60 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg font-bold text-ink flex items-center gap-2">
                        <span>Periksa Data —</span>
                        <span x-text="currentItem ? currentItem.nama_kk : ''" class="text-brand"></span>
                    </h3>
                    <template x-if="currentItem && currentItem.status === 'menunggu_verifikasi'">
                        <x-badge-status status="menunggu_verifikasi" />
                    </template>
                    <template x-if="currentItem && currentItem.status === 'terverifikasi'">
                        <x-badge-status status="terverifikasi" />
                    </template>
                </div>

                <button type="button" @click="modalValidasi = false"
                        class="h-8 w-8 rounded-lg text-muted hover:text-ink hover:bg-paper flex items-center justify-center transition-colors"
                        aria-label="Tutup modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Badan Modal: Dua Kolom 50/50 Seimbang (PRD 11.3 & Evaluasi UI) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 overflow-y-auto p-6 gap-6 flex-1">
                {{-- ============================================================
                     KOLOM KIRI: BUKTI VISUAL (Foto Rumah & Peta Titik Koordinat)
                     Diberi padding kanan (lg:pr-6) dan batas garis (lg:border-r)
                     agar seimbang dan tidak menempel rapat ke garis tengah
                     ============================================================ --}}
                <div class="space-y-5 lg:pr-6 lg:border-r border-line">
                    {{-- Foto Rumah Tampak Depan --}}
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
                            {{-- Jika Ada Foto Rumah --}}
                            <template x-if="currentItem && currentItem.foto_rumah">
                                <div class="w-full h-full flex items-center justify-center overflow-hidden">
                                    <img :src="currentItem.foto_rumah"
                                         alt="Foto Rumah Responden"
                                         class="w-full h-full object-contain transition-transform duration-300 select-none pointer-events-none"
                                         :style="`transform: rotate(${fotoRotation}deg) scale(${fotoZoom ? 1.5 : 1});`">
                                </div>
                            </template>

                            {{-- Jika Belum / Tidak Ada Foto --}}
                            <template x-if="!currentItem || !currentItem.foto_rumah">
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
                                  x-text="currentItem ? currentItem.latitude + ', ' + currentItem.longitude : ''"></span>
                        </div>

                        {{-- Kotak Peta Pratinjau --}}
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
                                <strong class="text-ink text-sm" x-text="currentItem ? currentItem.nama_kk : ''"></strong>
                            </div>
                            <div>
                                <span class="text-muted block">Jalur / Wilayah:</span>
                                <strong class="text-ink" x-text="currentItem ? currentItem.jalur : ''"></strong>
                            </div>
                            <div>
                                <span class="text-muted block">Alamat Detail:</span>
                                <span class="text-ink font-medium" x-text="currentItem ? currentItem.alamat : ''"></span>
                                <template x-if="currentItem && currentItem.patokan">
                                    <span class="block text-[11px] text-muted italic mt-0.5" x-text="'Patokan: ' + currentItem.patokan"></span>
                                </template>
                            </div>
                            <div>
                                <span class="text-muted block">Penginput:</span>
                                <span class="text-ink" x-text="currentItem ? currentItem.operator + ' · ' + currentItem.waktu_ringkas : ''"></span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Blok Khusus: Periksa Terhadap Foto (PRD 11.3) --}}
                    <div class="bg-paper p-3.5 rounded-xl border border-line space-y-2">
                        <div class="flex items-center gap-1.5 text-sedang-tx font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>⚠️ Periksa Kesesuaian Terhadap Foto Rumah</span>
                        </div>
                        <p class="text-muted text-[11px]">Pastikan jawaban kader di bawah cocok dengan kondisi fisik di foto:</p>

                        <div class="grid grid-cols-2 gap-3 pt-1 font-medium">
                            <div class="bg-surface p-2.5 rounded-lg border border-line">
                                <span class="text-muted block text-[10px] uppercase">Dinding:</span>
                                <strong class="text-ink text-xs" x-text="currentItem ? currentItem.dinding : ''"></strong>
                            </div>
                            <div class="bg-surface p-2.5 rounded-lg border border-line">
                                <span class="text-muted block text-[10px] uppercase">Lantai:</span>
                                <strong class="text-ink text-xs" x-text="currentItem ? currentItem.lantai : ''"></strong>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Jawaban 9 Variabel SVI --}}
                    <div class="space-y-1.5">
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Jawaban 9 Variabel SVI</p>
                        <div class="border border-line rounded-xl divide-y divide-line overflow-hidden bg-surface">
                            <template x-if="currentItem">
                                <template x-for="varItem in currentItem.svi_items" :key="varItem.no">
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
                            <span class="font-bold text-ink text-base tabular-nums" x-text="currentItem ? currentItem.skor + ' (skala 9–27)' : ''"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted font-medium">Skor Rata-Rata:</span>
                            <span class="font-bold text-ink tabular-nums" x-text="currentItem ? currentItem.skor_rata_rata : ''"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-muted font-medium">Klasifikasi:</span>
                            <template x-if="currentItem && currentItem.klasifikasi === 'tinggi'">
                                <x-badge-klasifikasi tingkat="tinggi" />
                            </template>
                            <template x-if="currentItem && currentItem.klasifikasi === 'sedang'">
                                <x-badge-klasifikasi tingkat="sedang" />
                            </template>
                            <template x-if="currentItem && currentItem.klasifikasi === 'rendah'">
                                <x-badge-klasifikasi tingkat="rendah" />
                            </template>
                        </div>
                        <p class="text-[11px] text-muted italic pt-1 border-t border-line">
                            * Skor dihitung otomatis oleh sistem berdasarkan rumus additive scoring dan tidak dapat diubah manual.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Footer Modal: Navigasi Antrean & Aksi Validasi (PRD 11.3) --}}
            <div class="px-6 py-3.5 border-t border-line bg-paper/60 flex flex-col sm:flex-row items-center justify-between gap-3 flex-shrink-0">
                {{-- Kiri: Hapus Data & Navigasi Sebelum/Berikut --}}
                <div class="flex items-center gap-2 w-full sm:w-auto justify-between sm:justify-start">
                    <button type="button"
                            @click="bukaModalHapus(currentItem)"
                            class="btn-danger !min-h-[38px] !py-1 !px-3 !text-xs font-semibold">
                        Hapus data
                    </button>

                    <div class="flex items-center gap-1">
                        <button type="button"
                                @click="navSebelumnya"
                                :disabled="currentIndex === 0"
                                class="btn-secondary !min-h-[38px] !py-1 !px-2.5 !text-xs disabled:opacity-40 disabled:cursor-not-allowed"
                                title="Data Sebelumnya">
                            ‹ Sebelumnya
                        </button>
                        <span class="text-[11px] text-muted tabular-nums px-2"
                              x-text="(currentIndex + 1) + ' dari ' + filteredAntrean.length"></span>
                        <button type="button"
                                @click="navBerikutnya"
                                :disabled="currentIndex >= filteredAntrean.length - 1"
                                class="btn-secondary !min-h-[38px] !py-1 !px-2.5 !text-xs disabled:opacity-40 disabled:cursor-not-allowed"
                                title="Data Berikutnya">
                            Berikutnya ›
                        </button>
                    </div>
                </div>

                {{-- Kanan: Aksi (Jika status menunggu verifikasi: Revisi & Verifikasi; Jika terverifikasi: Tutup) --}}
                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <template x-if="!currentItem || currentItem.status === 'menunggu_verifikasi'">
                        <div class="flex items-center gap-2">
                            <a :href="'/admin/data/' + (currentItem ? currentItem.id : 1) + '/edit'"
                               class="btn-secondary !min-h-[38px] !py-1 !px-4 !text-xs font-semibold">
                                Revisi data
                            </a>

                            <button type="button"
                                    @click="verifikasiItem"
                                    class="btn-primary !min-h-[38px] !py-1 !px-5 !text-xs font-bold shadow-md">
                                ✓ Verifikasi &amp; Tampilkan
                            </button>
                        </div>
                    </template>

                    <template x-if="currentItem && currentItem.status === 'terverifikasi'">
                        <button type="button"
                                @click="modalValidasi = false"
                                class="btn-primary !min-h-[38px] !py-1 !px-6 !text-xs font-bold shadow-sm">
                            Tutup
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL KONFIRMASI HAPUS PERMANEN KEDUA (PRD 11.3)
         Mengharuskan mengetik nama KK sebelum tombol hapus aktif
         ============================================================ --}}
    <div x-show="modalHapus"
         x-transition
         class="fixed inset-0 z-[60] bg-ink/75 flex items-center justify-center p-4"
         style="display: none;"
         x-cloak>

        <div @click.outside="modalHapus = false"
             class="bg-surface rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4 border border-tinggi/30">
            <div class="flex items-center gap-3 text-tinggi">
                <div class="h-10 w-10 rounded-full bg-tinggi-bg flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-ink">Hapus Data Permanen?</h3>
                    <p class="text-xs text-muted">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            <p class="text-xs text-ink leading-relaxed">
                Anda akan menghapus data survei kepala keluarga: <strong class="text-tinggi-tx" x-text="hapusTarget ? hapusTarget.nama_kk : ''"></strong>.
            </p>

            <div class="space-y-1.5 pt-1">
                <label class="block text-xs font-medium text-muted">
                    Ketik nama kepala keluarga <strong class="text-ink" x-text="hapusTarget ? hapusTarget.nama_kk : ''"></strong> untuk mengonfirmasi:
                </label>
                <input type="text"
                       x-model="konfirmasiNamaInput"
                       class="input-text text-sm"
                       placeholder="Ketik nama persis di sini…">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                <button type="button" @click="modalHapus = false" class="btn-secondary !min-h-[38px] !text-xs font-semibold">
                    Batal
                </button>
                <button type="button"
                        @click="konfirmasiHapusPermanen"
                        :disabled="!hapusTarget || konfirmasiNamaInput.trim() !== hapusTarget.nama_kk"
                        class="btn-danger-solid !min-h-[38px] !text-xs font-bold disabled:opacity-40 disabled:cursor-not-allowed">
                    Hapus Permanen
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
