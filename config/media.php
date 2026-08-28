<?php

declare(strict_types=1);

return [
    'disk' => env('MEDIA_DISK', 'public'),
    'max_file_size_kb' => 10240, // 10 MB
    'max_dimension' => 10000,    // 10000x10000 px
    'allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'image/webp',
    ],
    'allowed_extensions' => [
        'jpg',
        'jpeg',
        'png',
        'webp',
    ],
];
