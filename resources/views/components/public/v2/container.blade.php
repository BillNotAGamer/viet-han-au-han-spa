@props([
    'size' => 'standard',
])

@php
    $sizeClass = match ($size) {
        'reading' => 'v2-container--reading',
        'wide' => 'v2-container--wide',
        'full' => 'v2-container--full',
        default => '',
    };
@endphp

<div {{ $attributes->class(['v2-container', $sizeClass]) }}>
    {{ $slot }}
</div>
