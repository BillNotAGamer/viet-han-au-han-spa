@props([
    'level' => 2,
    'size' => 'lg',
])

@php
    $level = in_array((int) $level, [1, 2, 3], true) ? (int) $level : 2;
    $sizeClass = match ($size) {
        'xl' => 'v2-type-display-xl',
        'heading-lg' => 'v2-type-heading-lg',
        'heading-md' => 'v2-type-heading-md',
        default => 'v2-type-display-lg',
    };
@endphp

@if($level === 1)
    <h1 {{ $attributes->class([$sizeClass]) }}>{{ $slot }}</h1>
@elseif($level === 3)
    <h3 {{ $attributes->class([$sizeClass]) }}>{{ $slot }}</h3>
@else
    <h2 {{ $attributes->class([$sizeClass]) }}>{{ $slot }}</h2>
@endif
