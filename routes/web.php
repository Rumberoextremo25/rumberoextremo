<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CommercialAllyController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SalesStatsController;
use App\Http\Controllers\Admin\SystemAuditController;
use App\Http\Controllers\Admin\DemoVideoController;
use App\Http\Controllers\Admin\HomeSlideController;
use App\Http\Controllers\AllyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\RumberoAIController;
use App\Http\Controllers\QRGeneratorController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\TransaccionController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS - LANDING PAGE
|--------------------------------------------------------------------------
*/
Route::get('/', [PageController::class, 'index'])->name('welcome');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/demo', [PageController::class, 'demo'])->name('demo');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/cookies', [PageController::class, 'cookies'])->name('cookies');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/faq', [PageController::class, 'faqs'])->name('faq');

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS - AFILIADOS Y ALIADOS
|--------------------------------------------------------------------------
| Se aplica throttle para evitar spam en los formularios públicos (antes
| cualquiera podía insertar registros ilimitados en la base de datos).
*/
Route::get('/demo-afiliado', [PageController::class, 'afiliado'])->name('demo.afiliado');
Route::post('/afiliados', [PageController::class, 'storeAffiliateApplication'])
    ->middleware('throttle:6,1')->name('affiliate.store');
Route::get('/demo-aliados', [PageController::class, 'aliado'])->name('demo.aliado');
Route::post('/contacto-aliados', [PageController::class, 'storeAllyContact'])
    ->middleware('throttle:6,1')->name('allies.store');

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS - CONTACTO Y NEWSLETTER
|--------------------------------------------------------------------------
*/
Route::get('/contact', [PageController::class, 'showContactForm'])->name('contact');
Route::post('/contact', [PageController::class, 'storeContactMessage'])
    ->middleware('throttle:6,1')->name('contact.store');
Route::post('/newsletter/subscribe', [PageController::class, 'subscribeToNewsletter'])
    ->middleware('throttle:6,1')->name('newsletter.subscribe');

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS - CHATBOT
|--------------------------------------------------------------------------
*/
Route::get('/rumberoai/chat', function () {
    return redirect('/')->with('open_chat', true);
})->name('rumberoai.chat');

/*
|--------------------------------------------------------------------------
| RUTAS 2FA (antes del middleware auth)
|--------------------------------------------------------------------------
| El código de verificación se limita por IP: sin throttle se podía hacer
| fuerza bruta contra los 6 dígitos del segundo factor.
*/
Route::get('/2fa/verify', [AuthenticatedSessionController::class, 'showTwoFactorForm'])->name('2fa.verify');
Route::post('/2fa/verify', [AuthenticatedSessionController::class, 'verifyTwoFactor'])
    ->middleware('throttle:8,1')->name('2fa.verify.post');

/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACIÓN Y PERFIL
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard (requiere verificación de email)
    Route::middleware(['verified'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/payment-methods', [ProfileController::class, 'updatePaymentMethods'])->name('profile.payments.update');
Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::get('/password/change', [ProfileController::class, 'changePassword'])->name('password.change');
});

/*
|--------------------------------------------------------------------------
| RUTAS DE ADMIN (Protegidas con middleware admin)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.')
    ->group(function () {

        // ===========================================
        // DASHBOARD PRINCIPAL
        // ===========================================
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // ===========================================
        // ESTADÍSTICAS DE VENTAS
        // ===========================================
        Route::get('/sales-stats', [SalesStatsController::class, 'index'])->name('sales.stats');

        // ===========================================
        // SOLICITUDES DE LA WEB (locales y rumberos)
        // ===========================================
        Route::prefix('solicitudes')->name('requests.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\RequestController::class, 'index'])->name('index');
            Route::post('/correo-ventas', [\App\Http\Controllers\Admin\RequestController::class, 'updateSalesEmail'])->name('sales-email');
            Route::put('/{type}/{id}/estado', [\App\Http\Controllers\Admin\RequestController::class, 'updateStatus'])->name('status');
        });

        // ===========================================
        // NOTIFICACIONES PUSH (suscripción del navegador)
        // ===========================================
        Route::prefix('push')->name('push.')->group(function () {
            Route::get('/public-key', [\App\Http\Controllers\Admin\PushSubscriptionController::class, 'publicKey'])->name('public-key');
            Route::post('/subscribe', [\App\Http\Controllers\Admin\PushSubscriptionController::class, 'subscribe'])->name('subscribe');
            Route::post('/unsubscribe', [\App\Http\Controllers\Admin\PushSubscriptionController::class, 'unsubscribe'])->name('unsubscribe');
            Route::post('/test', [\App\Http\Controllers\Admin\PushSubscriptionController::class, 'test'])->name('test');
        });

        // ===========================================
        // NOTIFICACIONES (campana de la toolbar)
        // ===========================================
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
            Route::get('/feed', [\App\Http\Controllers\Admin\NotificationController::class, 'feed'])->name('feed');
            Route::post('/read-all', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('read-all');
            Route::post('/{id}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('read');
        });

        // ===========================================
        // CONFIGURACIÓN Y 2FA
        // ===========================================
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings/change-password', [SettingsController::class, 'changePassword'])->name('settings.change-password');
        Route::post('/settings/toggle-two-factor', [SettingsController::class, 'toggleTwoFactor'])->name('settings.toggle-two-factor');
        Route::post('/settings/verify-two-factor', [SettingsController::class, 'verifyTwoFactor'])->name('settings.verify-two-factor');
        Route::post('/settings/generate-backup-codes', [SettingsController::class, 'generateBackupCodes'])->name('settings.generate-backup-codes');
        Route::post('/settings/update-notifications', [SettingsController::class, 'updateNotifications'])->name('settings.update-notifications');
        Route::post('/settings/update-dark-mode', [SettingsController::class, 'updateDarkMode'])->name('settings.update-dark-mode');
        Route::post('/settings/support', [SettingsController::class, 'updateSupport'])->name('settings.update-support');
        Route::post('/settings/test-ai', [SettingsController::class, 'testAi'])->name('settings.test-ai');

        // ===========================================
        // AUDITORÍA DEL SISTEMA (solo administradores)
        // ===========================================
        Route::prefix('system-audit')->name('system-audit.')->group(function () {
            Route::get('/', [SystemAuditController::class, 'index'])->name('index');
            Route::post('/run', [SystemAuditController::class, 'run'])
                ->middleware('throttle:12,1')->name('run');
            Route::post('/fix', [SystemAuditController::class, 'fix'])
                ->middleware('throttle:20,1')->name('fix');
            Route::get('/export', [SystemAuditController::class, 'export'])->name('export');
            Route::post('/access', [SystemAuditController::class, 'updateAccess'])
                ->middleware('throttle:10,1')->name('access');
        });

        // ===========================================
        // GESTIÓN DE USUARIOS
        // ===========================================
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'usersIndex'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });

        // ===========================================
        // GESTIÓN DE ALIADOS
        // ===========================================
        Route::prefix('aliados')->name('aliados.')->group(function () {
            Route::get('/', [AllyController::class, 'index'])->name('index');
            Route::get('/create', [AllyController::class, 'aliadosCreate'])->name('create');
            Route::post('/', [AllyController::class, 'storeAlly'])->name('store');
            Route::get('/{id}', [AllyController::class, 'show'])->name('show');
            Route::get('/{ally}/edit', [AllyController::class, 'alliesEdit'])->name('edit');
            Route::put('/{ally}', [AllyController::class, 'updateAlly'])->name('update');
            Route::delete('/{ally}', [AllyController::class, 'destroyAlly'])->name('destroy');
        });

        // Utilidad para subcategorías
        Route::get('/get-subcategories', [AllyController::class, 'getSubcategories'])->name('get.subcategories');

        // ===========================================
        // QR GENERATOR
        // ===========================================
        Route::prefix('qr')->name('qr.')->group(function () {
            Route::get('/generate', [QRGeneratorController::class, 'index'])->name('index');
            Route::post('/generate', [QRGeneratorController::class, 'generate'])->name('generate');
            Route::post('/download', [QRGeneratorController::class, 'download'])->name('download');
        });

        // ===========================================
        // REPORTES DE VENTAS
        // ===========================================
        Route::prefix('reports')->name('reports.')->group(function () {
            // Vistas principales
            Route::get('/transactions', [ReportController::class, 'transactions'])->name('transactions');
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');

            // Datos AJAX para gráficos
            Route::get('/transactions/data', [ReportController::class, 'transactionsData'])->name('transactions.data');
            Route::get('/sales/data', [ReportController::class, 'salesData'])->name('sales.data');
            Route::get('/payouts/data', [ReportController::class, 'payoutsData'])->name('payouts.data');

            // Exportaciones PDF
            Route::get('/transactions/export', [ReportController::class, 'exportTransactions'])->name('transactions.export');
            Route::get('/transactions/preview', [ReportController::class, 'previewTransactions'])->name('transactions.preview');
            Route::get('/sales/export', [ReportController::class, 'exportTransactions'])->name('sales.export');
            Route::get('/sales/preview', [ReportController::class, 'previewTransactions'])->name('sales.preview');

            // Métricas del dashboard
            Route::get('/dashboard/metrics', [ReportController::class, 'dashboardMetrics'])->name('dashboard.metrics');

            // Datos en tiempo real
            Route::get('/recent/transactions', [ReportController::class, 'recentTransactions'])->name('recent.transactions');
        });

        // ===========================================
        // MÓDULOS DE CONTENIDO
        // ===========================================
        Route::resource('banners', BannerController::class);
        Route::resource('commercial-allies', CommercialAllyController::class);
        Route::resource('promotions', PromotionController::class);

        // ===========================================
        // VIDEOS DE LA DEMO (carrusel público)
        // ===========================================
        Route::prefix('demo-videos')->name('demo-videos.')->group(function () {
            Route::get('/', [DemoVideoController::class, 'index'])->name('index');
            Route::get('/create', [DemoVideoController::class, 'create'])->name('create');
            Route::post('/', [DemoVideoController::class, 'store'])->name('store');
            Route::get('/{demoVideo}/edit', [DemoVideoController::class, 'edit'])->name('edit');
            Route::put('/{demoVideo}', [DemoVideoController::class, 'update'])->name('update');
            Route::post('/{demoVideo}/toggle', [DemoVideoController::class, 'toggle'])->name('toggle');
            Route::delete('/{demoVideo}', [DemoVideoController::class, 'destroy'])->name('destroy');
        });

        // ===========================================
        // CARRUSEL DEL HOME (imágenes de la portada)
        // ===========================================
        Route::prefix('home-slides')->name('home-slides.')->group(function () {
            Route::get('/', [HomeSlideController::class, 'index'])->name('index');
            Route::get('/create', [HomeSlideController::class, 'create'])->name('create');
            Route::post('/', [HomeSlideController::class, 'store'])->name('store');
            Route::get('/{homeSlide}/edit', [HomeSlideController::class, 'edit'])->name('edit');
            Route::put('/{homeSlide}', [HomeSlideController::class, 'update'])->name('update');
            Route::post('/{homeSlide}/toggle', [HomeSlideController::class, 'toggle'])->name('toggle');
            Route::delete('/{homeSlide}', [HomeSlideController::class, 'destroy'])->name('destroy');
        });

        // ===========================================
        // PAYOUTS
        // ===========================================
        Route::prefix('payouts')->name('payouts.')->group(function () {
            // Vistas principales
            Route::get('/', [PayoutController::class, 'index'])->name('index');
            Route::get('/pendientes', [PayoutController::class, 'pendientes'])->name('pendientes');
            Route::get('/estadisticas', [PayoutController::class, 'estadisticas'])->name('estadisticas');
            Route::get('/dashboard', [PayoutController::class, 'dashboard'])->name('dashboard');
            Route::get('/archivos', [PayoutController::class, 'listarArchivos'])->name('archivos');
            Route::get('/lotes', [PayoutController::class, 'lotes'])->name('lotes');
            Route::get('/resumen-aliado', [PayoutController::class, 'resumenPorAliado'])->name('resumen-aliado');

            // CRUD
            Route::get('/{payoutId}', [PayoutController::class, 'show'])->name('show');
            Route::get('/{payoutId}/edit', [PayoutController::class, 'edit'])->name('edit');
            Route::put('/{payoutId}', [PayoutController::class, 'update'])->name('update');
            Route::get('/{payoutId}/auditoria', [PayoutController::class, 'auditoria'])->name('auditoria');

            // Acciones POST (limitadas por IP: evitan dobles envíos y abuso)
            Route::post('/generar-bnc', [PayoutController::class, 'generarArchivoBNC'])->middleware('throttle:20,1')->name('generar-bnc');
            Route::post('/confirmar', [PayoutController::class, 'confirmarPagos'])->middleware('throttle:20,1')->name('confirmar');
            Route::post('/{payoutId}/revertir', [PayoutController::class, 'revertirPago'])->middleware('throttle:20,1')->name('revertir');
            Route::post('/procesar-lote', [PayoutController::class, 'procesarLote'])->middleware('throttle:20,1')->name('procesar-lote');
            Route::get('/{payoutId}/confirmar', [PayoutController::class, 'confirmarIndividualForm'])->name('confirmar-individual-form');
            Route::post('/{payoutId}/confirmar-individual', [PayoutController::class, 'confirmarPagoIndividual'])->middleware('throttle:20,1')->name('confirmar-individual');
            Route::post('/simular-confirmacion', [PayoutController::class, 'simularConfirmacion'])->middleware('throttle:20,1')->name('simular-confirmacion');
            Route::get('/resumen-aliado/{aliadoId}/detalle', [PayoutController::class, 'detalleAliadoJson'])->name('detalle-aliado-json');
            Route::delete('/{payoutId}', [PayoutController::class, 'destroy'])->name('destroy');

            // Descargas y archivos
            Route::get('/descargar-bnc/{archivo}', [PayoutController::class, 'descargarArchivoBNC'])->name('descargar-bnc');
            Route::delete('/archivos/{archivo}', [PayoutController::class, 'eliminarArchivo'])->name('eliminar-archivo');

            // AJAX / JSON
            Route::get('/datos-graficos', [PayoutController::class, 'datosGraficos'])->name('datos-graficos');
            Route::get('/buscar', [PayoutController::class, 'buscar'])->name('buscar');
            Route::get('/stats', [PayoutController::class, 'getStats'])->name('stats');
            Route::get('/exportar-reporte', [PayoutController::class, 'exportarReporte'])->name('exportar-reporte');

            // Por aliado
            Route::get('/aliado/{aliadoId}', [PayoutController::class, 'porAliado'])->name('por-aliado');
        });
    });

/*
|--------------------------------------------------------------------------
| RUTAS DE TRANSACCIONES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('transacciones')->name('transacciones.')->group(function () {
    Route::get('/', [AdminController::class, 'transaccionesIndex'])->name('index');
    Route::get('/mis-transacciones', [AdminController::class, 'transaccionesIndex'])->name('mis-transacciones');
    Route::get('/exportar', [AdminController::class, 'transaccionesExportar'])->name('exportar');
    Route::get('/{id}/detalle', [AdminController::class, 'transaccionDetalle'])->name('detalle')->where('id', '[0-9]+');
    Route::get('/{id}/comprobante', [AdminController::class, 'transaccionComprobante'])->name('comprobante')->where('id', '[0-9]+');

    // Acciones de admin
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::post('/{id}/aprobar', [AdminController::class, 'transaccionAprobar'])->middleware('throttle:30,1')->name('aprobar')->where('id', '[0-9]+');
        Route::post('/{id}/rechazar', [AdminController::class, 'transaccionRechazar'])->middleware('throttle:30,1')->name('rechazar')->where('id', '[0-9]+');
        Route::post('/aprobar-masivas', [AdminController::class, 'aprobarMasivas'])->middleware('throttle:10,1')->name('aprobar-masivas');
        Route::post('/rechazar-masivas', [AdminController::class, 'rechazarMasivas'])->middleware('throttle:10,1')->name('rechazar-masivas');
    });
});

/*
|--------------------------------------------------------------------------
| RUTAS DEL CHAT ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('api/rumbero-ai')->group(function () {
    // Públicas: el widget del sitio atiende también a visitantes sin cuenta
    // (cada mensaje consume cuota de IA, por eso van con throttle).
    Route::post('/chat', [RumberoAIController::class, 'chat'])->middleware('throttle:30,1');
    Route::get('/conversacion', [RumberoAIController::class, 'conversacion'])->middleware('throttle:60,1');

    Route::middleware(['auth'])->group(function () {
        Route::post('/activar-descuento', [RumberoAIController::class, 'activarDescuento'])->middleware('throttle:10,1');
        Route::get('/promociones', [RumberoAIController::class, 'promocionesActivas']);
    });

    // Rutas exclusivas para admin
    Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::get('/admin/pendientes', [RumberoAIController::class, 'mensajesPendientes']);
        Route::post('/admin/responder', [RumberoAIController::class, 'adminResponder'])->middleware('throttle:60,1');
        Route::post('/admin/marcar-conversacion-leida', [RumberoAIController::class, 'marcarConversacionLeida'])->middleware('throttle:60,1');
    });
});

// Vista del chat admin
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('/admin/chat', [RumberoAIController::class, 'adminChatView'])->name('admin.chat');
});

/*
|--------------------------------------------------------------------------
| RUTAS DE PRUEBA / DEBUG
|--------------------------------------------------------------------------
| Eliminadas: /test-bnc-services instanciaba el servicio bancario en una ruta
| pública (exponía el estado y los errores de la integración BNC). Para
| diagnosticar usar: php artisan bnc:test-connection
*/

/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACIÓN (Breeze/Jetstream)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
