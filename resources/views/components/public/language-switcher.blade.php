@props([
    'variant' => 'header',
])

@php
    $currentLocale = app()->getLocale();
    $targetLocale = $currentLocale === 'vi' ? 'en' : 'vi';
    $switchUrl = \App\Support\Localization::switchLocaleUrl($targetLocale);
    $currentUrl = request()->url();
    $page = max(1, (int) request()->query('page', 1));

    if ($page > 1) {
        $pageQuery = '?'.http_build_query(['page' => $page]);
        $currentUrl .= $pageQuery;
        $switchUrl .= $pageQuery;
    }
@endphp

@if($variant === 'footer')
<nav {{ $attributes->class(['v2-language-switcher']) }} aria-label="{{ __('navigation.switch_language') }}">
    <a href="{{ $currentLocale === 'vi' ? $currentUrl : $switchUrl }}" class="{{ $currentLocale === 'vi' ? 'v2-language-switcher__link v2-language-switcher__link--active' : 'v2-language-switcher__link' }}" @if($currentLocale === 'vi') aria-current="page" @endif aria-label="Tiếng Việt">VI</a>
    <span class="v2-language-switcher__separator" aria-hidden="true">/</span>
    <a href="{{ $currentLocale === 'en' ? $currentUrl : $switchUrl }}" class="{{ $currentLocale === 'en' ? 'v2-language-switcher__link v2-language-switcher__link--active' : 'v2-language-switcher__link' }}" @if($currentLocale === 'en') aria-current="page" @endif aria-label="English">EN</a>
</nav>
@else
<div {{ $attributes->merge(['class' => 'public-language-switcher']) }} role="group" aria-label="{{ __('navigation.switch_language') }}">
    @if($currentLocale === 'vi')
        <span class="public-language-switcher__item public-language-switcher__item--active" aria-current="true">VI</span>
        <a href="{{ $switchUrl }}" class="public-language-switcher__item" aria-label="Switch to English">EN</a>
    @else
        <a href="{{ $switchUrl }}" class="public-language-switcher__item" aria-label="Chuyển sang Tiếng Việt">VI</a>
        <span class="public-language-switcher__item public-language-switcher__item--active" aria-current="true">EN</span>
    @endif
</div>
@endif
