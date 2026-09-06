{{--
    Empty State (PRD 13.3)
    Ditampilkan saat tabel atau daftar kosong.

    @props: title, description, actionText, actionUrl, icon (opsional)
--}}
@props([
    'title' => 'Tidak ada data',
    'description' => '',
    'actionText' => null,
    'actionUrl' => null,
    'icon' => 'inbox',
])

<div class="flex flex-col items-center justify-center py-16 px-6 text-center">
    {{-- Ikon --}}
    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-paper text-muted">
        @switch($icon)
            @case('inbox')
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                @break
            @case('search')
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                @break
            @case('filter')
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                @break
            @case('shield')
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                @break
            @default
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
        @endswitch
    </div>

    {{-- Judul --}}
    <h3 class="text-section-title text-ink mb-1">{{ $title }}</h3>

    {{-- Deskripsi --}}
    @if($description)
        <p class="text-help mb-6 max-w-sm">{{ $description }}</p>
    @endif

    {{-- Tombol aksi --}}
    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn-primary">
            {{ $actionText }}
        </a>
    @endif
</div>
