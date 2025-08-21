<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API de Autenticación del BNC
    |--------------------------------------------------------------------------
    |
    | Estas configuraciones son para obtener el token de sesión de la API.
    | Asegúrate de que los valores se definan en tu archivo .env.
    |
    */
    'auth_api_url' => env('BNC_AUTH_API_URL'),
    'client_guid' => env('BNC_CLIENT_GUID'),
    'master_key' => env('BNC_MASTER_KEY'),
    'merchant_id' => env('BNC_MERCHANT_ID'),

    /*
    |--------------------------------------------------------------------------
    | URLs de Endpoints de Pago
    |--------------------------------------------------------------------------
    |
    | Define las URLs para los diferentes tipos de transacciones de pago
    | con la API del BNC.
    |
    */
    'c2p_api_url' => env('BNC_C2P_API_URL'),
    'vpos_api_url' => env('BNC_VPOS_API_URL'),
    'p2p_api_url' => env('BNC_P2P_API_URL'),
    'banks_api_url' => env('BNC_BANKS_API_URL')
];