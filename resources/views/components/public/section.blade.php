@props([
    'bg' => 'ivory',
    'spacing' => 'default',
])

@php
    $bgStyles = match($bg) {
        'warm' => 'bg-brand-warm',
        'white' => 'bg-brand-surface',
        default => 'bg-brand-ivory',
    };

    $spacingStyles = match($spacing) {
        'compact' => 'py-8 sm:py-12',
        'spacious' => 'py-16 sm:py-24 lg:py-32',
        default => 'py-12 sm:py-16 lg:py-20',
    };
@endphp

<section {{ $attributes->merge(['class' => "w-full {$bgStyles} {$spacingStyles}"]) }}>
    {{ $slot }}
</section>
