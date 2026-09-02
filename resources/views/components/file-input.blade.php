@props([
    'name',
    'accept' => null,
    'maxSize' => 10,
    'label' => 'Pilih file',
    'hint' => 'Klik untuk memilih file',
    'required' => false,
    'multiple' => false,
    'id' => null,
])

@php($inputId = $id ?: 'file-input-' . str_replace(['[', ']'], '-', $name) . '-' . uniqid())

<div data-file-input class="space-y-2">
    <button type="button" data-file-trigger="{{ $inputId }}"
        class="w-full min-h-24 border-2 border-dashed border-sand-200 rounded-md bg-white px-4 py-4 text-center transition-colors hover:border-[#1677B8] hover:bg-[#1677B8]/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1677B8]/40">
        <span class="flex items-center justify-center gap-2 text-xs font-bold text-on-surface">
            <i class="ph ph-upload-simple text-base text-[#1677B8]"></i>
            <span data-file-label>{{ $label }}</span>
        </span>
        <span class="mt-1 block text-[10px] font-medium text-on-surface-variant" data-file-hint>{{ $hint }}</span>
    </button>

    <input id="{{ $inputId }}" type="file" name="{{ $name }}" @if($multiple) multiple @endif @if($accept) accept="{{ $accept }}" @endif
        @required($required) class="sr-only" data-file-input-control>

    <div class="hidden items-center justify-between gap-3 rounded-md border border-[#1677B8]/20 bg-[#1677B8]/5 px-3 py-2" data-file-selected>
        <div class="min-w-0">
            <p class="truncate text-[11px] font-bold text-on-surface" data-file-name></p>
            <p class="text-[10px] text-on-surface-variant" data-file-size></p>
        </div>
        <button type="button" data-file-clear class="shrink-0 text-[10px] font-bold text-red-600 hover:text-red-800 focus-visible:outline-none focus-visible:underline">Hapus</button>
    </div>
    <p class="hidden text-[10px] font-semibold text-red-600" data-file-error role="alert"></p>
</div>

<script>
(() => {
    const root = document.currentScript.previousElementSibling;
    const input = root.querySelector('[data-file-input-control]');
    const trigger = root.querySelector('[data-file-trigger]');
    const selected = root.querySelector('[data-file-selected]');
    const error = root.querySelector('[data-file-error]');
    const maxBytes = {{ (int) $maxSize }} * 1024 * 1024;
    const accept = @json($accept);
    const multiple = @json($multiple);

    const reset = () => {
        input.value = '';
        selected.classList.add('hidden');
        selected.classList.remove('flex');
        error.classList.add('hidden');
    };

    trigger.addEventListener('click', () => input.click());
    root.querySelector('[data-file-clear]').addEventListener('click', reset);
    input.addEventListener('change', () => {
        // Multiple uploads can provide their own list/counter while this component owns the picker UI.
        if (multiple) return;
        const file = input.files?.[0];
        error.classList.add('hidden');
        if (!file) return reset();

        const typeMatches = !accept || accept.split(',').some((type) => {
            const value = type.trim();
            return value === file.type || (value.endsWith('/*') && file.type.startsWith(value.slice(0, -1)));
        });
        if (file.size > maxBytes) return (error.textContent = 'Ukuran file maksimal {{ $maxSize }} MB.', error.classList.remove('hidden'), input.value = '');
        if (!typeMatches) return (error.textContent = 'Format file tidak sesuai.', error.classList.remove('hidden'), input.value = '');

        root.querySelector('[data-file-name]').textContent = file.name;
        root.querySelector('[data-file-size]').textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
        root.querySelector('[data-file-label]').textContent = 'File terpilih';
        root.querySelector('[data-file-hint]').textContent = 'Klik untuk mengganti file';
        selected.classList.remove('hidden');
        selected.classList.add('flex');
    });
})();
</script>
