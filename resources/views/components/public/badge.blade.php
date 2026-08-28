@props([
    'variant' => 'primary',
])

@php
    $styles = match($variant) {
        'gold' => 'bg-brand-gold/15 text-brand-gold-hover border-brand-gold/30',
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        default => 'bg-brand-primary/10 text-brand-primary border-brand-primary/20',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider border {$styles}"]) }}>
    {{ $slot }}
</span>
