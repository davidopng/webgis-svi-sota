{{--
    Radio Kartu SVI (PRD 10.4)
    Satu variabel SVI = satu komponen.
    Radio kartu penuh lebar, klik seluruh area.
    Kartu terpilih: batas brand 2px + latar brand/6%.
    Tanpa angka skor yang terlihat.

    @props:
        name     - nama field (misal: pendapatan)
        label    - teks pertanyaan
        number   - nomor urut pertanyaan (1-9)
        options  - array of ['value' => '...', 'text' => '...']
        value    - nilai terpilih saat ini (untuk mode edit)
        required - apakah wajib
        helpText - teks opsional di bawah pertanyaan
--}}
@props([
    'name' => '',
    'label' => '',
    'number' => '',
    'options' => [],
    'value' => null,
    'required' => true,
    'helpText' => null,
])

<fieldset class="space-y-3" id="field-{{ $name }}">
    <legend class="flex items-start justify-between gap-4">
        <span class="text-body font-medium text-ink">
            {{ $number }}. {{ $label }}
            @if($required)
                <span class="text-tinggi" aria-label="wajib diisi">*</span>
            @endif
        </span>
        @if($number)
            <span class="flex-shrink-0 text-xs font-medium text-muted bg-paper rounded-md px-2 py-1">
                {{ $number }}/9
            </span>
        @endif
    </legend>

    @if($helpText)
        <p class="text-help">{{ $helpText }}</p>
    @endif

    <div class="space-y-2">
        @foreach($options as $index => $option)
            <label class="group relative flex cursor-pointer items-center gap-3 rounded-lg border p-4 transition-all
                          {{ $value === ($option['value'] ?? $index)
                              ? 'border-brand border-2 bg-brand/[0.06]'
                              : 'border-line hover:border-muted hover:bg-paper' }}"
                   style="min-height: 52px;"
                   x-data
                   @click="$el.querySelector('input').checked = true; $el.querySelector('input').dispatchEvent(new Event('change', { bubbles: true }))">

                <input type="radio"
                       name="{{ $name }}"
                       value="{{ $option['value'] ?? $index }}"
                       @if($value === ($option['value'] ?? $index)) checked @endif
                       @if($required) required @endif
                       class="h-4 w-4 flex-shrink-0 border-line text-brand focus:ring-brand/25 focus:ring-offset-0">

                <span class="text-body text-ink">{{ $option['text'] }}</span>
            </label>
        @endforeach
    </div>

    {{-- Error message slot --}}
    <div class="input-error-message hidden" data-error-for="{{ $name }}"></div>
</fieldset>
