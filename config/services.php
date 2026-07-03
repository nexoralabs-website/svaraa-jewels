<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'razorpay' => [
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Feature Flag
    |--------------------------------------------------------------------------
    |
    | When set to false, customer-facing online payments (card, UPI) are
    | disabled. Cash on Delivery remains available. The entire Razorpay
    | integration, webhook handling, reconciliation, and payment models are
    | preserved and will activate automatically when this is set to true.
    |
    | To re-enable: set PAYMENTS_ENABLED=true in .env — no code changes needed.
    |
    */
    'payments' => [
        'enabled' => env('PAYMENTS_ENABLED', false),
    ],

];
