<?php

// config for Egyjs/Arb
return [
    'mode' => env('ARB_MODE', 'test'), // test or live
    'test_merchant_endpoint' => 'https://securepayments.alrajhibank.com.sa/pg/payment/tranportal.htm',
    'live_merchant_endpoint' => 'https://digitalpayments.alrajhibank.com.sa/pg/payment/tranportal.htm',
    'test_bank_hosted_endpoint' => 'https://securepayments.alrajhibank.com.sa/pg/payment/hosted.htm',
    'live_bank_hosted_endpoint' => 'https://digitalpayments.alrajhibank.com.sa/pg/payment/hosted.htm',
    'tranportal_id' => env('ARB_TRANPORTAL_ID'),
    'tranportal_password' => env('ARB_TRANPORTAL_PASSWORD'),
    'resource_key' => env('ARB_RESOURCE_KEY'), // your resource key
    'currency_code' => env('ARB_CURRENCY_CODE', '682'),
    'redirect' => [
        'success' => env('ARB_REDIRECT_SUCCESS', 'http://localhost:8000/arb/response'),
        'fail' => env('ARB_REDIRECT_FAIL', 'http://localhost:8000/arb/response'),
    ]
];
