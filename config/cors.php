<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://uat-ws.ngwekhaing.com',
        'https://uat-user.ngwekhaing.com',
        'https://uat-admin.ngwekhaing.com',
    ],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'Accept',
        'X-Internal-Secret',
    ],

    'supports_credentials' => false,
];
