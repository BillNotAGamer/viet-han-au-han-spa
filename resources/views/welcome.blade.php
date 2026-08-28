<x-public-layout>
    <x-public.section bg="ivory" spacing="spacious">
        <x-public.container>
            <x-public.section-heading
                :eyebrow="__('common.phase_status')"
                :title="__('common.under_construction')"
                :subtitle="__('common.development_notice', ['brand' => __('common.brand_name')])"
            >
                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <x-public.button as="a" href="{{ app()->getLocale() === 'vi' ? url('/lien-he') : url('/en/contact') }}" variant="primary" size="md">
                        {{ __('navigation.book_now') }}
                    </x-public.button>

                    <x-public.button as="a" href="{{ app()->getLocale() === 'vi' ? url('/dich-vu') : url('/en/services') }}" variant="secondary" size="md">
                        {{ __('navigation.services') }}
                    </x-public.button>
                </div>
            </x-public.section-heading>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-3xl mx-auto mt-12">
                <x-public.card>
                    <div class="text-xs font-semibold uppercase tracking-wider text-brand-text-muted">Design Tokens</div>
                    <div class="text-lg font-bold text-brand-primary mt-1">Burgundy & Gold</div>
                </x-public.card>
                <x-public.card>
                    <div class="text-xs font-semibold uppercase tracking-wider text-brand-text-muted">Styling</div>
                    <div class="text-lg font-bold text-brand-primary mt-1">Tailwind CSS 4</div>
                </x-public.card>
                <x-public.card>
                    <div class="text-xs font-semibold uppercase tracking-wider text-brand-text-muted">Interactivity</div>
                    <div class="text-lg font-bold text-brand-primary mt-1">Alpine.js</div>
                </x-public.card>
                <x-public.card>
                    <div class="text-xs font-semibold uppercase tracking-wider text-brand-text-muted">Localization</div>
                    <div class="text-lg font-bold text-brand-primary mt-1">{{ strtoupper(app()->getLocale()) }} (Native)</div>
                </x-public.card>
            </div>
        </x-public.container>
    </x-public.section>
</x-public-layout>
