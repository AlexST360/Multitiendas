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

    /*
    |--------------------------------------------------------------------------
    | Transbank — Webpay Plus
    |--------------------------------------------------------------------------
    |
    | 'environment' puede ser 'integration' (sandbox) o 'production'.
    | En production las credenciales vienen del panel Transbank.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | MercadoPago — Checkout Pro
    |--------------------------------------------------------------------------
    |
    | 'environment' puede ser 'sandbox' o 'production'.
    | En sandbox usar access_token que empieza con TEST-...
    |
    */

    'mercadopago' => [
        'environment'  => env('MP_ENV', 'sandbox'),
        'access_token' => env('MP_ACCESS_TOKEN', ''),
    ],

    'transbank' => [
        'environment'   => env('TRANSBANK_ENV', 'integration'),
        'commerce_code' => env('TRANSBANK_COMMERCE_CODE', '597055555532'),
        'api_key'       => env('TRANSBANK_API_KEY', '579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C'),
    ],

];
