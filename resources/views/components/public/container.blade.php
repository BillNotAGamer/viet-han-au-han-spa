@props(['size' => 'default'])

@php
    $maxWidth = match($size) {
        'sm' => 'max-w-4xl',
        'lg' => 'max-w-6xl',
        'full' => 'max-w-7xl',
        default => 'max-w-7xl',
    };
@endphp

<div {{ $attributes->merge(['class' => "{$maxWidth} mx-auto px-4 sm:px-6 lg:px-8"]) }}>
    {{ $slot }}
</div>
