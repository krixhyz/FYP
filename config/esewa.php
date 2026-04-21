<?php

return [
    'product_code' => env('ESEWA_PRODUCT_CODE', 'EPAYTEST'),
    'secret_key' => env('ESEWA_SECRET_KEY'),
    'form_url' => env('ESEWA_FORM_URL', 'https://rc-epay.esewa.com.np/api/epay/main/v2/form'),
    'status_url' => env('ESEWA_STATUS_URL', 'https://rc.esewa.com.np/api/epay/transaction/status/'),
    'success_url' => env('ESEWA_SUCCESS_URL', rtrim(env('APP_URL', 'http://localhost'), '/') . '/payment/esewa/success'),
    'failure_url' => env('ESEWA_FAILURE_URL', rtrim(env('APP_URL', 'http://localhost'), '/') . '/payment/esewa/failure'),
    'refund_url' => env('ESEWA_REFUND_URL'),
    'reservation_minutes' => (int) env('ESEWA_RESERVATION_MINUTES', 15),
];
