<?php
// config/gemini.php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Gemini (AI Studio)
    |--------------------------------------------------------------------------
    |
    | La clave se lee desde config (no con env()) para que siga funcionando
    | después de `php artisan config:cache`.
    |
    | Modelo por defecto: gemini-3.5-flash. Los modelos 2.x ya no están
    | disponibles para proyectos nuevos ("no longer available to new users"),
    | por eso se dejan varios nombres como respaldo.
    |
    */
    'api_key' => env('GEMINI_API_KEY'),

    // Versión de la API: generateContent en v1beta es la que soporta los
    // modelos actuales (2.5/3.x) y sus opciones de "thinking".
    'api_url' => env('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta'),

    'model' => env('GEMINI_MODEL', 'gemini-3.5-flash'),

    // Se prueban en orden si el primero devuelve 404/429/503.
    'fallback_models' => [
        'gemini-3.5-flash-lite',
        'gemini-3.1-flash-lite',
        'gemini-flash-latest',
    ],

    'timeout' => (int) env('GEMINI_TIMEOUT', 45),

    // En algunos Windows con proxy/antivirus el bundle de CA local no valida
    // contra Google. Si tu red lo permite, deja GEMINI_VERIFY_TLS=true.
    'verify_tls' => filter_var(env('GEMINI_VERIFY_TLS', false), FILTER_VALIDATE_BOOLEAN),
];
