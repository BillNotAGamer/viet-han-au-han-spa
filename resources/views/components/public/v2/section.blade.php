@props([
    'surface' => 'paper',
    'spacing' => 'default',
])

@php
    $surfaceClass = match ($surface) {
        'ivory' => 'v2-surface--ivory',
        'muted' => 'v2-surface--muted',
        'brand' => 'v2-surface--brand',
        'deep' => 'v2-surface--deep',
        'espresso' => 'v2-surface--espresso',
        default => 'v2-surface--paper',
    };

    $spacingClass = match ($spacing) {
        'compact' => 'v2-section--compact',
        'spacious' => 'v2-section--spacious',
        default => '',
    };
@endphp

<section {{ $attributes->class(['v2-section', $surfaceClass, $spacingClass]) }}>
    {{ $slot }}
</section>
