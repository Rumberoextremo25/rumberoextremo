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
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/faq', [PageController::class, 'faqs'])->name('faq');

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS - AFILIADOS Y ALIADOS
|--------------------------------------------------------------------------
*/
Route::get('/demo-afiliado', [PageController::class, 'afiliado'])->name('demo.afiliado');
Route::post('/afiliados', [PageController::class, 'storeAffiliateApplication'])->name('affiliate.store');
Route::get('/demo-aliados', [PageController::class, 'aliado'])->name('demo.aliado');
Route::post('/contacto-aliados', [PageController::class, 'storeAllyContact'])->name('allies.store');

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS - CONTACTO Y NEWSLETTER
|--------------------------------------------------------------------------
*/
Route::get('/contact', [PageController::class, 'showContactForm'])->name('contact');
Route::post('/contact', [PageController::class, 'storeContactMessage'])->name('contact.store');
Route::post('/newsletter/subscribe', [PageController::class, 'subscribeToNewsletter'])->name('newsletter.subscribe');

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS - CHATBOT
|--------------------------------------------------------------------------
*/
Route::get('/rumberoai/chat', function () {
    return redirect('/')->with('open_chat', true);
})->name('rumberoai.chat');

Route::get('/test-chat', function () {
    return view('test-chat');
});

/*
|--------------------------------------------------------------------------
| RUTAS 2FA (antes del middleware auth)
|--------------------------------------------------------------------------
*/
Route::get('/2fa/verify', [AuthenticatedSessionController::class, 'showTwoFactorForm'])->name('2fa.verify');
Route::post('/2fa/verify', [AuthenticatedSessionController::class, 'verifyTwoFactor'])->name('2fa.verify.post');

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
        // CONFIGURACIÓN Y 2FA
        // ===========================================
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings/change-password', [SettingsController::class, 'changePassword'])->name('settings.change-password');
        Route::post('/settings/toggle-two-factor', [SettingsController::class, 'toggleTwoFactor'])->name('settings.toggle-two-factor');
        Route::post('/settings/verify-two-factor', [SettingsController::class, 'verifyTwoFactor'])->name('settings.verify-two-factor');
        Route::post('/settings/generate-backup-codes', [SettingsController::class, 'generateBackupCodes'])->name('settings.generate-backup-codes');
        Route::post('/settings/update-notifications', [SettingsController::class, 'updateNotifications'])->name('settings.update-notifications');
        Route::post('/settings/update-dark-mode', [SettingsController::class, 'updateDarkMode'])->name('settings.update-dark-mode');

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

            // Acciones POST
            Route::post('/generar-bnc', [PayoutController::class, 'generarArchivoBNC'])->name('generar-bnc');
            Route::post('/confirmar', [PayoutController::class, 'confirmarPagos'])->name('confirmar');
            Route::post('/{payoutId}/revertir', [PayoutController::class, 'revertirPago'])->name('revertir');
            Route::post('/procesar-lote', [PayoutController::class, 'procesarLote'])->name('procesar-lote');
            Route::get('/{payoutId}/confirmar', [PayoutController::class, 'confirmarIndividualForm'])->name('confirmar-individual-form');
            Route::post('/{payoutId}/confirmar-individual', [PayoutController::class, 'confirmarPagoIndividual'])->name('confirmar-individual');
            Route::post('/simular-confirmacion', [PayoutController::class, 'simularConfirmacion'])->name('simular-confirmacion');
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
        Route::post('/{id}/aprobar', [AdminController::class, 'transaccionAprobar'])->name('aprobar')->where('id', '[0-9]+');
        Route::post('/{id}/rechazar', [AdminController::class, 'transaccionRechazar'])->name('rechazar')->where('id', '[0-9]+');
        Route::post('/aprobar-masivas', [AdminController::class, 'aprobarMasivas'])->name('aprobar-masivas');
        Route::post('/rechazar-masivas', [AdminController::class, 'rechazarMasivas'])->name('rechazar-masivas');
    });
});

/*
|--------------------------------------------------------------------------
| RUTAS DEL CHAT ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('api/rumbero-ai')->middleware(['auth'])->group(function () {
    // Rutas públicas para usuarios
    Route::post('/chat', [RumberoAIController::class, 'chat']);
    Route::get('/conversacion', [RumberoAIController::class, 'conversacion']);
    Route::post('/activar-descuento', [RumberoAIController::class, 'activarDescuento']);
    Route::get('/promociones', [RumberoAIController::class, 'promocionesActivas']);

    // Rutas exclusivas para admin
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::get('/admin/pendientes', [RumberoAIController::class, 'mensajesPendientes']);
        Route::post('/admin/responder', [RumberoAIController::class, 'adminResponder']);
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
*/
Route::get('/test-bnc-services', function() {
    $bnc = new \App\Services\BncApiService();
    return response()->json($bnc->checkAvailableServices());
});

/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACIÓN (Breeze/Jetstream)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
