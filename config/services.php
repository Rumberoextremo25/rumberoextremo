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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'platform_bank' => [
        'api' => [
            'base_url' => env('PLATFORM_BANK_API_BASE_URL'),
            'client_id' => env('PLATFORM_BANK_API_CLIENT_ID'),
            'client_secret' => env('PLATFORM_BANK_API_CLIENT_SECRET'),
            'auth_url' => env('PLATFORM_BANK_API_AUTH_URL'),
            'transfer_url' => env('PLATFORM_BANK_API_TRANSFER_URL'),
            'payment_request_url' => env('PLATFORM_BANK_API_PAYMENT_REQUEST_URL'),
            'incoming_verification_url' => env('PLATFORM_BANK_API_INCOMING_VERIFICATION_URL'),
            'webhook_secret' => env('PLATFORM_BANK_WEBHOOK_SECRET'), // Para la validación del webhook
        ],
        'main_account' => [
            'bank_name' => env('PLATFORM_BANK_MAIN_ACCOUNT_BANK_NAME'),
            'account_number' => env('PLATFORM_BANK_MAIN_ACCOUNT_NUMBER'),
            'account_type' => env('PLATFORM_BANK_MAIN_ACCOUNT_TYPE'), // ej. 'Corriente', 'Ahorros'
            'id_number' => env('PLATFORM_BANK_MAIN_ACCOUNT_ID_NUMBER'),
            'account_holder_name' => env('PLATFORM_BANK_MAIN_ACCOUNT_HOLDER_NAME'),
        ],
        'commission_account' => [ // Si usas una cuenta separada para comisiones
            'bank_name' => env('PLATFORM_BANK_COMMISSION_ACCOUNT_BANK_NAME'),
            'account_number' => env('PLATFORM_BANK_COMMISSION_ACCOUNT_NUMBER'),
            'account_type' => env('PLATFORM_BANK_COMMISSION_ACCOUNT_TYPE'),
            'id_number' => env('PLATFORM_BANK_COMMISSION_ACCOUNT_ID_NUMBER'),
            'account_holder_name' => env('PLATFORM_BANK_COMMISSION_ACCOUNT_HOLDER_NAME'),
        ],
    ],

];
