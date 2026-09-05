<x-layouts.public :title="$title" header-mode="solid" :contact-href="$contactHref" :seo="$seo ?? null">
    <section class="bg-brand-ivory pt-32 sm:pt-36 lg:pt-40 pb-14 sm:pb-20 border-b border-brand-border">
        <x-public.container size="lg">
            <div class="max-w-3xl space-y-5">
                <span class="text-xs sm:text-sm font-semibold tracking-widest uppercase text-[#B3956B]">
                    {{ __('booking.eyebrow') }}
                </span>
                <h1 class="font-serif text-4xl sm:text-5xl lg:text-6xl font-bold tracking-normal text-[#5B1121] leading-tight break-words [text-wrap:balance]">
                    {{ __('booking.title') }}
                </h1>
                <p class="text-base sm:text-lg text-brand-text-secondary leading-relaxed break-words max-w-2xl">
                    {{ __('booking.intro') }}
                </p>
            </div>
        </x-public.container>
    </section>

    <section class="bg-[#FAF7F2] py-14 sm:py-20 lg:py-24">
        <x-public.container size="lg">
            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,0.78fr)_minmax(0,1fr)] gap-10 lg:gap-16">
                <div class="min-w-0 space-y-6">
                    <div class="border border-[#E8DFC8] bg-white p-6 sm:p-8 shadow-xs">
                        <h2 class="font-serif text-3xl font-bold tracking-normal text-[#5B1121]">
                            {{ __('booking.process_title') }}
                        </h2>
                        <p class="mt-4 text-base leading-relaxed text-brand-text-secondary break-words">
                            {{ __('booking.process_copy') }}
                        </p>
                    </div>

                    <div class="border border-[#C5A880]/45 bg-[#211B19] p-6 sm:p-8 text-white shadow-md">
                        <h2 class="font-serif text-2xl font-bold tracking-normal">
                            {{ __('booking.confirmation_title') }}
                        </h2>
                        <p class="mt-3 text-sm leading-relaxed text-white/76 break-words">
                            {{ __('booking.confirmation_copy') }}
                        </p>
                    </div>
                </div>

                <div class="min-w-0">
                    @if(session('booking_status'))
                        <div class="mb-6 border border-[#C5A880]/55 bg-[#F4EFEA] px-5 py-4 text-sm font-semibold text-[#5B1121]" role="status">
                            {{ session('booking_status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ $action }}" class="border border-[#E8DFC8] bg-white p-6 sm:p-8 shadow-sm space-y-6" novalidate>
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="sm:col-span-2">
                                <label for="customer_name" class="block text-sm font-semibold text-[#5B1121]">
                                    {{ __('booking.fields.customer_name') }} <span aria-hidden="true">*</span>
                                </label>
                                <input id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" required maxlength="150" aria-invalid="{{ $errors->has('customer_name') ? 'true' : 'false' }}" aria-describedby="@error('customer_name') customer_name_error @enderror" class="mt-2 block w-full rounded-sm border border-[#D8CBB7] bg-[#FFFCF8] px-4 py-3 text-brand-text shadow-xs focus:border-[#5B1121] focus:ring-[#5B1121]">
                                @error('customer_name')
                                    <p id="customer_name_error" class="mt-2 text-sm text-[#9A1B34]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-semibold text-[#5B1121]">
                                    {{ __('booking.fields.phone') }} <span aria-hidden="true">*</span>
                                </label>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required maxlength="30" aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}" aria-describedby="@error('phone') phone_error @enderror" class="mt-2 block w-full rounded-sm border border-[#D8CBB7] bg-[#FFFCF8] px-4 py-3 text-brand-text shadow-xs focus:border-[#5B1121] focus:ring-[#5B1121]">
                                @error('phone')
                                    <p id="phone_error" class="mt-2 text-sm text-[#9A1B34]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-semibold text-[#5B1121]">
                                    {{ __('booking.fields.email') }}
                                </label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="255" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" aria-describedby="@error('email') email_error @enderror" class="mt-2 block w-full rounded-sm border border-[#D8CBB7] bg-[#FFFCF8] px-4 py-3 text-brand-text shadow-xs focus:border-[#5B1121] focus:ring-[#5B1121]">
                                @error('email')
                                    <p id="email_error" class="mt-2 text-sm text-[#9A1B34]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="service_id" class="block text-sm font-semibold text-[#5B1121]">
                                    {{ __('booking.fields.service') }} <span aria-hidden="true">*</span>
                                </label>
                                <select id="service_id" name="service_id" required aria-invalid="{{ $errors->has('service_id') ? 'true' : 'false' }}" aria-describedby="@error('service_id') service_id_error @enderror" class="mt-2 block w-full rounded-sm border border-[#D8CBB7] bg-[#FFFCF8] px-4 py-3 text-brand-text shadow-xs focus:border-[#5B1121] focus:ring-[#5B1121]">
                                    <option value="">{{ __('booking.fields.service_placeholder') }}</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service['id'] }}" @selected((string) old('service_id') === (string) $service['id'])>
                                            {{ $service['name'] }}@if($service['price_summary']) — {{ $service['price_summary'] }}@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('service_id')
                                    <p id="service_id_error" class="mt-2 text-sm text-[#9A1B34]">{{ $message }}</p>
                                @enderror
                                @if($services->isEmpty())
                                    <p class="mt-2 text-sm text-brand-text-muted">{{ __('booking.no_services') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="preferred_date" class="block text-sm font-semibold text-[#5B1121]">
                                    {{ __('booking.fields.preferred_date') }} <span aria-hidden="true">*</span>
                                </label>
                                <input id="preferred_date" name="preferred_date" type="date" value="{{ old('preferred_date') }}" min="{{ $minDate }}" required aria-invalid="{{ $errors->has('preferred_date') ? 'true' : 'false' }}" aria-describedby="@error('preferred_date') preferred_date_error @enderror" class="mt-2 block w-full rounded-sm border border-[#D8CBB7] bg-[#FFFCF8] px-4 py-3 text-brand-text shadow-xs focus:border-[#5B1121] focus:ring-[#5B1121]">
                                @error('preferred_date')
                                    <p id="preferred_date_error" class="mt-2 text-sm text-[#9A1B34]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="preferred_time" class="block text-sm font-semibold text-[#5B1121]">
                                    {{ __('booking.fields.preferred_time') }} <span aria-hidden="true">*</span>
                                </label>
                                <input id="preferred_time" name="preferred_time" type="time" value="{{ old('preferred_time') }}" required aria-invalid="{{ $errors->has('preferred_time') ? 'true' : 'false' }}" aria-describedby="@error('preferred_time') preferred_time_error @enderror" class="mt-2 block w-full rounded-sm border border-[#D8CBB7] bg-[#FFFCF8] px-4 py-3 text-brand-text shadow-xs focus:border-[#5B1121] focus:ring-[#5B1121]">
                                @error('preferred_time')
                                    <p id="preferred_time_error" class="mt-2 text-sm text-[#9A1B34]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="notes" class="block text-sm font-semibold text-[#5B1121]">
                                    {{ __('booking.fields.notes') }}
                                </label>
                                <textarea id="notes" name="notes" rows="5" maxlength="2000" aria-invalid="{{ $errors->has('notes') ? 'true' : 'false' }}" aria-describedby="@error('notes') notes_error @enderror" class="mt-2 block w-full rounded-sm border border-[#D8CBB7] bg-[#FFFCF8] px-4 py-3 text-brand-text shadow-xs focus:border-[#5B1121] focus:ring-[#5B1121]">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p id="notes_error" class="mt-2 text-sm text-[#9A1B34]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="flex items-start gap-3 text-sm leading-relaxed text-brand-text-secondary">
                                <input type="checkbox" name="consent" value="1" @checked(old('consent')) required aria-invalid="{{ $errors->has('consent') ? 'true' : 'false' }}" aria-describedby="@error('consent') consent_error @enderror" class="mt-1 rounded border-[#D8CBB7] text-[#5B1121] focus:ring-[#5B1121]">
                                <span>{{ __('booking.fields.consent') }}</span>
                            </label>
                            @error('consent')
                                <p id="consent_error" class="mt-2 text-sm text-[#9A1B34]">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="inline-flex min-h-12 w-full sm:w-auto items-center justify-center rounded-sm bg-[#5B1121] px-8 py-3 text-sm font-semibold uppercase tracking-widest text-white transition hover:bg-[#4A0D1A] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#5B1121] focus-visible:ring-offset-4">
                            {{ __('booking.submit') }}
                        </button>
                    </form>
                </div>
            </div>
        </x-public.container>
    </section>
</x-layouts.public>
