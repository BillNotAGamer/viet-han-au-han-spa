@php
    $isVi = app()->getLocale() === 'vi';
    $bookingRitualImg = Vite::asset('resources/images/pages/booking/booking-welcome-lounge.webp');
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null" variant="v2">
    {{-- =========================================================================
       PAGE HERO — Private Spa Concierge Reservation Header
       ========================================================================= --}}
    <section class="v2-page-hero v2-page-hero--intro v2-booking-page__hero bg-[#F5F0E8] border-b border-[#E2D9CC] py-16 lg:py-24">
        <x-public.v2.container>
            <div class="v2-page-hero__copy max-w-3xl">
                <div class="flex items-center gap-3 mb-4">
                    <x-public.v2.eyebrow>{{ __('booking.eyebrow') }}</x-public.v2.eyebrow>
                    <span class="w-8 h-px bg-[#C9A24D]/60" aria-hidden="true"></span>
                    <span class="font-serif italic text-xs text-[#C9A24D]">Concierge Privé</span>
                </div>
                <x-public.v2.display-heading level="1" size="xl" class="v2-primary-page-hero__title font-serif text-[#181311]">
                    {{ __('booking.title') }}
                </x-public.v2.display-heading>
                <div class="w-12 h-px bg-[#C9A24D] my-6"></div>
                <x-public.v2.editorial-copy size="lg" class="text-[#594B42] leading-relaxed">
                    {{ __('booking.intro') }}
                </x-public.v2.editorial-copy>
            </div>
        </x-public.v2.container>
    </section>

    {{-- =========================================================================
       CONCIERGE DESK — Asymmetric Split Layout
       ========================================================================= --}}
    <x-public.v2.section surface="ivory" class="v2-booking-page py-16 lg:py-24">
        <x-public.v2.container>
            <div class="v2-booking-page__grid grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
                
                {{-- Left Column: Concierge Dossier & Sanctuary Assurance --}}
                <aside class="v2-booking-page__concierge lg:col-span-5 space-y-8">
                    <div class="border border-[#E2D9CC] bg-[#FBF9F4] p-2">
                        <x-public.v2.media-frame
                            :src="$bookingRitualImg"
                            :alt="__('booking.process_title')"
                            role="portrait"
                            class="aspect-[4/3] w-full object-cover"
                        />
                    </div>

                    {{-- Process Note --}}
                    <div class="v2-booking-page__note border border-[#E2D9CC] bg-[#FBF9F4] p-6 sm:p-8">
                        <x-public.v2.eyebrow>{{ __('booking.eyebrow') }}</x-public.v2.eyebrow>
                        <x-public.v2.display-heading level="2" size="heading-md" class="font-serif text-xl sm:text-2xl text-[#181311] mt-2 mb-3">
                            {{ __('booking.process_title') }}
                        </x-public.v2.display-heading>
                        <p class="v2-type-body text-[#594B42] text-sm leading-relaxed m-0">
                            {{ __('booking.process_copy') }}
                        </p>
                    </div>

                    {{-- Royal Sanctuary Commitments --}}
                    <div class="border border-[#E2D9CC] bg-[#F5F0E8] p-6 sm:p-8 space-y-4">
                        <span class="text-[11px] font-mono uppercase tracking-widest text-[#C9A24D] font-medium block">
                            {{ $isVi ? 'Chuẩn mực đón tiếp' : 'Sanctuary Standards' }}
                        </span>
                        <div class="divide-y divide-[#E2D9CC] text-sm">
                            <div class="py-3 flex items-baseline gap-3">
                                <span class="font-serif italic text-base text-[#C9A24D]">01</span>
                                <p class="m-0 text-[#181311] font-medium">{{ $isVi ? 'Phác đồ chuyên biệt theo thể trạng từng quý khách' : 'Customized therapy protocol per individual assessment' }}</p>
                            </div>
                            <div class="py-3 flex items-baseline gap-3">
                                <span class="font-serif italic text-base text-[#C9A24D]">02</span>
                                <p class="m-0 text-[#181311] font-medium">{{ $isVi ? 'Dược liệu Đông Y & thảo mộc tự nhiên tinh khiết' : 'Pure natural Eastern herbs and organic botanicals' }}</p>
                            </div>
                            <div class="py-3 flex items-baseline gap-3">
                                <span class="font-serif italic text-base text-[#C9A24D]">03</span>
                                <p class="m-0 text-[#181311] font-medium">{{ $isVi ? 'Không gian riêng tư, tĩnh tại chuẩn resort 5 sao' : '5-star private and tranquil sanctuary suites' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Confirmation Assurance Card --}}
                    <div class="v2-booking-page__confirmation border border-[#C9A24D]/40 bg-[#181311] text-[#EFE8DC] p-6 sm:p-8">
                        <div class="flex items-center gap-2 text-[#C9A24D] mb-2">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#C9A24D]"></span>
                            <span class="text-xs uppercase tracking-widest font-mono">{{ $isVi ? 'Cam kết hỗ trợ' : 'Reservation Notice' }}</span>
                        </div>
                        <h2 class="font-serif text-xl sm:text-2xl text-[#EFE8DC] m-0 mb-3 font-medium">
                            {{ __('booking.confirmation_title') }}
                        </h2>
                        <p class="text-sm text-[#EFE8DC]/80 leading-relaxed m-0">
                            {{ __('booking.confirmation_copy') }}
                        </p>
                    </div>
                </aside>

                {{-- Right Column: Reservation Ledger --}}
                <div class="v2-booking-page__form lg:col-span-7">
                    <div class="border border-[#E2D9CC] bg-[#FBF9F4] p-6 sm:p-10 lg:p-12 relative shadow-none">
                        <div class="border-b border-[#E2D9CC] pb-6 mb-8 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-mono uppercase tracking-widest text-[#C9A24D] font-medium block mb-1">
                                    {{ $isVi ? 'Phiếu Đăng Ký Lịch Hẹn' : 'Concierge Booking Request' }}
                                </span>
                                <h3 class="font-serif text-2xl text-[#181311] font-medium m-0">
                                    {{ $isVi ? 'Thông Tin Đặt Chỗ' : 'Reservation Details' }}
                                </h3>
                            </div>
                            <span class="hidden sm:inline-block font-serif italic text-sm text-[#C9A24D]">Việt Hàn Âu Hàn</span>
                        </div>

                        <x-public.booking-form
                            :action="$action"
                            :services="$services"
                            :min-date="$minDate"
                            id-prefix="page"
                            variant="v2-page"
                        />
                    </div>
                </div>

            </div>
        </x-public.v2.container>
    </x-public.v2.section>
</x-layouts.public>
