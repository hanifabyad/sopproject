@props([
    'href' => null,
    'icon' => null,
    'danger' => false,
])

@php
$classes = 'm3-item-enter group relative flex items-center gap-2.5 w-full px-4 py-2.5 text-xs font-semibold tracking-wide transition-all outline-none cursor-pointer ' . 
           ($danger 
               ? 'text-rose-600 hover:bg-rose-50/80 active:scale-[0.98]' 
               : 'text-slate-700 hover:bg-slate-100/80 hover:text-[#1677B8] active:scale-[0.98]');
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="{{ $icon }} text-base opacity-75 group-hover:opacity-100 transition-opacity flex-shrink-0"></i>
        @endif
        <span class="truncate">{{ $slot }}</span>
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes . ' border-none bg-transparent text-left']) }}>
        @if($icon)
            <i class="{{ $icon }} text-base opacity-75 group-hover:opacity-100 transition-opacity flex-shrink-0"></i>
        @endif
        <span class="truncate">{{ $slot }}</span>
    </button>
@endif
