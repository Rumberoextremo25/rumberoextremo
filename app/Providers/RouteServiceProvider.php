<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home'; // Puedes cambiar esto a tu ruta de inicio deseada

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Define aquí cualquier limitador de tasa de peticiones si lo necesitas
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // Mapea las rutas API
            Route::middleware('api') // Aplica el middleware 'api' a estas rutas
                ->prefix('api')      // Añade el prefijo '/api'
                ->group(base_path('routes/api.php')); // Carga el archivo de rutas de la API

            // Mapea las rutas web
            Route::middleware('web') // Aplica el middleware 'web' a estas rutas (sesiones, CSRF, etc.)
                ->group(base_path('routes/web.php')); // Carga el archivo de rutas web
        });
    }
}