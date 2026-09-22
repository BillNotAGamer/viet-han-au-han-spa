@props([
    'src',
    'alt' => '',
    'role' => 'landscape',
    'position' => 'center',
    'priority' => false,
    'caption' => null,
])

@php
    $roleClass = match ($role) {
        'cinematic' => 'v2-media-frame--cinematic',
        'portrait' => 'v2-media-frame--portrait',
        'atmosphere' => 'v2-media-frame--atmosphere',
        'collection' => 'v2-media-frame--collection',
        'gallery' => 'v2-media-frame--gallery',
        default => 'v2-media-frame--landscape',
    };
    $positionClass = match ($position) {
        'top' => 'v2-media-frame--position-top',
        'portrait' => 'v2-media-frame--position-portrait',
        default => '',
    };
@endphp

<figure {{ $attributes->class(['v2-media-frame', $roleClass, $positionClass]) }} data-reveal-image>
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        loading="{{ $priority ? 'eager' : 'lazy' }}"
        decoding="async"
        @if($priority) fetchpriority="high" @endif
    >
    @if($caption)
        <figcaption class="v2-media-frame__caption v2-type-caption">{{ $caption }}</figcaption>
    @endif
</figure>
