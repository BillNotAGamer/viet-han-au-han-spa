<?php

declare(strict_types=1);

namespace App\View\Components\Tracking;

use App\Services\Tracking\TrackingConfiguration;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Body extends Component
{
    public function __construct(
        protected TrackingConfiguration $trackingConfiguration
    ) {}

    public function render(): View
    {
        $data = $this->trackingConfiguration->getViewData();

        return view('components.tracking.body', [
            'mode' => $data['mode'],
            'gtmId' => $data['gtmId'],
        ]);
    }
}
