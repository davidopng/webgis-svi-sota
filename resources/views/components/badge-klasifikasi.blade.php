{{--
    Lencana Klasifikasi Kerentanan (PRD 7.1)
    Menampilkan ikon bentuk + teks klasifikasi dengan warna sesuai tingkat.

    @props: tingkat (rendah|sedang|tinggi)
--}}
@props([
    'tingkat' => 'rendah',
])

@php
    $config = match($tingkat) {
        'rendah' => [
            'bg'    => 'bg-rendah-bg',
            'text'  => 'text-rendah-tx',
            'border'=> 'border-rendah/20',
            'icon'  => '●',
            'label' => 'Kerentanan Rendah',
        ],
        'sedang' => [
            'bg'    => 'bg-sedang-bg',
            'text'  => 'text-sedang-tx',
            'border'=> 'border-sedang/20',
            'icon'  => '▲',
            'label' => 'Kerentanan Sedang',
        ],
        'tinggi' => [
            'bg'    => 'bg-tinggi-bg',
            'text'  => 'text-tinggi-tx',
            'border'=> 'border-tinggi/20',
            'icon'  => '◆',
            'label' => 'Kerentanan Tinggi',
        ],
        default => [
            'bg'    => 'bg-paper',
            'text'  => 'text-muted',
            'border'=> 'border-line',
            'icon'  => '?',
            'label' => 'Tidak diketahui',
        ],
    };
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
    <span aria-hidden="true">{{ $config['icon'] }}</span>
    <span>{{ $config['label'] }}</span>
</span>
