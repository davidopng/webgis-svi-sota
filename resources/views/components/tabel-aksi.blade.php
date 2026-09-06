{{--
    Tabel Aksi / Dropdown Menu (PRD 11.2)
    Menu dropdown ⋮ untuk aksi pada baris tabel.

    Penggunaan:
    <x-tabel-aksi>
        <x-tabel-aksi-item href="/admin/data/1/edit" icon="edit">Ubah data</x-tabel-aksi-item>
        <x-tabel-aksi-item @click="..." icon="trash" variant="danger">Hapus data</x-tabel-aksi-item>
    </x-tabel-aksi>
--}}
@props([])

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open"
            @click.outside="open = false"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-muted hover:bg-paper hover:text-ink transition-colors"
            aria-label="Aksi lainnya"
            aria-haspopup="true"
            :aria-expanded="open">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="5" r="1"/><circle cx="12" cy="19" r="1"/></svg>
    </button>

    <div x-show="open"
         x-transition:enter="transition duration-100 ease-out"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition duration-75 ease-in"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 top-full z-20 mt-1 w-44 rounded-lg border border-line bg-surface p-1 shadow-[var(--shadow-modal)]"
         x-cloak>
        {{ $slot }}
    </div>
</div>
