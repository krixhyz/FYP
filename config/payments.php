<?php

return [
    'khalti' => [
        'public_key' => env('KHALTI_PUBLIC_KEY'),
        'secret_key' => env('KHALTI_SECRET_KEY'),
        'verify_url' => 'https://khalti.com/api/v2/payment/verify/',
    ],
];