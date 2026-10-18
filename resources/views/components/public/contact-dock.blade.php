@php
    $fbIcon = Vite::asset('resources/images/general/icons/Facebook.png');
    $msgIcon = Vite::asset('resources/images/general/icons/messenger.png');
    $zaloIcon = Vite::asset('resources/images/general/icons/zalo.png');
    $phoneIcon = Vite::asset('resources/images/general/icons/phone.png');

    $isEn = app()->getLocale() === 'en';
    $callText = $isEn ? 'Call now' : 'Gọi ngay';
    $phoneAria = $isEn ? 'Call hotline Việt Hàn Âu Hàn Spa' : 'Gọi hotline Việt Hàn Âu Hàn Spa';
@endphp

<nav class="contact-dock" aria-label="{{ $isEn ? 'Quick contact channels' : 'Kênh liên hệ nhanh' }}">
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
                    <svg class="contact-dock__icon contact-dock__icon--facebook" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
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
                    <svg class="contact-dock__icon contact-dock__icon--messenger" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                        <path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.654V24l4.088-2.242c1.092.303 2.246.464 3.443.464 6.627 0 12-4.975 12-11.111C24 4.974 18.627 0 12 0zm1.191 14.963l-3.056-3.259-5.963 3.259 6.559-6.963 3.13 3.259 5.89-3.259-6.56 6.963z"/>
                    </svg>
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
