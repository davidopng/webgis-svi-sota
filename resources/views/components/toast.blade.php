{{--
    Toast Notification (PRD 13.1)
    Muncul di kanan atas (desktop) / atas layar (mobile).
    Tiga jenis: berhasil (rendah), peringatan (sedang), gagal (tinggi).
    Hilang otomatis setelah 5 detik, dapat ditutup manual.

    Penggunaan:
    <x-toast type="berhasil" message="Data berhasil disimpan." />

    Untuk penggunaan dinamis via Alpine, lihat contoh di bawah.
--}}
@props([
    'type' => 'berhasil',
    'message' => '',
])

@php
    $config = match($type) {
        'berhasil' => [
            'bg'     => 'bg-rendah-bg',
            'border' => 'border-rendah/30',
            'text'   => 'text-rendah-tx',
            'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>',
        ],
        'peringatan' => [
            'bg'     => 'bg-sedang-bg',
            'border' => 'border-sedang/30',
            'text'   => 'text-sedang-tx',
            'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>',
        ],
        'gagal' => [
            'bg'     => 'bg-tinggi-bg',
            'border' => 'border-tinggi/30',
            'text'   => 'text-tinggi-tx',
            'icon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>',
        ],
        default => [
            'bg'     => 'bg-paper',
            'border' => 'border-line',
            'text'   => 'text-ink',
            'icon'   => '',
        ],
    };
@endphp

<div x-data="{ show: true }"
     x-init="setTimeout(() => show = false, 5000)"
     x-show="show"
     x-transition:enter="transition duration-200 ease-out"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition duration-150 ease-in"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-2"
     class="flex items-start gap-3 rounded-lg border p-4 shadow-[var(--shadow-card)] max-w-[400px] {{ $config['bg'] }} {{ $config['border'] }}"
     role="alert">

    {{-- Ikon --}}
    <span class="{{ $config['text'] }} flex-shrink-0 mt-0.5" aria-hidden="true">
        {!! $config['icon'] !!}
    </span>

    {{-- Pesan --}}
    <p class="flex-1 text-sm font-medium {{ $config['text'] }}">
        {{ $message }}
    </p>

    {{-- Tombol tutup --}}
    <button @click="show = false"
            class="{{ $config['text'] }} flex-shrink-0 rounded-md p-0.5 hover:opacity-70 transition-opacity"
            aria-label="Tutup notifikasi">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>
</div>
