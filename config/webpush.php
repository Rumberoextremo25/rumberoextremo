<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Web Push (VAPID)
    |--------------------------------------------------------------------------
    |
    | Genera las claves con:
    |   php artisan webpush:vapid
    |
    | La clave pública viaja al navegador (applicationServerKey); la privada
    | nunca sale del servidor y se usa para firmar el JWT de cada envío.
    |
    */

    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:' . env('MAIL_FROM_ADDRESS', 'soporte@rumberoextremo.com')),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tiempo de vida del mensaje (segundos)
    |--------------------------------------------------------------------------
    */

    'ttl' => (int) env('WEBPUSH_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Apariencia de la notificación del sistema
    |--------------------------------------------------------------------------
    */

    'icon' => env('WEBPUSH_ICON', '/assets/img/logo_sidebar.png'),

];
