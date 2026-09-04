<?php

declare(strict_types=1);

namespace App\View\Components\Tracking;

use App\Services\Tracking\TrackingConfiguration;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Head extends Component
{
    public function __construct(
        protected TrackingConfiguration $trackingConfiguration
    ) {}

    public function render(): View
    {
        $data = $this->trackingConfiguration->getViewData();
        $conversion = session('booking_conversion');

        return view('components.tracking.head', [
            'mode' => $data['mode'],
            'gtmId' => $data['gtmId'],
            'ga4Id' => $data['ga4Id'],
            'metaPixelId' => $data['metaPixelId'],
            'conversion' => is_array($conversion) ? $conversion : null,
        ]);
    }
}
