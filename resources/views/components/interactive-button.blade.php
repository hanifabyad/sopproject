@props([
    'text' => 'Simpan',
    'type' => 'submit',
    'variant' => 'primary', // 'primary', 'blue', 'danger', 'success', 'outline'
    'href' => null,
    'icon' => 'ph ph-arrow-right text-sm',
    'dot' => true,
])

@php
    $variantClass = match($variant) {
        'blue' => 'btn-interactive-blue',
        'danger' => 'btn-interactive-danger',
        'success' => 'btn-interactive-success',
        'primary' => 'btn-interactive-primary',
        'outline' => 'btn-interactive-outline',
        default => 'btn-interactive-primary',
    };
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "btn-interactive $variantClass"]) }}>
        <span class="btn-interactive-default">
            @if($dot)<span class="btn-interactive-dot"></span>@endif
            <span>{{ $text }}</span>
        </span>
        <span class="btn-interactive-hover">
            <span>{{ $text }}</span>
            <i class="{{ $icon }}"></i>
        </span>
        <span class="btn-interactive-bg"></span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "btn-interactive $variantClass"]) }}>
        <span class="btn-interactive-default">
            @if($dot)<span class="btn-interactive-dot"></span>@endif
            <span>{{ $text }}</span>
        </span>
        <span class="btn-interactive-hover">
            <span>{{ $text }}</span>
            <i class="{{ $icon }}"></i>
        </span>
        <span class="btn-interactive-bg"></span>
    </button>
@endif
