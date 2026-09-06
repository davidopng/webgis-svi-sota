{{--
    Modal (PRD 13.2)
    Latar gelap ink 50%, kotak putih terpusat, radius 10px.
    Tutup dengan ×, Batal, Esc, atau klik latar — kecuali $persistent.
    Fokus terkunci di dalam modal selama terbuka.

    Penggunaan:
    <x-modal name="konfirmasi-hapus" max-width="md">
        <h2>Apakah Anda yakin?</h2>
        ...
    </x-modal>

    Buka dengan: $dispatch('open-modal', 'konfirmasi-hapus')
    Tutup dengan: $dispatch('close-modal', 'konfirmasi-hapus')
--}}
@props([
    'name' => 'default',
    'maxWidth' => 'lg',
    'persistent' => false,
])

@php
    $widthClass = match($maxWidth) {
        'sm'  => 'max-w-sm',
        'md'  => 'max-w-md',
        'lg'  => 'max-w-lg',
        'xl'  => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        'validation' => 'max-w-[1100px]',
        default => 'max-w-lg',
    };
@endphp

<div x-data="{ open: false }"
     x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
     x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
     @if(!$persistent)
         x-on:keydown.escape.window="if (open) open = false"
     @endif
     x-show="open"
     class="fixed inset-0 z-[80] flex items-center justify-center p-4"
     style="display: none;"
     x-cloak>

    {{-- Overlay --}}
    <div x-show="open"
         x-transition:enter="transition duration-200 ease-out"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-150 ease-in"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @if(!$persistent) @click="open = false" @endif
         class="absolute inset-0 bg-ink/50"></div>

    {{-- Panel --}}
    <div x-show="open"
         x-transition:enter="transition duration-200 ease-out"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition duration-150 ease-in"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-trap.noscroll.inert="open"
         class="relative w-full {{ $widthClass }} rounded-[10px] bg-surface shadow-[var(--shadow-modal)] overflow-y-auto max-h-[90vh]"
         role="dialog"
         aria-modal="true"
         aria-labelledby="modal-title-{{ $name }}">

        {{-- Tombol tutup --}}
        @unless($persistent)
            <button @click="open = false"
                    class="absolute top-4 right-4 z-10 inline-flex h-8 w-8 items-center justify-center rounded-md text-muted hover:bg-paper hover:text-ink transition-colors"
                    aria-label="Tutup">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        @endunless

        {{-- Konten modal --}}
        <div class="p-6">
            {{ $slot }}
        </div>
    </div>
</div>
