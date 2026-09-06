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

<div {{ $attributes->merge(['class' => "max-w-3xl mb-8 sm:mb-12 {$alignClass}"]) }}>
    @if($eyebrow)
        <span class="inline-block text-[15px] font-semibold tracking-[0.1em] uppercase text-brand-gold-hover mb-2.5">
            {{ $eyebrow }}
        </span>
    @endif

    @if($title)
        <h2 class="text-2xl sm:text-3xl lg:text-[clamp(2.25rem,3.2vw,3.25rem)] font-semibold tracking-[-0.02em] text-brand-primary mb-4 sm:mb-5 leading-[1.10] break-words [text-wrap:balance]">
            {{ $title }}
        </h2>
    @endif

    @if($subtitle)
        <p class="text-[18px] sm:text-[19px] font-medium text-brand-text-secondary leading-[1.66] break-words">
            {{ $subtitle }}
        </p>
    @endif

    {{ $slot }}
</div>
