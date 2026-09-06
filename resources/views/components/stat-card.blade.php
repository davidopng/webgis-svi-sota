{{--
    Kartu Metrik (PRD 6.5)
    Menampilkan angka besar + label + deskripsi.

    @props: value, label, description, color (brand|rendah|sedang|tinggi), icon (svg slot), href
--}}
@props([
    'value' => '0',
    'label' => 'Label',
    'description' => '',
    'color' => 'brand',
    'href' => null,
])

@php
    $colorMap = [
        'brand'  => 'text-brand',
        'rendah' => 'text-rendah',
        'sedang' => 'text-sedang',
        'tinggi' => 'text-tinggi',
    ];
    $textColor = $colorMap[$color] ?? 'text-brand';
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    class="card flex flex-col gap-2 {{ $href ? 'cursor-pointer hover:border-brand/30 transition-colors' : '' }}"
>
    <p class="text-label text-muted">{{ $label }}</p>

    <p class="text-metric {{ $textColor }}">{{ $value }}</p>

    @if($description)
        <p class="text-help">{{ $description }}</p>
    @endif

    {{-- Slot untuk ikon atau konten tambahan --}}
    @if($slot->isNotEmpty())
        <div class="mt-1">
            {{ $slot }}
        </div>
    @endif
</{{ $tag }}>
