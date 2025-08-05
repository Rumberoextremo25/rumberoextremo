<?php

return [
    /**
     * Configuración para la autenticación y endpoints de la API del BNC.
     * Asegúrate de que estas variables estén definidas en tu archivo .env.
     */
    'auth_api_url' => env('BNC_AUTH_API_URL'),
    'client_guid' => env('BNC_CLIENT_GUID'),
    'master_key' => env('BNC_MASTER_KEY'),
    'merchant_id' => env('BNC_MERCHANT_ID'),

    /**
     * Endpoints específicos para los servicios de pago del BNC.
     */
    'c2p_api_url' => env('BNC_C2P_API_URL'),
    'vpos_api_url' => env('BNC_VPOS_API_URL'),

    /**
     * Endpoint para obtener la lista de bancos del BNC.
     * Si la lista de bancos proviene de una API del BNC, esta es la URL.
     */
    'banks_api_url' => env('BNC_BANKS_API_URL'),

    /**
     * Configuración para la API de tasas de cambio del BCV (Banco Central de Venezuela).
     * Esta URL puede ser un servicio de terceros o un scraper interno si el BCV no tiene API directa.
     */
    'rates_api_url' => env('BCV_RATES_API_URL'),
    'cache_duration_minutes' => env('BCV_RATES_CACHE_MINUTES', 60), // Duración de la caché para las tasas del BCV en minutos (por defecto 60)
];