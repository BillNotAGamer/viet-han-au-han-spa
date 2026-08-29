@php
    $currentLocale = app()->getLocale();
    $targetLocale = $currentLocale === 'vi' ? 'en' : 'vi';
    $switchUrl = \App\Support\Localization::switchLocaleUrl($targetLocale);
@endphp

<div {{ $attributes->merge(['class' => 'public-language-switcher']) }} role="group" aria-label="{{ __('navigation.switch_language') }}">
    @if($currentLocale === 'vi')
        <span class="public-language-switcher__item public-language-switcher__item--active" aria-current="true">VI</span>
        <a href="{{ $switchUrl }}" class="public-language-switcher__item" aria-label="Switch to English">EN</a>
    @else
        <a href="{{ $switchUrl }}" class="public-language-switcher__item" aria-label="Chuyển sang Tiếng Việt">VI</a>
        <span class="public-language-switcher__item public-language-switcher__item--active" aria-current="true">EN</span>
    @endif
</div>
