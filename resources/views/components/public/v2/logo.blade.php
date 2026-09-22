@props([
    'variant' => 'colored',
    'decorative' => false,
    'loading' => 'eager',
])

@php
    $logo = match ($variant) {
        'white' => [
            'path' => 'resources/images/general/viet-han-spa-white-logo.png',
            'class' => 'v2-logo--white',
            'width' => 1254,
            'height' => 1254,
        ],
        'compact' => [
            'path' => 'resources/images/general/viet-han-logo.png',
            'class' => 'v2-logo--compact',
            'width' => 480,
            'height' => 480,
        ],
        default => [
            'path' => 'resources/images/general/viet-han-spa-no-bg-logo.png',
            'class' => 'v2-logo--colored',
            'width' => 1254,
            'height' => 1254,
        ],
    };
@endphp

<img
    src="{{ Vite::asset($logo['path']) }}"
    alt="{{ $decorative ? '' : __('common.brand_name') }}"
    width="{{ $logo['width'] }}"
    height="{{ $logo['height'] }}"
    loading="{{ $loading }}"
    decoding="async"
    @if($decorative) aria-hidden="true" @endif
    {{ $attributes->class(['v2-logo', $logo['class']]) }}
>
