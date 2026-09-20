@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
    'disabled' => false,
])

@php
    $variantClass = match ($variant) {
        'secondary' => 'v2-button--secondary',
        'inverse' => 'v2-button--inverse',
        'icon' => 'v2-button--icon',
        'text' => 'v2-text-link',
        default => 'v2-button--primary',
    };
    $baseClass = $variant === 'text' ? '' : 'v2-button';
@endphp

@if($href !== null)
    <a
        @if(! $disabled) href="{{ $href }}" @endif
        @if($disabled) aria-disabled="true" tabindex="-1" @endif
        {{ $attributes->class([$baseClass, $variantClass]) }}
    >
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($disabled)
        {{ $attributes->class([$baseClass, $variantClass]) }}
    >
        {{ $slot }}
    </button>
@endif
