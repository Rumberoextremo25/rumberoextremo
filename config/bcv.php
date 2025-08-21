<?php

return [
    /**
     * Configuración para la API de tasas de cambio del BCV (Banco Central de Venezuela).
     * Esta URL puede ser un servicio de terceros o un scraper interno si el BCV no tiene API directa.
     */
    'rates_api_url' => env('BCV_RATES_API_URL'),
    
    /**
     * Duración de la caché para las tasas del BCV en minutos.
     * Por defecto es 60 minutos (1 hora).
     */
    'cache_duration_minutes' => env('BCV_RATES_CACHE_MINUTES', 60),
];