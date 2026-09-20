<?php

declare(strict_types=1);

namespace App\View\Components\Public;

use App\Services\Booking\BookingRequestCreator;
use App\Services\Booking\BookingServiceCatalog;
use Carbon\CarbonImmutable;
use Illuminate\View\Component;
use Illuminate\View\View;

class BookingModal extends Component
{
    public function __construct(
        protected BookingServiceCatalog $serviceCatalog
    ) {}

    public function render(): View
    {
        $locale = app()->getLocale();
        $minDate = CarbonImmutable::now(BookingRequestCreator::BUSINESS_TIMEZONE)->toDateString();
        $services = $this->serviceCatalog->optionsForLocale($locale);
        $action = $locale === 'en' ? route('en.booking.store') : route('vi.booking.store');

        return view('components.public.booking-modal', [
            'locale' => $locale,
            'services' => $services,
            'minDate' => $minDate,
            'action' => $action,
        ]);
    }
}
