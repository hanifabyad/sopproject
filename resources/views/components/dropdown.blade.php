@props([
    'align' => 'right',
    'width' => '56',
    'contentClasses' => 'py-1.5 bg-white/95 backdrop-blur-xl border border-slate-200/80 shadow-2xl rounded-xl overflow-hidden',
])

@php
switch ($align) {
    case 'left':
        $alignmentClasses = 'origin-top-left left-0';
        break;
    case 'center':
        $alignmentClasses = 'origin-top left-1/2 -translate-x-1/2';
        break;
    case 'right':
    default:
        $alignmentClasses = 'origin-top-right right-0';
        break;
}

switch ($width) {
    case '48':
        $width = 'w-48';
        break;
    case '56':
        $width = 'w-56';
        break;
    case '64':
        $width = 'w-64';
        break;
    case 'auto':
        $width = 'w-auto min-w-[14rem]';
        break;
    default:
        $width = $width;
        break;
}
@endphp

<div class="relative inline-block text-left m3-dropdown-wrapper" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open" class="cursor-pointer">
        {{ $trigger }}
    </div>

    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
         class="m3-content m3-dropdown-menu absolute z-50 mt-2 {{ $width }} rounded-xl {{ $alignmentClasses }}"
         style="display: none;"
         @click="open = false">
        <div class="{{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
