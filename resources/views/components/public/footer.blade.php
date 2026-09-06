@inject('siteSettings', 'App\Services\Settings\SiteSettings')

@php
    $isVi = app()->getLocale() === 'vi';
    $homeUrl = $isVi ? route('vi.home') : route('en.home');
    $aboutUrl = $isVi ? route('vi.about') : route('en.about');
    $servicesUrl = $isVi ? route('vi.services.index') : route('en.services.index');
    $trainingUrl = $isVi ? route('vi.training.index') : route('en.training.index');
    $blogUrl = $isVi ? route('vi.blog.index') : route('en.blog.index');
    $contactUrl = $isVi ? route('vi.contact') : route('en.contact');
    $bookingUrl = $isVi ? route('vi.booking.create') : route('en.booking.create');
    $phone = trim((string) $siteSettings->getPublic('contact.phone', ''));
    $email = trim((string) $siteSettings->getPublic('contact.email', ''));
    $address = trim((string) $siteSettings->getPublic('contact.address', ''));
    $phoneDigits = $phone ? preg_replace('/[^0-9+]/', '', $phone) : '';
    $phoneHref = $phoneDigits && preg_match('/^\+?[0-9]{6,15}$/', $phoneDigits) ? 'tel:'.$phoneDigits : null;
    $emailHref = filter_var($email, FILTER_VALIDATE_EMAIL) ? 'mailto:'.$email : null;
    $logoUrl = Vite::asset('resources/images/general/viet-han-logo.png');
@endphp

<footer class="public-footer w-full bg-[#140F0E] border-t border-[#C5A880]/20 text-white/70 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-12" data-reveal-group>
            <!-- Col 1: Brand Essence -->
            <div class="lg:col-span-5 space-y-5" data-reveal>
                <div class="flex items-center gap-3.5">
                    <img src="{{ $logoUrl }}" alt="Việt Hàn Âu Hàn Spa" class="h-10 w-auto object-contain brightness-110" loading="lazy">
                    <span class="font-semibold text-2xl text-white tracking-wide">
                        Việt Hàn <span class="text-[#C5A880]">Âu Hàn Spa</span>
                    </span>
                </div>

                <p class="text-[15px] sm:text-[16px] leading-[1.6] text-white/75 max-w-md">
                    {{ __('common.tagline') }}
                </p>

                @if($address)
                    <div class="pt-2 text-[15px] leading-[1.6] text-white/70 flex items-start gap-2.5">
                        <span class="text-[#C5A880] mt-0.5 shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <span class="font-semibold text-white/85">{{ $isVi ? 'Địa chỉ:' : 'Address:' }}</span>
                            <span class="ml-1">{{ $address }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Col 2: Quick Navigation -->
            <div class="lg:col-span-3 space-y-4" data-reveal>
                <div class="inline-flex items-center gap-2 text-base font-semibold uppercase tracking-widest text-[#C5A880]">
                    <span class="w-4 h-px bg-[#C5A880]/60"></span>
                    {{ $isVi ? 'Khám Phá' : 'Navigation' }}
                </div>
                <ul class="space-y-2.5 text-[15px] sm:text-[16px] leading-[1.6]">
                    <li><a href="{{ $homeUrl }}" class="hover:text-[#C5A880] transition duration-200">{{ __('navigation.home') }}</a></li>
                    <li><a href="{{ $aboutUrl }}" class="hover:text-[#C5A880] transition duration-200">{{ __('navigation.about') }}</a></li>
                    <li><a href="{{ $servicesUrl }}" class="hover:text-[#C5A880] transition duration-200">{{ __('navigation.services') }}</a></li>
                    <li><a href="{{ $trainingUrl }}" class="hover:text-[#C5A880] transition duration-200">{{ __('navigation.training') }}</a></li>
                    <li><a href="{{ $blogUrl }}" class="hover:text-[#C5A880] transition duration-200">{{ __('navigation.blog') }}</a></li>
                    <li><a href="{{ $contactUrl }}" class="hover:text-[#C5A880] transition duration-200">{{ __('navigation.contact') }}</a></li>
                    <li><a href="{{ $bookingUrl }}" class="text-[#C5A880] hover:text-white font-medium transition duration-200">{{ __('navigation.booking') }} &rarr;</a></li>
                </ul>
            </div>

            <!-- Col 3: Contact Info -->
            <div class="lg:col-span-4 space-y-4" data-reveal>
                <div class="inline-flex items-center gap-2 text-base font-semibold uppercase tracking-widest text-[#C5A880]">
                    <span class="w-4 h-px bg-[#C5A880]/60"></span>
                    {{ $isVi ? 'Thông Tin Liên Hệ' : 'Contact Info' }}
                </div>
                <ul class="space-y-3 text-[15px] sm:text-[16px] leading-[1.6]">
                    @if($phone && $phoneHref)
                        <li class="flex items-center gap-2.5">
                            <span class="text-[#C5A880] shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </span>
                            <div>
                                <span class="text-white/60 text-sm">{{ $isVi ? 'Hotline:' : 'Phone:' }}</span>
                                <a href="{{ $phoneHref }}" class="font-medium hover:text-[#C5A880] transition ml-1 text-white/95">{{ $phone }}</a>
                            </div>
                        </li>
                    @endif
                    @if($email && $emailHref)
                        <li class="flex items-center gap-2.5">
                            <span class="text-[#C5A880] shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <div>
                                <span class="text-white/60 text-sm">Email:</span>
                                <a href="{{ $emailHref }}" class="font-medium hover:text-[#C5A880] transition ml-1 text-white/95">{{ $email }}</a>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Bottom Copyright -->
        <div class="mt-14 pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-white/60" data-reveal>
            <p>&copy; {{ date('Y') }} Việt Hàn Âu Hàn Spa. {{ __('common.all_rights_reserved') }}.</p>
            <p class="italic text-[#C5A880]/85 tracking-wide">
                Beauty &amp; Wellness Sanctuary
            </p>
        </div>
    </div>
</footer>
