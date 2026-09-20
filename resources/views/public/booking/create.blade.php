@php
    $bookingRitualImg = Vite::asset('resources/images/pages/booking/booking-welcome-lounge.webp');
@endphp

<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null">
    {{-- Hero Header --}}
    <section class="bg-gradient-to-b from-[#F5EFEB] to-[#FAF7F2] pt-28 sm:pt-32 lg:pt-36 pb-10 sm:pb-14 border-b border-[#E8DFC8]/70">
        <x-public.container size="lg">
            <div class="max-w-3xl space-y-3.5" data-reveal>
                <span class="inline-flex items-center gap-2 text-[14px] sm:text-[15px] font-semibold tracking-[0.1em] uppercase text-[#9B7B4F]">
                    <span class="w-6 h-px bg-[#C5A880]/70"></span>
                    {{ __('booking.eyebrow') }}
                </span>
                <h1 class="text-3xl sm:text-5xl lg:text-[clamp(2.5rem,4vw,3.85rem)] font-semibold tracking-[-0.025em] text-[#5B1121] leading-[1.08] break-words [text-wrap:balance]">
                    {{ __('booking.title') }}
                </h1>
                <p class="text-[18px] sm:text-[19px] font-medium text-[#554D4A] leading-[1.66] break-words max-w-2xl pt-1">
                    {{ __('booking.intro') }}
                </p>
            </div>
        </x-public.container>
    </section>

    {{-- Main Concierge Booking Section --}}
    <section class="bg-[#FAF7F2] py-10 sm:py-14 lg:py-18">
        <x-public.container size="lg">
            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] gap-10 lg:gap-16 items-start">
                {{-- Left Column: Concierge Narrative & Visual Atmosphere --}}
                <div class="min-w-0 space-y-6" data-reveal>
                    <div class="rounded-2xl border border-[#E8DFC8] bg-white overflow-hidden shadow-xs group editorial-card">
                        <div class="aspect-16/10 overflow-hidden relative editorial-image-zoom">
                            <img src="{{ $bookingRitualImg }}" alt="Việt Hàn Âu Hàn Spa Concierge" class="w-full h-full object-cover transition duration-700 group-hover:scale-105" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#181312]/60 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4 text-white">
                                <span class="text-[14px] uppercase tracking-widest text-[#C5A880] font-semibold">{{ app()->getLocale() === 'vi' ? 'Tiếp Nhận Lịch Hẹn' : 'Booking Concierge' }}</span>
                            </div>
                        </div>
                        <div class="p-6 sm:p-8 space-y-3">
                            <h2 class="text-2xl sm:text-3xl lg:text-[28px] font-semibold tracking-[-0.02em] text-[#5B1121] leading-[1.15]">
                                {{ __('booking.process_title') }}
                            </h2>
                            <p class="text-[17px] leading-[1.65] text-[#554D4A] break-words">
                                {{ __('booking.process_copy') }}
                            </p>
                        </div>
                    </div>

                    {{-- Concierge Reassurance Pillars --}}
                    <div class="rounded-2xl border border-[#E8DFC8] bg-white p-6 sm:p-7 shadow-xs space-y-4" data-reveal>
                        <div class="flex items-center gap-3.5 text-[15px] sm:text-[16px] text-[#554D4A]">
                            <div class="w-8 h-8 rounded-full bg-[#FAF7F2] border border-[#E8DFC8] flex items-center justify-center text-[#9B7B4F] shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span>{{ app()->getLocale() === 'vi' ? 'Gửi yêu cầu đặt lịch trực tuyến' : 'Submit appointment request online' }}</span>
                        </div>
                        <div class="flex items-center gap-3.5 text-[15px] sm:text-[16px] text-[#554D4A]">
                            <div class="w-8 h-8 rounded-full bg-[#FAF7F2] border border-[#E8DFC8] flex items-center justify-center text-[#9B7B4F] shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span>{{ app()->getLocale() === 'vi' ? 'Nhân viên sẽ liên hệ xác nhận' : 'Staff will follow up to confirm' }}</span>
                        </div>
                        <div class="flex items-center gap-3.5 text-[15px] sm:text-[16px] text-[#554D4A]">
                            <div class="w-8 h-8 rounded-full bg-[#FAF7F2] border border-[#E8DFC8] flex items-center justify-center text-[#9B7B4F] shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <span>{{ app()->getLocale() === 'vi' ? 'Thông tin được dùng để hỗ trợ yêu cầu đặt lịch' : 'Information used to support your booking request' }}</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-[#C5A880]/40 bg-[#211B19] p-6 sm:p-7 text-white shadow-xl space-y-3" data-reveal>
                        <span class="text-[14px] uppercase tracking-widest text-[#C5A880] font-semibold">{{ app()->getLocale() === 'vi' ? 'Quy Trình Hẹn' : 'Booking Process' }}</span>
                        <h2 class="text-xl sm:text-2xl font-semibold tracking-tight text-white">
                            {{ __('booking.confirmation_title') }}
                        </h2>
                        <p class="text-[16px] leading-[1.65] text-white/85 break-words">
                            {{ __('booking.confirmation_copy') }}
                        </p>
                    </div>
                </div>

                {{-- Right Column: Concierge Booking Form --}}
                <div class="min-w-0" data-reveal>
                    <x-public.booking-form
                        :action="$action"
                        :services="$services"
                        :min-date="$minDate"
                        id-prefix="page"
                    />
                </div>
            </div>
        </x-public.container>
    </section>
</x-layouts.public>
