@props([
    'as' => 'button',
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $baseStyles = 'inline-flex items-center justify-center font-semibold tracking-wide rounded-xl transition duration-150 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'bg-brand-primary text-white hover:bg-brand-primary-hover active:bg-brand-primary-hover focus-visible:ring-brand-primary shadow-sm',
        'secondary' => 'border border-brand-primary text-brand-primary bg-transparent hover:bg-brand-primary hover:text-white focus-visible:ring-brand-primary',
        'gold' => 'bg-brand-gold text-brand-text hover:bg-brand-gold-hover focus-visible:ring-brand-gold shadow-sm font-semibold',
        'outline-gold' => 'border border-brand-gold text-brand-gold-hover hover:bg-brand-gold hover:text-brand-text focus-visible:ring-brand-gold',
        'text' => 'text-brand-primary hover:text-brand-primary-hover hover:underline p-0 focus-visible:ring-brand-primary',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-base font-semibold',
        'md' => 'px-6 py-2.5 text-[17px] sm:text-[18px] font-semibold',
        'lg' => 'px-8 py-3.5 text-lg font-semibold',
    ];

    $classes = "{$baseStyles} " . ($variants[$variant] ?? $variants['primary']) . " " . ($variant === 'text' ? '' : ($sizes[$size] ?? $sizes['md']));
@endphp

@if($as === 'a')
    <a {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
