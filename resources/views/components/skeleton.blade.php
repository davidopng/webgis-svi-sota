{{--
    Skeleton Component (PRD 13.4)
    Rangka abu-abu animasi shimmer untuk pemuatan kartu, tabel, dan grafik.

    @props: type ('text', 'card', 'table-row', 'chart'), lines (default 3), height (default 'h-4')
--}}
@props([
    'type' => 'text',
    'lines' => 3,
    'height' => 'h-4',
    'width' => 'w-full'
])

@if($type === 'card')
    <div {{ $attributes->merge(['class' => 'card space-y-4 p-5 animate-pulse']) }}>
        <div class="flex items-center justify-between">
            <div class="skeleton h-4 w-32"></div>
            <div class="skeleton h-4 w-12 rounded-full"></div>
        </div>
        <div class="skeleton h-8 w-20"></div>
        <div class="skeleton h-3 w-40"></div>
    </div>
@elseif($type === 'table-row')
    <tr {{ $attributes->merge(['class' => 'border-b border-line']) }}>
        <td class="py-3 px-4"><div class="skeleton h-4 w-28"></div></td>
        <td class="py-3 px-4"><div class="skeleton h-4 w-20"></div></td>
        <td class="py-3 px-4"><div class="skeleton h-4 w-16"></div></td>
        <td class="py-3 px-4 text-center"><div class="skeleton h-4 w-8 mx-auto"></div></td>
        <td class="py-3 px-4"><div class="skeleton h-5 w-24 rounded-full"></div></td>
        <td class="py-3 px-4 text-right"><div class="skeleton h-6 w-14 ml-auto rounded"></div></td>
    </tr>
@elseif($type === 'chart')
    <div {{ $attributes->merge(['class' => 'card p-5 space-y-4']) }}>
        <div class="flex justify-between items-center pb-2 border-b border-line">
            <div class="skeleton h-5 w-48"></div>
            <div class="skeleton h-4 w-16"></div>
        </div>
        <div class="skeleton h-52 w-full rounded-lg"></div>
        <div class="flex justify-center gap-4 pt-2">
            <div class="skeleton h-4 w-20"></div>
            <div class="skeleton h-4 w-20"></div>
            <div class="skeleton h-4 w-20"></div>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'space-y-2.5']) }}>
        @for($i = 0; $i < $lines; $i++)
            <div class="skeleton {{ $height }} {{ $i === $lines - 1 ? 'w-2/3' : $width }}"></div>
        @endfor
    </div>
@endif
