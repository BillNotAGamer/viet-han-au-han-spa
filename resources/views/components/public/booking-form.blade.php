@props([
    'action',
    'services',
    'minDate',
    'idPrefix' => 'booking',
    'variant' => 'page',
])

@php
    $isModal = $variant === 'modal';
    $isV2Page = $variant === 'v2-page';
    $prefix = $idPrefix ? rtrim($idPrefix, '_') . '_' : '';
    $nameId = $prefix . 'customer_name';
    $phoneId = $prefix . 'phone';
    $emailId = $prefix . 'email';
    $serviceInputId = $prefix . 'service_id';
    $dateId = $prefix . 'preferred_date';
    $timeId = $prefix . 'preferred_time';
    $notesId = $prefix . 'notes';
    $consentId = $prefix . 'consent';
    $formClass = match (true) {
        $isModal => 'space-y-3',
        $isV2Page => 'v2-booking-form',
        default => 'rounded-2xl border border-[#E8DFC8] bg-white p-6 sm:p-10 shadow-lg space-y-6',
    };
    $gridClass = match (true) {
        $isModal => 'grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-x-5 sm:gap-y-3',
        $isV2Page => 'v2-booking-form__grid',
        default => 'grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-6',
    };
    $wideFieldClass = $isModal || $isV2Page ? '' : 'sm:col-span-2';
    $controlClass = match (true) {
        $isModal => 'mt-2 sm:mt-1.5 block w-full h-12 sm:h-11 rounded-xl border border-[#D8CBB7] bg-[#FFFCF8] px-4 sm:px-3.5 py-2 text-brand-text shadow-xs transition duration-200 focus:border-[#5B1121] focus:ring-1 focus:ring-[#5B1121] text-[16px]',
        $isV2Page => 'v2-booking-form__control',
        default => 'mt-2 block w-full rounded-xl border border-[#D8CBB7] bg-[#FFFCF8] px-4 py-3 sm:py-3.5 text-brand-text shadow-xs transition duration-200 focus:border-[#5B1121] focus:ring-1 focus:ring-[#5B1121] text-[16px] sm:text-[17px]',
    };
    $textareaClass = match (true) {
        $isModal => 'mt-2 sm:mt-1.5 block w-full min-h-[84px] sm:h-[76px] sm:min-h-[76px] rounded-xl border border-[#D8CBB7] bg-[#FFFCF8] px-4 sm:px-3.5 py-2.5 sm:py-2 text-brand-text shadow-xs transition duration-200 focus:border-[#5B1121] focus:ring-1 focus:ring-[#5B1121] text-[16px] resize-y',
        $isV2Page => 'v2-booking-form__control v2-booking-form__textarea',
        default => $controlClass,
    };
    $consentWrapClass = match (true) {
        $isModal => 'pt-0.5',
        $isV2Page => 'v2-booking-form__consent',
        default => 'pt-1 sm:pt-2',
    };
    $consentLabelClass = match (true) {
        $isModal => 'flex items-start sm:items-center gap-2.5 text-[14px] leading-snug text-[#554D4A] cursor-pointer',
        $isV2Page => 'v2-booking-form__consent-label',
        default => 'flex items-start gap-3 text-[14px] sm:text-[15px] leading-relaxed text-[#554D4A] cursor-pointer',
    };
    $checkboxClass = match (true) {
        $isModal => 'mt-0.5 sm:mt-0 rounded border-[#D8CBB7] text-[#5B1121] focus:ring-[#5B1121]',
        $isV2Page => 'v2-booking-form__checkbox',
        default => 'mt-1 rounded border-[#D8CBB7] text-[#5B1121] focus:ring-[#5B1121]',
    };
    $submitWrapClass = match (true) {
        $isModal => 'pt-0.5',
        $isV2Page => 'v2-booking-form__submit',
        default => 'pt-2 sm:pt-4',
    };
    $submitButtonClass = match (true) {
        $isModal => 'btn-editorial-primary w-full sm:w-auto h-12 px-8 py-0 text-center',
        $isV2Page => 'v2-button v2-button--primary',
        default => 'btn-editorial-primary w-full sm:w-auto px-8 sm:px-10 py-3.5 sm:py-4 text-center',
    };
@endphp

@if(session('booking_status'))
    <div @class([
        'v2-booking-form__status' => $isV2Page,
        'mb-6 rounded-2xl border border-[#C5A880]/60 bg-[#FAF2EB] px-6 py-5 text-sm font-semibold text-[#5B1121] shadow-xs flex items-center gap-3' => ! $isV2Page,
    ]) role="status">
        <span class="w-2.5 h-2.5 rounded-full bg-[#9B7B4F]"></span>
        <span>{{ session('booking_status') }}</span>
    </div>
@endif

<form method="POST" action="{{ $action }}" class="{{ $attributes->get('class', $formClass) }}" novalidate>
    @csrf

    <div class="{{ $gridClass }}">
        <div @class([$wideFieldClass])>
            <label for="{{ $nameId }}" class="block text-[13px] sm:text-[14px] font-semibold uppercase tracking-[0.08em] text-[#5B1121] font-sans">
                {{ __('booking.fields.customer_name') }} <span class="text-[#9A1B34]" aria-hidden="true">*</span>
            </label>
            <input
                id="{{ $nameId }}"
                name="customer_name"
                type="text"
                value="{{ old('customer_name') }}"
                required
                maxlength="150"
                aria-invalid="{{ $errors->has('customer_name') ? 'true' : 'false' }}"
                aria-describedby="@error('customer_name') {{ $nameId }}_error @enderror"
                class="{{ $controlClass }}"
            >
            @error('customer_name')
                <p id="{{ $nameId }}_error" class="mt-2 text-xs font-medium text-[#9A1B34] font-sans">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="{{ $phoneId }}" class="block text-[13px] sm:text-[14px] font-semibold uppercase tracking-[0.08em] text-[#5B1121] font-sans">
                {{ __('booking.fields.phone') }} <span class="text-[#9A1B34]" aria-hidden="true">*</span>
            </label>
            <input
                id="{{ $phoneId }}"
                name="phone"
                type="tel"
                value="{{ old('phone') }}"
                required
                maxlength="30"
                aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                aria-describedby="@error('phone') {{ $phoneId }}_error @enderror"
                class="{{ $controlClass }}"
            >
            @error('phone')
                <p id="{{ $phoneId }}_error" class="mt-2 text-xs font-medium text-[#9A1B34] font-sans">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="{{ $emailId }}" class="block text-[13px] sm:text-[14px] font-semibold uppercase tracking-[0.08em] text-[#5B1121] font-sans">
                {{ __('booking.fields.email') }}
            </label>
            <input
                id="{{ $emailId }}"
                name="email"
                type="email"
                value="{{ old('email') }}"
                maxlength="255"
                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                aria-describedby="@error('email') {{ $emailId }}_error @enderror"
                class="{{ $controlClass }}"
            >
            @error('email')
                <p id="{{ $emailId }}_error" class="mt-2 text-xs font-medium text-[#9A1B34] font-sans">{{ $message }}</p>
            @enderror
        </div>

        <div @class([$wideFieldClass])>
            <label for="{{ $serviceInputId }}" class="block text-[13px] sm:text-[14px] font-semibold uppercase tracking-[0.08em] text-[#5B1121] font-sans">
                {{ __('booking.fields.service') }} <span class="text-[#9A1B34]" aria-hidden="true">*</span>
            </label>
            <select
                id="{{ $serviceInputId }}"
                name="service_id"
                required
                aria-invalid="{{ $errors->has('service_id') ? 'true' : 'false' }}"
                aria-describedby="@error('service_id') {{ $serviceInputId }}_error @enderror"
                class="{{ $controlClass }}"
            >
                <option value="">{{ __('booking.fields.service_placeholder') }}</option>
                @foreach($services as $service)
                    <option value="{{ $service['id'] }}" @selected((string) old('service_id') === (string) $service['id'])>
                        {{ $service['name'] }}@if($service['price_summary']) — {{ $service['price_summary'] }}@endif
                    </option>
                @endforeach
            </select>
            @error('service_id')
                <p id="{{ $serviceInputId }}_error" class="mt-2 text-xs font-medium text-[#9A1B34] font-sans">{{ $message }}</p>
            @enderror
            @if($services->isEmpty())
                <p class="mt-2 text-xs text-[#736965] font-sans">{{ __('booking.no_services') }}</p>
            @endif
        </div>

        <div>
            <label for="{{ $dateId }}" class="block text-[13px] sm:text-[14px] font-semibold uppercase tracking-[0.08em] text-[#5B1121] font-sans">
                {{ __('booking.fields.preferred_date') }} <span class="text-[#9A1B34]" aria-hidden="true">*</span>
            </label>
            <input
                id="{{ $dateId }}"
                name="preferred_date"
                type="date"
                value="{{ old('preferred_date') }}"
                min="{{ $minDate }}"
                required
                aria-invalid="{{ $errors->has('preferred_date') ? 'true' : 'false' }}"
                aria-describedby="@error('preferred_date') {{ $dateId }}_error @enderror"
                class="{{ $controlClass }}"
            >
            @error('preferred_date')
                <p id="{{ $dateId }}_error" class="mt-2 text-xs font-medium text-[#9A1B34] font-sans">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="{{ $timeId }}" class="block text-[13px] sm:text-[14px] font-semibold uppercase tracking-[0.08em] text-[#5B1121] font-sans">
                {{ __('booking.fields.preferred_time') }} <span class="text-[#9A1B34]" aria-hidden="true">*</span>
            </label>
            <input
                id="{{ $timeId }}"
                name="preferred_time"
                type="time"
                value="{{ old('preferred_time') }}"
                required
                aria-invalid="{{ $errors->has('preferred_time') ? 'true' : 'false' }}"
                aria-describedby="@error('preferred_time') {{ $timeId }}_error @enderror"
                class="{{ $controlClass }}"
            >
            @error('preferred_time')
                <p id="{{ $timeId }}_error" class="mt-2 text-xs font-medium text-[#9A1B34] font-sans">{{ $message }}</p>
            @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="{{ $notesId }}" class="block text-[13px] sm:text-[14px] font-semibold uppercase tracking-[0.08em] text-[#5B1121] font-sans">
                {{ __('booking.fields.notes') }}
            </label>
            <textarea
                id="{{ $notesId }}"
                name="notes"
                rows="{{ $isModal ? 3 : 4 }}"
                maxlength="2000"
                aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}"
                aria-describedby="@error('notes') {{ $notesId }}_error @enderror"
                class="{{ $textareaClass }}"
            >{{ old('notes') }}</textarea>
            @error('notes')
                <p id="{{ $notesId }}_error" class="mt-2 text-xs font-medium text-[#9A1B34] font-sans">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="{{ $consentWrapClass }}">
        <label for="{{ $consentId }}" class="{{ $consentLabelClass }}">
            <input
                id="{{ $consentId }}"
                type="checkbox"
                name="consent"
                value="1"
                @checked(old('consent'))
                required
                aria-invalid="{{ $errors->has('consent') ? 'true' : 'false' }}"
                aria-describedby="@error('consent') {{ $consentId }}_error @enderror"
                class="{{ $checkboxClass }}"
            >
            <span>{{ __('booking.fields.consent') }}</span>
        </label>
        @error('consent')
            <p id="{{ $consentId }}_error" class="mt-2 text-xs font-medium text-[#9A1B34] font-sans">{{ $message }}</p>
        @enderror
    </div>

    <div class="{{ $submitWrapClass }}">
        <button type="submit" class="{{ $submitButtonClass }}">
            {{ __('booking.submit') }}
        </button>
    </div>
</form>
