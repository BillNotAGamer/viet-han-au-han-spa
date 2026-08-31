@inject('siteSettings', 'App\Services\Settings\SiteSettings')

@php
    $isVi = app()->getLocale() === 'vi';
    $homeUrl = $isVi ? route('vi.home') : route('en.home');
    $aboutUrl = $isVi ? route('vi.about') : route('en.about');
    $servicesUrl = $isVi ? route('vi.services.index') : route('en.services.index');
    $trainingUrl = $isVi ? route('vi.training.index') : route('en.training.index');
    $blogUrl = $isVi ? route('vi.blog.index') : route('en.blog.index');
    $contactUrl = $isVi ? route('vi.contact') : route('en.contact');
    $phone = trim((string) $siteSettings->getPublic('contact.phone', ''));
    $email = trim((string) $siteSettings->getPublic('contact.email', ''));
    $address = trim((string) $siteSettings->getPublic('contact.address', ''));
    $hours = trim((string) $siteSettings->getPublic('business.hours', ''));
    $phoneDigits = $phone ? preg_replace('/[^0-9+]/', '', $phone) : '';
    $phoneHref = $phoneDigits && preg_match('/^\+?[0-9]{6,15}$/', $phoneDigits) ? 'tel:'.$phoneDigits : null;
    $emailHref = filter_var($email, FILTER_VALIDATE_EMAIL) ? 'mailto:'.$email : null;
@endphp

<footer class="w-full bg-[#140F0E] border-t border-[#C5A880]/15 text-white/70 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 lg:gap-12">
            <!-- Col 1: Brand -->
            <div class="md:col-span-2 space-y-4">
                <div class="font-serif font-bold text-2xl text-[#C5A880]">
                    Việt Hàn Âu Hàn Spa
                </div>
                <p class="text-sm leading-relaxed text-white/70 max-w-sm">
                    {{ __('common.tagline') }}
                </p>
                @if($address)
                    <p class="text-xs text-white/50">
                        <span class="font-semibold text-white/80">{{ $isVi ? 'Địa chỉ:' : 'Address:' }}</span> {{ $address }}
                    </p>
                @endif
            </div>

            <!-- Col 2: Quick Navigation -->
            <div class="space-y-4">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#C5A880]">
                    {{ $isVi ? 'Liên kết nhanh' : 'Quick Links' }}
                </div>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ $homeUrl }}" class="hover:text-[#C5A880] transition">{{ __('navigation.home') }}</a></li>
                    <li><a href="{{ $aboutUrl }}" class="hover:text-[#C5A880] transition">{{ __('navigation.about') }}</a></li>
                    <li><a href="{{ $servicesUrl }}" class="hover:text-[#C5A880] transition">{{ __('navigation.services') }}</a></li>
                    <li><a href="{{ $trainingUrl }}" class="hover:text-[#C5A880] transition">{{ __('navigation.training') }}</a></li>
                    <li><a href="{{ $blogUrl }}" class="hover:text-[#C5A880] transition">{{ __('navigation.blog') }}</a></li>
                    <li><a href="{{ $contactUrl }}" class="hover:text-[#C5A880] transition">{{ __('navigation.contact') }}</a></li>
                </ul>
            </div>

            <!-- Col 3: Contact & Hours -->
            <div class="space-y-4">
                <div class="text-xs font-semibold uppercase tracking-wider text-[#C5A880]">
                    {{ $isVi ? 'Thông tin liên hệ' : 'Contact & Hours' }}
                </div>
                <ul class="space-y-2 text-sm">
                    @if($phone && $phoneHref)
                        <li>
                            <span class="text-white/50">{{ $isVi ? 'Hotline:' : 'Phone:' }}</span>
                            <a href="{{ $phoneHref }}" class="font-medium hover:text-[#C5A880] transition text-white/90">{{ $phone }}</a>
                        </li>
                    @endif
                    @if($email && $emailHref)
                        <li>
                            <span class="text-white/50">Email:</span>
                            <a href="{{ $emailHref }}" class="font-medium hover:text-[#C5A880] transition text-white/90">{{ $email }}</a>
                        </li>
                    @endif
                    @if($hours)
                        <li class="pt-1 text-xs leading-relaxed text-white/50">
                            <span class="font-semibold text-white/80">{{ $isVi ? 'Giờ mở cửa:' : 'Hours:' }}</span><br>
                            {{ $hours }}
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Bottom Copyright -->
        <div class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between text-xs text-white/40">
            <p>&copy; {{ date('Y') }} Việt Hàn Âu Hàn Spa. {{ __('common.all_rights_reserved') }}.</p>
            <p class="mt-2 sm:mt-0 font-serif italic text-[#C5A880]/80">
                Beauty &amp; Wellness Sanctuary
            </p>
        </div>
    </div>
</footer>
