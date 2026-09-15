<?php

return [
    'auth_api_url'           => env('BNC_AUTH_API_URL'),
    'c2p_api_url'            => env('BNC_C2P_API_URL'),
    'vpos_api_url'           => env('BNC_VPOS_API_URL'),
    'validation_api_url'     => env('BNC_P2P_API_URL'),
    'banks_api_url'          => env('BNC_BANKS_API_URL'),
    'rates_api_url'          => env('BNC_RATES_API_URL'),
    'legacy_login_url'       => env('BNC_LEGACY_LOGIN_URL'),
    'debit_token_request_url'=> env('BNC_DEBITO_SOLICITAR_URL'),
    'debit_beginner_url'     => env('BNC_DEBITO_EMITIR_URL'),
    'debit_reenviar_url'     => env('BNC_DEBITO_REENVIAR_URL'),

    'client_guid'            => env('BNC_CLIENT_GUID'),
    'master_key'             => env('BNC_MASTER_KEY'),
    'merchant_id'            => env('BNC_MERCHANT_ID'),
];