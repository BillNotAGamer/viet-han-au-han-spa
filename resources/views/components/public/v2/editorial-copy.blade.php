@props([
    'size' => 'body',
])

@php
    $sizeClass = match ($size) {
        'lg' => 'v2-type-body-lg',
        'sm' => 'v2-type-body-sm',
        default => 'v2-type-body',
    };
@endphp

<div {{ $attributes->class([$sizeClass]) }}>
    {{ $slot }}
</div>
