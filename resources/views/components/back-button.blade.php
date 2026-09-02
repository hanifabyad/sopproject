@props([
    'href' => 'javascript:history.back()',
    'text' => 'Kembali',
    'variant' => 'default', // 'default' (white card), 'light' (on blue header), 'dark' (charcoal), 'outline', 'blue'
])

@php
    $baseClasses = "group relative inline-flex items-center justify-center overflow-hidden rounded-md text-xs font-bold transition-all shadow-sm cursor-pointer select-none h-9 px-4 min-w-[105px]";
    
    $variantClasses = match($variant) {
        'light'   => 'bg-white/15 hover:bg-white/25 text-white border border-white/20',
        'dark'    => 'bg-charcoal-900 hover:bg-black text-gold-fixed border border-charcoal-800',
        'outline' => 'bg-transparent hover:bg-sand-100 text-charcoal-900 border border-sand-300',
        'blue'    => 'bg-[#1677B8] hover:bg-[#125d91] text-white border border-[#1677B8]',
        default   => 'bg-white hover:bg-sand-50 text-charcoal-900 border border-sand-200/80',
    };
    
    $sliderBg = match($variant) {
        'light'   => 'bg-white/25 text-[#ffe16e]',
        'dark'    => 'bg-gold-fixed text-charcoal-900',
        'blue'    => 'bg-white/20 text-white',
        'outline' => 'bg-sand-200 text-charcoal-900',
        default   => 'bg-[#1677B8] text-white',
    };
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }}>
    <span class="inline-block translate-x-2 transition-all duration-300 group-hover:opacity-0 group-hover:-translate-x-2 whitespace-nowrap">
        {{ $text }}
    </span>
    <span class="absolute inset-0 z-10 grid w-8 place-items-center {{ $sliderBg }} transition-all duration-300 ease-out group-hover:w-full">
        <i class="ph ph-arrow-left text-sm font-bold transition-transform duration-300 group-hover:-translate-x-0.5"></i>
    </span>
</a>
