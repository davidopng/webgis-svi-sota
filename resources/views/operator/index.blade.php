@extends('layouts.app', ['role' => 'operator', 'userName' => Auth::user()?->name ?? 'Operator'])

@section('title', 'Daftar Data Saya — SOTA-GIS')
@section('page-title', 'Daftar Data Saya')

@php
    $dataListJson = json_encode(($dataList ?? collect([]))->map(fn($item) => [
        'id' => $item->id,
        'nama_kk' => $item->nama_kk,
        'jalur' => $item->jalur,
        'alamat' => $item->alamat ?? (($item->rt ?? '') . ' / ' . ($item->rw ?? '')),
        'skor' => $item->total_skor,
        'skor_rata_rata' => number_format($item->total_skor / 9, 2, ',', '.'),
        'klasifikasi' => $item->klasifikasi,
        'status' => $item->status_verifikasi,
        'diinput_pada' => $item->created_at ? $item->created_at->format('d M Y, H:i') : '-'
    ])->values());
@endphp

@section('content')
<script>
    function operatorDataList() {
        const rawList = {!! $dataListJson !!};
        return {
            statusFilter: 'semua',
            searchQuery: '',
            toastSukses: false,
            dataList: rawList,
            init() {
                // Cek jika ada query parameter status
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('status')) {
                    this.statusFilter = urlParams.get('status');
                }
                if (urlParams.get('baru') === '1') {
                    this.toastSukses = true;
                    setTimeout(() => { this.toastSukses = false; }, 5000);
                }
            },

            get countMenunggu() {
                return this.dataList.filter(item => item.status === 'menunggu_verifikasi').length;
            },
            get countTerverifikasi() {
                return this.dataList.filter(item => item.status === 'terverifikasi').length;
            },

            get filteredData() {
                return this.dataList.filter(item => {
                    const matchStatus = this.statusFilter === 'semua' || item.status === this.statusFilter;
                    const q = this.searchQuery ? this.searchQuery.toLowerCase().trim() : '';
                    const matchSearch = !q ||
                                        (item.nama_kk && item.nama_kk.toLowerCase().includes(q)) ||
                                        (item.alamat && item.alamat.toLowerCase().includes(q));
                    return matchStatus && matchSearch;
                });
            }
        };
    }

    if (window.Alpine) {
        Alpine.data('operatorDataList', operatorDataList);
    } else {
        document.addEventListener('alpine:init', () => {
            Alpine.data('operatorDataList', operatorDataList);
        });
    }
</script>

<div x-data="operatorDataList()" class="space-y-6">

    {{-- Header & Tombol Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-ink">Data Hasil Survei Anda</h2>
            <p class="text-help mt-0.5">Daftar rumah tangga yang Anda data di Jalur A, Jalur B, dan Poros Trans.</p>
        </div>
        <a href="/operator/data/create" class="btn-primary self-start sm:self-auto font-bold shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Input Data Warga Baru</span>
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
                Semua (<span x-text="dataList.length"></span>)
            </button>
            <button type="button"
                    @click="statusFilter = 'menunggu_verifikasi'"
                    :class="statusFilter === 'menunggu_verifikasi' ? 'bg-sedang text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                    class="rounded-md px-3 py-1.5 text-xs transition-colors">
                Menunggu Verifikasi (<span x-text="countMenunggu"></span>)
            </button>
            <button type="button"
                    @click="statusFilter = 'terverifikasi'"
                    :class="statusFilter === 'terverifikasi' ? 'bg-rendah text-white font-bold' : 'bg-paper text-muted hover:text-ink'"
                    class="rounded-md px-3 py-1.5 text-xs transition-colors">
                Terverifikasi (<span x-text="countTerverifikasi"></span>)
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

                {{-- Bar Bawah Kartu Mobile --}}
                <div class="flex items-center justify-between border-t border-line pt-2.5 text-xs">
                    <span class="text-muted text-[11px]" x-text="item.diinput_pada || ''"></span>

                    {{-- Tombol Aksi Mobile --}}
                    <div>
                        <template x-if="item.status === 'menunggu_verifikasi'">
                            <a :href="'/operator/data/' + item.id + '/edit'"
                               class="btn-secondary !min-h-[36px] !py-1 !px-3.5 !text-xs font-semibold">
                                Ubah Data
                            </a>
                        </template>
                        <template x-if="item.status === 'terverifikasi'">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-muted italic">
                                    Sudah diverifikasi
                                </span>
                                <a :href="'/operator/data/' + item.id + '/edit'"
                                   class="btn-secondary !min-h-[32px] !py-1 !px-3 !text-xs font-bold text-muted hover:text-ink">
                                    Lihat
                                </a>
                            </div>
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
