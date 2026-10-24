<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content-Security-Policy
    |--------------------------------------------------------------------------
    |
    | csp_enforce:
    |   true  -> se envía la cabecera Content-Security-Policy (bloquea).
    |   false -> se envía Content-Security-Policy-Report-Only (solo informa).
    |
    | En producción conviene activarla. Por defecto queda desactivada en local
    | porque las vistas incluyen scripts/estilos en línea y rompería el panel.
    |
    */

    'csp_enforce' => env('SECURITY_CSP_ENFORCE', env('APP_ENV', 'production') === 'production'),

    /*
    |--------------------------------------------------------------------------
    | Orígenes de script adicionales
    |--------------------------------------------------------------------------
    |
    | Lista separada por espacios de dominios extra permitidos en script-src
    | (por ejemplo el CDN de un widget de analítica o de chat).
    |
    */

    'csp_extra_script_src' => env('SECURITY_CSP_EXTRA_SCRIPT_SRC', ''),

    /*
    |--------------------------------------------------------------------------
    | Proxies de confianza
    |--------------------------------------------------------------------------
    |
    | Lista separada por comas de IPs/CIDR de los proxies (load balancer,
    | Cloudflare, Laragon…). Nunca usar "*" en producción: permitiría falsear
    | la IP del cliente y por tanto los límites de peticiones y los logs.
    |
    */

    'trusted_proxies' => env('TRUSTED_PROXIES', ''),

];
