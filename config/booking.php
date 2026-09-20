<?php

declare(strict_types=1);

return [
    'notifications' => [
        'email' => [
            'enabled' => (bool) env('BOOKING_NOTIFICATION_EMAIL_ENABLED', false),
            'recipient' => env('BOOKING_NOTIFICATION_EMAIL'),
        ],
    ],
];
