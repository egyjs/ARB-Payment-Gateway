<?php

// config for Egyjs/Arb
return [
    'mode' => env('ARJ_MODE', 'test'), // test or live
    'test_merchant_endpoint' => 'https://securepayments.alrajhibank.com.sa/pg/payment/tranportal.htm',
    'live_merchant_endpoint' => 'https://digitalpayments.alrajhibank.com.sa/pg/payment/tranportal.htm',
    'test_bank_hosted_endpoint' => 'https://securepayments.alrajhibank.com.sa/pg/payment/hosted.htm',
    'live_bank_hosted_endpoint' => 'https://digitalpayments.alrajhibank.com.sa/pg/payment/hosted.htm',
    'tranportal_id' => env('ARJ_TRANPORTAL_ID'), // your tranportal id
    'tranportal_password' => env('ARJ_TRANPORTAL_PASSWORD'), // your tranportal password
    "resource_key" => env('ARJ_RESOURCE_KEY'), // your resource key
    "currency_code" => env('ARJ_CURRENCY_CODE'),
];
