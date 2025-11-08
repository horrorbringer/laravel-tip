<?php

return [
    'mode'        => env('PAYWAY_MODE', 'sandbox'),
    'merchant_id' => env('PAYWAY_MERCHANT_ID'),
    'api_key'     => env('PAYWAY_API_KEY'),
    'return_url'  => env('PAYWAY_RETURN_URL'),
    'cancel_url'  => env('PAYWAY_CANCEL_URL'),

    'base_urls'   => [
        'sandbox'    => 'https://checkout-sandbox.payway.com.kh',
        'production' => 'https://checkout.payway.com.kh',
    ],

    'endpoints'   => [
        'purchase'        => '/api/payment-gateway/v1/payments/purchase',
        'check_transaction'=> '/api/payment-gateway/v1/payments/check-transaction-2',
    ],
];
