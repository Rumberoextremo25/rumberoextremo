<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // --- AÑADE ESTE BLOQUE DE CÓDIGO ---
        if (config('app.env') === 'production' || env('APP_FORCE_HTTPS', false)) {
            // URL::forceScheme('https'); // Opción 1: Fuerza HTTPS siempre en producción
            // O, si estás detrás de un proxy/load balancer y Laravel no lo detecta:
            $this->app['request']->server->set('HTTPS', 'on'); // Le dice a Laravel que la solicitud es HTTPS
            URL::forceScheme('https'); // Luego fuerza el esquema para todas las URLs generadas

            // Para versiones recientes de Laravel y escenarios con proxies (como Cloudflare), también es crucial
            // configurar el middleware TrustProxies. Lo vemos en el Paso 2.
        }
    }
}
