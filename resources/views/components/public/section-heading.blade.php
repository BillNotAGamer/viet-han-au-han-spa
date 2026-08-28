@props([
    'eyebrow' => null,
    'title' => null,
    'subtitle' => null,
    'align' => 'center',
])

@php
    $alignClass = match($align) {
        'left' => 'text-left',
        'right' => 'text-right',
        default => 'text-center mx-auto',
    };
@endphp

<div {{ $attributes->merge(['class' => "max-w-3xl mb-12 sm:mb-16 {$alignClass}"]) }}>
    @if($eyebrow)
        <span class="inline-block text-xs sm:text-sm font-semibold tracking-widest uppercase text-brand-gold-hover mb-3">
            {{ $eyebrow }}
        </span>
    @endif

    @if($title)
        <h2 class="font-serif text-2xl sm:text-4xl lg:text-5xl font-bold tracking-tight text-brand-primary mb-4 leading-tight break-words [text-wrap:balance]">
            {{ $title }}
        </h2>
    @endif

    @if($subtitle)
        <p class="text-base sm:text-lg text-brand-text-secondary leading-relaxed break-words">
            {{ $subtitle }}
        </p>
    @endif

    {{ $slot }}
</div>
