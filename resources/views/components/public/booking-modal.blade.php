@props([
    'action',
    'services',
    'minDate',
    'locale' => 'vi',
])

@php
    $modalImg = Vite::asset('resources/images/pages/booking/booking-welcome-lounge.webp');
    $closeLabel = $locale === 'en' ? 'Close' : 'Đóng';
@endphp

<div
    id="booking-modal"
    x-data="{
        isOpen: false,
        triggerEl: null,
        open(event) {
            this.triggerEl = event?.detail?.trigger || document.activeElement;
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
            if (typeof window.dataLayer !== 'undefined' && Array.isArray(window.dataLayer)) {
                window.dataLayer.push({
                    event: 'booking_modal_opened',
                    locale: {{ Js::from($locale) }},
                });
            }
            this.$nextTick(() => {
                const firstInput = document.getElementById('modal_customer_name');
                if (firstInput) {
                    firstInput.focus();
                } else {
                    this.$refs.closeButton?.focus();
                }
            });
        },
        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
            if (this.triggerEl && typeof this.triggerEl.focus === 'function') {
                this.$nextTick(() => {
                    this.triggerEl.focus();
                });
            }
        }
    }"
    @open-booking-modal.window="open($event)"
    @keydown.escape.window="if (isOpen) close()"
    x-cloak
    x-show="isOpen"
    class="v2-booking-modal fixed inset-0 z-[90] flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
    aria-labelledby="booking-modal-title"
    role="dialog"
    aria-modal="true"
>
    {{-- Dark Translucent Backdrop --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="v2-booking-modal__backdrop fixed inset-0 bg-[#140F0E]/80 backdrop-blur-xs transition-opacity"
        aria-hidden="true"
    ></div>

    {{-- Modal Dialog Surface --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        @click.stop
        class="v2-booking-modal__dialog relative w-full max-w-[840px] max-h-[calc(100vh-24px)] max-h-[calc(100dvh-24px)] sm:max-h-[calc(100vh-48px)] sm:max-h-[calc(100dvh-48px)] my-auto bg-[#FFFCF8] rounded-2xl sm:rounded-3xl border border-[#E8DFC8] shadow-2xl overflow-hidden flex flex-col z-10"
    >
        {{-- Close Button (Top Right) --}}
        <button
            type="button"
            x-ref="closeButton"
            @click="close()"
            class="v2-booking-modal__close absolute top-3.5 right-3.5 sm:top-5 sm:right-5 z-20 flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/90 sm:bg-white/80 hover:bg-white text-[#5B1121] border border-[#E8DFC8]/80 shadow-sm transition duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-2"
            aria-label="{{ $closeLabel }}"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Scrollable Container --}}
        <div class="overflow-y-auto flex-1 overscroll-contain">
            {{-- Spa Visual Header --}}
            <div class="v2-booking-modal__media relative h-40 sm:h-32 w-full overflow-hidden bg-[#181312]">
                <img
                    src="{{ $modalImg }}"
                    alt="Việt Hàn Âu Hàn Spa — Không Gian Tiếp Nhận Lịch Hẹn"
                    class="w-full h-full object-cover object-[center_45%]"
                    loading="lazy"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-[#FFFCF8] via-[#140F0E]/40 to-transparent"></div>
                <div class="absolute bottom-3 left-5 sm:left-8 right-14 sm:right-16">
                    <span class="inline-flex items-center gap-2 text-[11px] sm:text-[12px] font-semibold tracking-[0.12em] uppercase text-[#C5A880] font-sans drop-shadow-sm">
                        <span class="w-5 h-px bg-[#C5A880]"></span>
                        {{ $locale === 'vi' ? 'Tiếp Nhận Lịch Hẹn' : 'Booking Concierge' }}
                    </span>
                    <h2 id="booking-modal-title" class="text-2xl sm:text-[36px] font-semibold tracking-[-0.02em] text-[#5B1121] leading-[1.05] mt-0.5 drop-shadow-xs">
                        {{ __('booking.title') }}
                    </h2>
                </div>
            </div>

            {{-- Intro Copy & Form Area --}}
            <div class="v2-booking-modal__content px-5 py-5 sm:px-8 sm:py-5 space-y-3">
                <p class="text-[16px] sm:text-[16px] text-[#554D4A] leading-[1.5] max-w-3xl">
                    {{ __('booking.intro') }}
                </p>

                <div class="border-t border-[#E8DFC8]/80 pt-3">
                    <x-public.booking-form
                        :action="$action"
                        :services="$services"
                        :min-date="$minDate"
                        id-prefix="modal"
                        variant="modal"
                    />
                </div>
            </div>
        </div>
    </div>
</div>
