@extends('layouts.app', [
    'role' => 'admin',
    'userName' => Auth::user()?->name ?? 'Jhoni Arafat'
])

@section('title', 'Log Pembaruan — SOTA-GIS')
@section('page-title', 'Log Pembaruan Sistem')

@php
    $logsJson = json_encode($logs ?? []);
@endphp

@section('content')
<script>
    function adminLogPage() {
        const rawLogs = {!! $logsJson !!};
        return {
            // Filter State
            filterAksi: 'semua',
            filterPengguna: 'semua',
            filterTanggal: '',
            searchKk: '',

            // Modal Detail State
            modalDetail: false,
            activeLog: null,

            openDetail(log) {
                this.activeLog = log;
                this.modalDetail = true;
            },

            resetFilter() {
                this.filterAksi = 'semua';
                this.filterPengguna = 'semua';
                this.filterTanggal = '';
                this.searchKk = '';
                this.currentPage = 1;
            },

            // Log Audit Trail
            logs: rawLogs,

            // Paginasi
            perPage: 10,
            currentPage: 1,

            get totalPages() {
                return Math.max(1, Math.ceil(this.filteredLogs.length / this.perPage));
            },

            get paginatedLogs() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredLogs.slice(start, start + this.perPage);
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

            get filteredLogs() {
                return this.logs.filter(log => {
                    const matchAksi = this.filterAksi === 'semua' || log.aksi === this.filterAksi || (this.filterAksi === 'hapus' && log.aksi === 'delete');
                    const matchPengguna = this.filterPengguna === 'semua' || log.pengguna === this.filterPengguna;
                    const matchTanggal = !this.filterTanggal || log.tanggal === this.filterTanggal;
                    const matchSearch = !this.searchKk ||
                        (log.dataTerkait && log.dataTerkait.toLowerCase().includes(this.searchKk.toLowerCase())) ||
                        (log.pengguna && log.pengguna.toLowerCase().includes(this.searchKk.toLowerCase())) ||
                        (log.keterangan && log.keterangan.toLowerCase().includes(this.searchKk.toLowerCase()));

                    return matchAksi && matchPengguna && matchTanggal && matchSearch;
                });
            }
        };
    }
</script>

<div x-data="adminLogPage()" class="space-y-6">

    {{-- Header Halaman --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-line">
        <div>
            <h2 class="text-xl font-bold text-ink">Catatan Aktivitas &amp; Log Pembaruan</h2>
            <p class="text-help text-xs mt-0.5">
                Audit trail seluruh rekam jejak penambahan, modifikasi, verifikasi, dan penghapusan data survei sosial ekonomi Kampung Sota.
            </p>
        </div>
        <div class="text-xs text-muted">
            Total tercatat: <strong class="text-ink font-bold" x-text="logs.length"></strong> aktivitas
        </div>
    </div>

    {{-- ============================================================
         FILTER BAR LOG (PRD 11.5)
         ============================================================ --}}
    <div class="card p-4 space-y-3">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            {{-- Tombol Filter Jenis Aksi (Badge/Pills) --}}
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                <span class="text-muted font-semibold mr-1">Aksi:</span>
                <button type="button"
                        @click="filterAksi = 'semua'; currentPage = 1"
                        :class="filterAksi === 'semua' ? 'bg-brand text-white' : 'bg-paper text-muted hover:text-ink border border-line'"
                        class="px-3 py-1.5 rounded-lg font-bold transition-colors">
                    Semua
                </button>
                <button type="button"
                        @click="filterAksi = 'tambah'; currentPage = 1"
                        :class="filterAksi === 'tambah' ? 'bg-ink text-white' : 'bg-paper text-muted hover:text-ink border border-line'"
                        class="px-3 py-1.5 rounded-lg font-bold transition-colors">
                    Tambah
                </button>
                <button type="button"
                        @click="filterAksi = 'ubah'; currentPage = 1"
                        :class="filterAksi === 'ubah' ? 'bg-blue-600 text-white' : 'bg-paper text-muted hover:text-ink border border-line'"
                        class="px-3 py-1.5 rounded-lg font-bold transition-colors">
                    Ubah
                </button>
                <button type="button"
                        @click="filterAksi = 'verifikasi'; currentPage = 1"
                        :class="filterAksi === 'verifikasi' ? 'bg-rendah text-white' : 'bg-paper text-muted hover:text-ink border border-line'"
                        class="px-3 py-1.5 rounded-lg font-bold transition-colors">
                    Verifikasi
                </button>
                <button type="button"
                        @click="filterAksi = 'hapus'; currentPage = 1"
                        :class="filterAksi === 'hapus' ? 'bg-tinggi text-white' : 'bg-paper text-muted hover:text-ink border border-line'"
                        class="px-3 py-1.5 rounded-lg font-bold transition-colors">
                    Hapus
                </button>
            </div>

            {{-- Filter Pengguna, Tanggal, & Pencarian --}}
            <div class="flex flex-wrap items-center gap-3">
                {{-- Dropdown Pengguna --}}
                <div class="w-full sm:w-44">
                    <select x-model="filterPengguna" @change="currentPage = 1" class="input-select !min-h-[38px] !py-1 text-xs font-medium">
                        <option value="semua">Semua Pengguna</option>
                        <option value="Jhoni Arafat">Jhoni Arafat (Admin)</option>
                        <option value="Harpiyansa">Harpiyansa (Operator 1)</option>
                        <option value="Kusnaeni">Kusnaeni (Operator 2)</option>
                    </select>
                </div>

                {{-- Filter Tanggal --}}
                <div class="w-full sm:w-36">
                    <input type="date"
                           x-model="filterTanggal"
                           @change="currentPage = 1"
                           class="input-text !min-h-[38px] !py-1 text-xs text-ink font-medium">
                </div>

                {{-- Cari Nama Responden / Pengguna --}}
                <div class="w-full sm:w-48">
                    <input type="text"
                           x-model="searchKk"
                           @input="currentPage = 1"
                           placeholder="Cari warga / aksi..."
                           class="input-text !min-h-[38px] !py-1 text-xs">
                </div>

                {{-- Reset Filter --}}
                <button type="button"
                        x-show="filterAksi !== 'semua' || filterPengguna !== 'semua' || filterTanggal !== '' || searchKk !== ''"
                        @click="resetFilter"
                        class="text-xs text-tinggi font-bold hover:underline py-1.5 px-2">
                    Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- ============================================================
         TABEL LOG AUDIT TRAIL (PRD 11.5)
         ============================================================ --}}
    <div class="card overflow-hidden !p-0 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-paper border-b border-line text-xs font-semibold uppercase tracking-wider text-muted">
                    <tr>
                        <th class="py-3.5 px-4 w-44">Waktu (WIT)</th>
                        <th class="py-3.5 px-4 w-48">Pengguna</th>
                        <th class="py-3.5 px-4 w-28">Aksi</th>
                        <th class="py-3.5 px-4">Data Terkait</th>
                        <th class="py-3.5 px-4 text-right w-28">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    <template x-for="log in paginatedLogs" :key="log.id">
                        <tr class="hover:bg-paper/40 transition-colors">
                            {{-- Waktu --}}
                            <td class="py-3.5 px-4 text-xs font-mono text-muted tabular-nums whitespace-nowrap"
                                x-text="log.waktu"></td>

                            {{-- Pengguna & Peran --}}
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-ink block text-xs" x-text="log.pengguna"></span>
                                <span class="text-[11px] text-muted block" x-text="log.peran"></span>
                            </td>

                            {{-- Lencana Aksi (PRD 11.5: Tambah netral, Ubah biru abu, Verifikasi rendah, Hapus tinggi) --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <template x-if="log.aksi === 'tambah'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-paper text-ink border border-line">
                                        Tambah
                                    </span>
                                </template>
                                <template x-if="log.aksi === 'ubah'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                        Ubah
                                    </span>
                                </template>
                                <template x-if="log.aksi === 'verifikasi'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rendah-bg text-rendah-tx border border-rendah/30">
                                        Verifikasi
                                    </span>
                                </template>
                                <template x-if="log.aksi === 'hapus' || log.aksi === 'delete'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-tinggi-bg text-tinggi-tx border border-tinggi/30">
                                        Hapus
                                    </span>
                                </template>
                            </td>

                            {{-- Data Terkait & Keterangan --}}
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-ink text-xs flex items-center gap-1.5">
                                    <span x-text="log.dataTerkait"></span>
                                    <span class="text-[11px] font-normal text-muted" x-text="'(' + log.jalur + ')'"></span>
                                </div>
                                <p class="text-[11px] text-help mt-0.5 truncate max-w-md" x-text="log.keterangan"></p>
                            </td>

                            {{-- Tombol Buka Modal Detail --}}
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <button type="button"
                                        @click="openDetail(log)"
                                        class="text-xs font-bold text-brand hover:underline flex items-center gap-1 ml-auto">
                                    <span>Detail</span>
                                    <span>→</span>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Keadaan Kosong (Empty State saat Filter Nihil) (PRD 13.3) --}}
        <div x-show="filteredLogs.length === 0" class="p-8 text-center" style="display: none;">
            <x-empty-state
                icon="filter"
                title="Tidak ada riwayat log yang sesuai"
                description="Tidak ditemukan catatan perubahan data dengan kriteria filter yang Anda pilih."
            />
            <div class="mt-2">
                <button type="button" @click="resetFilter" class="btn-secondary !text-xs !min-h-[38px]">
                    Atur Ulang Filter
                </button>
            </div>
        </div>

        {{-- Footer Paginasi Interaktif --}}
        <div class="p-4 border-t border-line flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs text-muted">
            <span>
                Menampilkan <strong class="text-ink" x-text="paginatedLogs.length"></strong> dari <strong class="text-ink" x-text="filteredLogs.length"></strong> catatan aktivitas
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
         MODAL DETAIL PERBANDINGAN SEBELUM / SESUDAH (PRD 11.5)
         ============================================================ --}}
    <div x-show="modalDetail"
         x-transition.opacity
         class="fixed inset-0 z-50 bg-ink/50 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;"
         x-cloak
         @keydown.escape.window="modalDetail = false">

        <div @click.outside="modalDetail = false"
             class="bg-surface rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col overflow-hidden border border-line">

            {{-- Header Modal --}}
            <div class="p-5 border-b border-line flex items-center justify-between bg-paper/50">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-ink">Perbandingan Perubahan Data</h3>
                        <template x-if="activeLog && activeLog.aksi === 'tambah'">
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-paper text-ink border border-line">Perekaman Baru</span>
                        </template>
                        <template x-if="activeLog && activeLog.aksi === 'ubah'">
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">Modifikasi Nilai</span>
                        </template>
                        <template x-if="activeLog && activeLog.aksi === 'verifikasi'">
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-rendah-bg text-rendah-tx">Verifikasi Lapangan</span>
                        </template>
                        <template x-if="activeLog && activeLog.aksi === 'hapus'">
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-tinggi-bg text-tinggi-tx">Penghapusan</span>
                        </template>
                    </div>
                    <p class="text-help text-xs mt-0.5" x-text="activeLog ? activeLog.dataTerkait + ' (' + activeLog.jalur + ')' : ''"></p>
                </div>
                <button type="button" @click="modalDetail = false" class="text-muted hover:text-ink p-1 rounded-lg hover:bg-paper">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            {{-- Body Modal (Dua Kolom Sebelum / Sesudah dengan Baris Berubah Disorot) --}}
            <div class="p-5 overflow-y-auto space-y-4 text-xs">
                {{-- Metadata Audit Info --}}
                <div class="bg-paper p-3 rounded-lg border border-line grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div>
                        <span class="text-muted block text-[11px]">Waktu Eksekusi:</span>
                        <strong class="text-ink font-mono" x-text="activeLog ? activeLog.waktu : ''"></strong>
                    </div>
                    <div>
                        <span class="text-muted block text-[11px]">Pelaksana (Aktor):</span>
                        <strong class="text-ink" x-text="activeLog ? activeLog.pengguna + ' (' + activeLog.peran + ')' : ''"></strong>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <span class="text-muted block text-[11px]">Catatan / Alasan:</span>
                        <span class="text-ink italic" x-text="activeLog ? activeLog.keterangan : ''"></span>
                    </div>
                </div>

                {{-- Tampilan Perbandingan Dua Kolom (Label - Nilai Mudah Dibaca Sesuai PRD 11.5) --}}
                <template x-if="activeLog && activeLog.sebelum">
                    <div class="space-y-2">
                        <div class="grid grid-cols-2 gap-4 font-bold text-xs border-b border-line pb-2">
                            <div class="text-muted flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-muted"></span> Kondisi Sebelum
                            </div>
                            <div class="text-brand flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-brand"></span> Kondisi Sesudah (Terkini)
                            </div>
                        </div>

                        <div class="divide-y divide-line space-y-1">
                            <template x-for="(valSesudah, key) in (activeLog ? activeLog.sesudah : {})" :key="key">
                                <div class="grid grid-cols-2 gap-4 py-2 text-xs rounded px-2 transition-colors"
                                     :class="(activeLog?.perubahan || []).includes(key) ? 'bg-amber-50/80 border-l-2 border-amber-500 font-medium' : ''">
                                    {{-- Kolom Sebelum --}}
                                    <div>
                                        <span class="text-muted block text-[11px]" x-text="key"></span>
                                        <span class="text-ink"
                                              :class="(activeLog?.perubahan || []).includes(key) ? 'line-through text-muted' : ''"
                                              x-text="(activeLog?.sebelum && activeLog.sebelum[key]) ? activeLog.sebelum[key] : '—'"></span>
                                    </div>

                                    {{-- Kolom Sesudah (Disorot bila berubah) --}}
                                    <div>
                                        <span class="text-muted block text-[11px]" x-text="key"></span>
                                        <span :class="(activeLog?.perubahan || []).includes(key) ? 'font-bold text-ink bg-amber-100 px-1.5 py-0.5 rounded inline-block' : 'text-ink'"
                                              x-text="valSesudah"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Tampilan Perekaman Data Baru (Tidak ada kondisi sebelum) --}}
                <template x-if="activeLog && !activeLog.sebelum">
                    <div class="space-y-3">
                        <div class="p-3 bg-brand/5 border border-brand/20 rounded-lg text-xs text-brand font-semibold flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span>Data baru pertama kali diinput ke dalam sistem. Seluruh field di bawah ini merupakan nilai awal masukan operator.</span>
                        </div>

                        <div class="bg-paper p-4 rounded-xl border border-line divide-y divide-line">
                            <template x-for="(val, key) in (activeLog ? activeLog.sesudah : {})" :key="key">
                                <div class="py-2 flex justify-between items-center text-xs">
                                    <span class="text-muted" x-text="key"></span>
                                    <strong class="text-ink text-right" x-text="val"></strong>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Footer Modal --}}
            <div class="p-4 border-t border-line flex justify-end bg-paper/50">
                <button type="button" @click="modalDetail = false" class="btn-secondary !text-xs !min-h-[38px] px-4 font-bold">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
