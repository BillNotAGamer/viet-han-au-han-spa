@php
    $currentLocale = app()->getLocale();
    $targetLocale = $currentLocale === 'vi' ? 'en' : 'vi';
    $switchUrl = \App\Support\Localization::switchLocaleUrl($targetLocale);
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center p-1 rounded-full bg-brand-warm border border-brand-border text-xs font-semibold uppercase tracking-wider']) }} role="group" aria-label="{{ __('navigation.switch_language') }}">
    @if($currentLocale === 'vi')
        <span class="px-2.5 py-1 rounded-full bg-brand-primary text-white shadow-xs" aria-current="true">VI</span>
        <a href="{{ $switchUrl }}" class="px-2.5 py-1 rounded-full text-brand-text-secondary hover:text-brand-primary hover:bg-brand-ivory transition" aria-label="Switch to English">EN</a>
    @else
        <a href="{{ $switchUrl }}" class="px-2.5 py-1 rounded-full text-brand-text-secondary hover:text-brand-primary hover:bg-brand-ivory transition" aria-label="Chuyển sang Tiếng Việt">VI</a>
        <span class="px-2.5 py-1 rounded-full bg-brand-primary text-white shadow-xs" aria-current="true">EN</span>
    @endif
</div>
