@inject('siteSettings', 'App\Services\Settings\SiteSettings')

@php
    $isVi = app()->getLocale() === 'vi';
    $phone = $siteSettings->getPublic('contact.phone', '090 123 4567');
    $email = $siteSettings->getPublic('contact.email', 'info@viethanauhanspa.com');
    $address = $siteSettings->getPublic('contact.address', 'Việt Hàn Âu Hàn Spa, TP. Hồ Chí Minh');
    $hours = $siteSettings->getPublic('business.hours', '09:00 - 20:30 (Thứ 2 - Chủ Nhật)');
@endphp

<footer class="w-full bg-brand-warm border-t border-brand-border text-brand-text-secondary mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 lg:gap-12">
            <!-- Col 1: Brand -->
            <div class="md:col-span-2 space-y-4">
                <div class="font-serif font-bold text-2xl text-brand-primary">
                    Việt Hàn Âu Hàn Spa
                </div>
                <p class="text-sm leading-relaxed text-brand-text-secondary max-w-sm">
                    {{ __('common.tagline') }}. Trải nghiệm chăm sóc sắc đẹp và trị liệu sức khỏe chuẩn Hàn Quốc trong không gian tinh tế và thư giãn.
                </p>
                @if($address)
                    <p class="text-xs text-brand-text-muted">
                        <span class="font-semibold text-brand-text">{{ $isVi ? 'Địa chỉ:' : 'Address:' }}</span> {{ $address }}
                    </p>
                @endif
            </div>

            <!-- Col 2: Quick Navigation -->
            <div class="space-y-4">
                <div class="text-xs font-semibold uppercase tracking-wider text-brand-primary">
                    {{ $isVi ? 'Liên kết nhanh' : 'Quick Links' }}
                </div>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ $isVi ? url('/') : url('/en') }}" class="hover:text-brand-primary transition">{{ __('navigation.home') }}</a></li>
                    <li><a href="{{ $isVi ? url('/dich-vu') : url('/en/services') }}" class="hover:text-brand-primary transition">{{ __('navigation.services') }}</a></li>
                    <li><a href="{{ $isVi ? url('/dao-tao-hoc-vien') : url('/en/training') }}" class="hover:text-brand-primary transition">{{ __('navigation.training') }}</a></li>
                    <li><a href="{{ $isVi ? url('/blog') : url('/en/blog') }}" class="hover:text-brand-primary transition">{{ __('navigation.blog') }}</a></li>
                    <li><a href="{{ $isVi ? url('/gioi-thieu') : url('/en/about') }}" class="hover:text-brand-primary transition">{{ __('navigation.about') }}</a></li>
                    <li><a href="{{ $isVi ? url('/lien-he') : url('/en/contact') }}" class="hover:text-brand-primary transition">{{ __('navigation.contact') }}</a></li>
                </ul>
            </div>

            <!-- Col 3: Contact & Hours -->
            <div class="space-y-4">
                <div class="text-xs font-semibold uppercase tracking-wider text-brand-primary">
                    {{ $isVi ? 'Thông tin liên hệ' : 'Contact & Hours' }}
                </div>
                <ul class="space-y-2 text-sm">
                    @if($phone)
                        <li>
                            <span class="text-brand-text-muted">{{ $isVi ? 'Hotline:' : 'Phone:' }}</span>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="font-medium hover:text-brand-primary transition">{{ $phone }}</a>
                        </li>
                    @endif
                    @if($email)
                        <li>
                            <span class="text-brand-text-muted">Email:</span>
                            <a href="mailto:{{ $email }}" class="font-medium hover:text-brand-primary transition">{{ $email }}</a>
                        </li>
                    @endif
                    @if($hours)
                        <li class="pt-1 text-xs leading-relaxed text-brand-text-muted">
                            <span class="font-semibold text-brand-text">{{ $isVi ? 'Giờ mở cửa:' : 'Hours:' }}</span><br>
                            {{ $hours }}
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Bottom Copyright -->
        <div class="mt-12 pt-8 border-t border-brand-border flex flex-col sm:flex-row items-center justify-between text-xs text-brand-text-muted">
            <p>&copy; {{ date('Y') }} Việt Hàn Âu Hàn Spa. {{ __('common.all_rights_reserved') }}.</p>
            <p class="mt-2 sm:mt-0 font-serif italic text-brand-primary">
                Beauty & Wellness Sanctuary
            </p>
        </div>
    </div>
</footer>
