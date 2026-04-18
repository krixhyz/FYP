<?php

$baseUrl = rtrim(env('KHALTI_BASE_URL', 'https://dev.khalti.com'), '/');

return [
    'base_url' => $baseUrl,
    'initiate_url' => env('KHALTI_INITIATE_URL', $baseUrl . '/api/v2/epayment/initiate/'),
    'lookup_url' => env('KHALTI_LOOKUP_URL', $baseUrl . '/api/v2/epayment/lookup/'),
    'secret_key' => env('KHALTI_SECRET_KEY', env('Khalti_live_secret_Key')),
    'public_key' => env('KHALTI_PUBLIC_KEY', env('Khalti_live_public_Key')),
    'website_url' => env('KHALTI_WEBSITE_URL', rtrim(env('APP_URL', 'http://localhost'), '/')),
    'return_url' => env('KHALTI_RETURN_URL', rtrim(env('APP_URL', 'http://localhost'), '/') . '/payment/khalti/return'),
    'refund_url' => env('KHALTI_REFUND_URL'),
];
