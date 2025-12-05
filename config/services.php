<?php

use Modules\User\Entities\User;

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'paytm' => [
        'env' => env('PAYTM_ENVIRONMENT'),
        'merchant_id' => env('PAYTM_MERCHANT_ID'),
        'merchant_key' => env('PAYTM_MERCHANT_KEY'),
        'merchant_website' => env('PAYTM_MERCHANT_WEBSITE'),
        'channel' => env('PAYTM_CHANNEL'),
        'industry_type' => env('PAYTM_INDUSTRY_TYPE'),
    ],

    'geliver' => [
        'token' => env('GELIVER_API_TOKEN'),
        'sender_address_id' => env('GELIVER_SENDER_ADDRESS_ID'),
        'default_length' => env('GELIVER_DEFAULT_LENGTH', 10.0),
        'default_width' => env('GELIVER_DEFAULT_WIDTH', 10.0),
        'default_height' => env('GELIVER_DEFAULT_HEIGHT', 10.0),
        'default_weight' => env('GELIVER_DEFAULT_WEIGHT', 1.0),
        'test_mode' => env('GELIVER_TEST_MODE', true),
        'webhook_secret' => env('GELIVER_WEBHOOK_SECRET'),
        'cacert' => env('GELIVER_CACERT_PATH'),
    ],
];
