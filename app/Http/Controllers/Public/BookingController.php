<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreBookingRequest;
use App\Services\Booking\BookingRequestCreator;
use App\Services\Booking\BookingServiceCatalog;
use App\Services\Seo\SeoManager;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        protected BookingServiceCatalog $serviceCatalog,
        protected BookingRequestCreator $bookingCreator,
        protected SeoManager $seoManager
    ) {}

    public function create(Request $request): View
    {
        $locale = app()->getLocale();

        $request->attributes->set('localized_urls', [
            'vi' => route('vi.booking.create'),
            'en' => route('en.booking.create'),
        ]);

        $seo = $this->seoManager->composeForListing('booking', $locale);

        return view('public.booking.create', [
            'locale' => $locale,
            'title' => __('booking.meta.title'),
            'services' => $this->serviceCatalog->optionsForLocale($locale),
            'action' => $locale === 'en' ? route('en.booking.store') : route('vi.booking.store'),
            'minDate' => CarbonImmutable::now(BookingRequestCreator::BUSINESS_TIMEZONE)->toDateString(),
            'contactHref' => $locale === 'en' ? route('en.booking.create') : route('vi.booking.create'),
            'seo' => $seo,
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $locale = app()->getLocale();

        $booking = $this->bookingCreator->create($request->validatedBookingData(), $locale, $request);

        return redirect()
            ->route($locale === 'en' ? 'en.booking.create' : 'vi.booking.create')
            ->with('booking_status', __('booking.success'))
            ->with('booking_conversion', [
                'locale' => $locale,
                'service_id' => $booking->service_id,
            ]);
    }
}
