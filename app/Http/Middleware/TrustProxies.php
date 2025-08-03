<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    // protected $proxies = [
    //     '192.168.1.1', // Si conoces la IP de tu proxy
    //     '192.168.1.2',
    // ];
    // O, para confiar en todos los proxies (¡menos seguro en algunos entornos!):
    protected $proxies = '*'; // <--- ¡CAMBIA ESTO!

    /**
     * The headers that should be trusted.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO; // <--- ¡ESTE ES CLAVE PARA HTTPS!
}