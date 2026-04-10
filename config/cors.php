<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(
        explode(',', env('CORS_ALLOWED_ORIGINS', '*'))
    ),

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'Accept',
        'X-Internal-Secret',
    ],

    'supports_credentials' => false,
];
