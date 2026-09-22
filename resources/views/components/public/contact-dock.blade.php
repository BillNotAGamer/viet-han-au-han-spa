@props([
    'motionHook' => false,
])

@php
    $fbIcon = Vite::asset('resources/images/general/icons/Facebook.png');
    $msgIcon = Vite::asset('resources/images/general/icons/messenger.png');
    $zaloIcon = Vite::asset('resources/images/general/icons/zalo.png');
    $phoneIcon = Vite::asset('resources/images/general/icons/phone.png');

    $isEn = app()->getLocale() === 'en';
    $callText = $isEn ? 'Call now' : 'Gọi ngay';
    $phoneAria = $isEn ? 'Call hotline Việt Hàn Âu Hàn Spa' : 'Gọi hotline Việt Hàn Âu Hàn Spa';
@endphp

<nav class="contact-dock" @if($motionHook) data-persistent-ui @endif aria-label="{{ $isEn ? 'Quick contact channels' : 'Kênh liên hệ nhanh' }}">
    <ul class="contact-dock__list">
        {{-- 1. Facebook --}}
        <li class="contact-dock__item">
            <a
                href="https://www.facebook.com/profile.php?id=61575606630966"
                target="_blank"
                rel="noopener noreferrer"
                class="contact-dock__button contact-dock__button--facebook"
                aria-label="Facebook Việt Hàn Âu Hàn Spa"
            >
                <span class="contact-dock__halo contact-dock__halo--facebook" aria-hidden="true"></span>
                <span class="contact-dock__surface contact-dock__surface--facebook">
                    <img src="{{ $fbIcon }}" alt="" class="contact-dock__icon contact-dock__icon--facebook" loading="lazy" width="200" height="200">
                </span>
                <span class="contact-dock__tooltip" role="tooltip" aria-hidden="true">Facebook</span>
            </a>
        </li>

        {{-- 2. Messenger --}}
        <li class="contact-dock__item">
            <a
                href="https://www.facebook.com/profile.php?id=61575606630966"
                target="_blank"
                rel="noopener noreferrer"
                class="contact-dock__button contact-dock__button--messenger"
                aria-label="Messenger Việt Hàn Âu Hàn Spa"
            >
                <span class="contact-dock__halo contact-dock__halo--messenger" aria-hidden="true"></span>
                <span class="contact-dock__surface contact-dock__surface--messenger">
                    <img src="{{ $msgIcon }}" alt="" class="contact-dock__icon contact-dock__icon--messenger" loading="lazy" width="200" height="200">
                </span>
                <span class="contact-dock__tooltip" role="tooltip" aria-hidden="true">Messenger</span>
            </a>
        </li>

        {{-- 3. Zalo --}}
        <li class="contact-dock__item">
            <a
                href="https://zalo.me/0902309026"
                target="_blank"
                rel="noopener noreferrer"
                class="contact-dock__button contact-dock__button--zalo"
                aria-label="Zalo Việt Hàn Âu Hàn Spa"
            >
                <span class="contact-dock__halo contact-dock__halo--zalo" aria-hidden="true"></span>
                <span class="contact-dock__surface contact-dock__surface--zalo">
                    <img src="{{ $zaloIcon }}" alt="" class="contact-dock__icon contact-dock__icon--zalo" loading="lazy" width="100" height="95">
                </span>
                <span class="contact-dock__tooltip" role="tooltip" aria-hidden="true">Zalo</span>
            </a>
        </li>

        {{-- 4. Hotline --}}
        <li class="contact-dock__item">
            <a
                href="tel:0902309026"
                class="contact-dock__button contact-dock__button--phone"
                aria-label="{{ $phoneAria }}"
            >
                <span class="contact-dock__halo contact-dock__halo--phone" aria-hidden="true"></span>
                <span class="contact-dock__surface contact-dock__surface--phone">
                    <img src="{{ $phoneIcon }}" alt="" class="contact-dock__icon contact-dock__icon--phone" loading="lazy" width="50" height="50">
                </span>
                <span class="contact-dock__tooltip" role="tooltip" aria-hidden="true">{{ $callText }}</span>
            </a>
        </li>
    </ul>
</nav>
