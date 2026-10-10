<?php

declare(strict_types=1);

return [
    'google_sheets' => [
        'enabled' => (bool) env('GOOGLE_SHEETS_BOOKING_ENABLED', false),
        'spreadsheet_id' => env('GOOGLE_SHEETS_SPREADSHEET_ID'),
        'tab_name' => env('GOOGLE_SHEETS_TAB_NAME'),
        'credentials_file' => env('GOOGLE_SHEETS_CREDENTIALS_FILE'),
        'batch_limit' => (int) env('GOOGLE_SHEETS_BOOKING_BATCH_LIMIT', 25),
        'max_batch_limit' => 200,
        'lease_seconds' => 120,
        'max_attempts' => 5,
        'retry_base_seconds' => 60,
        'expected_headers' => [
            'NGÀY',
            'DỊCH VỤ',
            'TÊN KHÁCH HÀNG',
            'SỐ ĐIỆN THOẠI',
            'GIỜ MONG MUỐN',
            'MÃ ĐẶT LỊCH',
        ],
    ],

    'notifications' => [
        'email' => [
            'enabled' => (bool) env('BOOKING_NOTIFICATION_EMAIL_ENABLED', false),
            'recipient' => env('BOOKING_NOTIFICATION_EMAIL'),
        ],
    ],
];
