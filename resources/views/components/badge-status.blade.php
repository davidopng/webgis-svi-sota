{{--
    Lencana Status Verifikasi (PRD 7.2)
    Hanya dua status: menunggu_verifikasi dan terverifikasi.

    @props: status (menunggu_verifikasi|terverifikasi)
--}}
@props([
    'status' => 'menunggu_verifikasi',
])

@php
    $config = match($status) {
        'menunggu_verifikasi' => [
            'bg'    => 'bg-sedang-bg',
            'text'  => 'text-sedang-tx',
            'border'=> 'border-sedang/30',
            'label' => 'Menunggu verifikasi',
        ],
        'terverifikasi' => [
            'bg'    => 'bg-rendah-bg',
            'text'  => 'text-rendah-tx',
            'border'=> 'border-rendah/30',
            'label' => 'Terverifikasi',
        ],
        default => [
            'bg'    => 'bg-paper',
            'text'  => 'text-muted',
            'border'=> 'border-line',
            'label' => 'Tidak diketahui',
        ],
    };
@endphp

<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
    {{ $config['label'] }}
</span>
