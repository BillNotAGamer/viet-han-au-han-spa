@props([
    'variant' => 'default',
])

@php
    $styles = match($variant) {
        'warm' => 'bg-brand-warm border-brand-border',
        'primary' => 'bg-brand-primary text-white border-brand-primary-light',
        default => 'bg-brand-surface border-brand-border',
    };
@endphp

<div {{ $attributes->merge(['class' => "rounded-2xl border {$styles} p-6 sm:p-8 shadow-xs transition duration-200 hover:shadow-md"]) }}>
    {{ $slot }}
</div>
