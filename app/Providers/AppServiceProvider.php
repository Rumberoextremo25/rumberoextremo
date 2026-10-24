<?php

namespace App\Providers;

use App\Models\Ally;
use App\Models\Banner;
use App\Models\CommercialAlly;
use App\Models\Promotion;
use App\Models\User;
use App\Observers\AdminActivityObserver;
use App\Services\AdminActivityNotifier;
use App\Services\BncApiService;
use App\Services\DataCypher;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BncApiService::class, function ($app) {
            return new BncApiService($app->make(DataCypher::class));
        });

        $this->app->singleton(DataCypher::class, function ($app) {
            // Se lee desde config, no con env(): env() devuelve null cuando el
            // proyecto está cacheado (php artisan config:cache) y la clave de
            // cifrado del banco quedaría vacía.
            $masterKey = config('services.bnc.master_key')
                ?: config('bnc.master_key')
                ?: getenv('BNC_MASTER_KEY')
                ?: ($_SERVER['BNC_MASTER_KEY'] ?? null)
                ?: ($_ENV['BNC_MASTER_KEY'] ?? null);

            return new DataCypher($masterKey);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fuerza HTTPS en las URLs generadas. No se toca el objeto Request:
        // marcarlo como seguro sin serlo hacía que la aplicación creyera que
        // la conexión estaba cifrada (y afectaba a las cookies Secure).
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }

        $this->registerActivityObservers();
        $this->registerNotificationComposer();
    }

    /**
     * Notificaciones reales: cualquier creación, edición o borrado de estas
     * entidades (desde el panel, la API o artisan) avisa a los administradores.
     */
    private function registerActivityObservers(): void
    {
        $observer = new AdminActivityObserver($this->app->make(AdminActivityNotifier::class));

        foreach ([User::class, Ally::class, CommercialAlly::class, Promotion::class, Banner::class] as $model) {
            $model::observe($observer);
        }
    }

    /**
     * Alimenta la campana de la toolbar con las notificaciones del usuario.
     * Se resuelve aquí para que esté disponible en cualquier página del panel.
     */
    private function registerNotificationComposer(): void
    {
        View::composer('layouts.nav.admin-toolbar', function ($view) {
            if (! auth()->check()) {
                return;
            }

            try {
                if (! Schema::hasTable('notifications')) {
                    $view->with(['toolbarNotifications' => collect(), 'toolbarUnread' => 0]);

                    return;
                }

                $user = auth()->user();

                $view->with([
                    'toolbarNotifications' => $user->notifications()->latest()->limit(12)->get(),
                    'toolbarUnread' => $user->unreadNotifications()->count(),
                ]);
            } catch (\Throwable) {
                $view->with(['toolbarNotifications' => collect(), 'toolbarUnread' => 0]);
            }
        });
    }
}
