<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
        |----------------------------------------------------------------------
        | Proxies de confianza
        |----------------------------------------------------------------------
        | Antes existía App\Http\Middleware\TrustProxies con `$proxies = '*'`,
        | que permite falsear la IP del cliente (y con ella los límites de
        | intentos de login y los registros de auditoría). Ahora se configura
        | por entorno y, si no se define nada, no se confía en ningún proxy.
        |
        | Nota: aquí se usa env() y no config(), porque este closure se ejecuta
        | antes de que el repositorio de configuración esté disponible.
        */
        $proxies = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('TRUSTED_PROXIES', ''))
        )));

        $middleware->trustProxies(
            at: $proxies === [] ? null : $proxies,
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        /*
        |----------------------------------------------------------------------
        | Alias de middleware
        |----------------------------------------------------------------------
        | 'admin' se usa en routes/api.php pero nunca se registró (las rutas de
        | payouts de la API fallaban con "Target class [admin] does not exist").
        | Aquí queda registrado de verdad.
        */
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'user.status' => CheckUserStatus::class,
        ]);

        /*
        |----------------------------------------------------------------------
        | Grupo web
        |----------------------------------------------------------------------
        | - CheckUserStatus bloquea a usuarios suspendidos (antes era código
        |   muerto: el archivo app/Http/Kernel.php no se usa en Laravel 12).
        | - SecurityHeaders añade CSP, HSTS, no-store, anti-clickjacking…
        */
        $middleware->web(append: [
            CheckUserStatus::class,
            SecurityHeaders::class,
        ]);

        /*
        |----------------------------------------------------------------------
        | CSRF
        |----------------------------------------------------------------------
        | Los webhooks de pagos llegan desde servidores externos sin token CSRF.
        | Se excluyen explícitamente y solo esos (la API ya está fuera del grupo
        | web). Cualquier ruta web nueva sigue protegida por defecto.
        */
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'api/*',
            'pago/webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Nunca rellenar de vuelta estos campos en un redirect con errores
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
            'card_number',
            'cvv',
            'token',
        ]);
    })->create();
