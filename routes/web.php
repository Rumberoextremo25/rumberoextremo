<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CommercialAllyController;
use App\Http\Controllers\Admin\PromotionController;
use Illuminate\Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AllyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\RumberoAIController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\PayoutController;

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
| RUTAS DE ADMIN (Protegidas con middleware admin usando la clase directamente)
|--------------------------------------------------------------------------
*/


// ✅ RUTAS 2FA - AGREGAR AQUÍ
Route::get('/2fa/verify', [AuthenticatedSessionController::class, 'showTwoFactorForm'])->name('2fa.verify');
Route::post('/2fa/verify', [AuthenticatedSessionController::class, 'verifyTwoFactor'])->name('2fa.verify.post');


Route::prefix('admin')
    ->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->name('admin.')
    ->group(function () {

        // ===========================================
        // CONFIGURACIÓN Y 2FA
        // ===========================================
        // Configuración
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings/change-password', [SettingsController::class, 'changePassword'])->name('settings.change-password');
        Route::post('/settings/toggle-two-factor', [SettingsController::class, 'toggleTwoFactor'])->name('settings.toggle-two-factor');
        Route::post('/settings/verify-two-factor', [SettingsController::class, 'verifyTwoFactor'])->name('settings.verify-two-factor');
        Route::post('/settings/generate-backup-codes', [SettingsController::class, 'generateBackupCodes'])->name('settings.generate-backup-codes');
        Route::post('/settings/update-notifications', [SettingsController::class, 'updateNotifications'])->name('settings.update-notifications');
        Route::post('/settings/update-dark-mode', [SettingsController::class, 'updateDarkMode'])->name('settings.update-dark-mode');

        // ===========================================
        // GESTIÓN DE USUARIOS (UserController)
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
        // GESTIÓN DE ALIADOS (AllyController)
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
        // REPORTES DE VENTAS
        // ===========================================
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/sales/data', [ReportController::class, 'salesData'])->name('sales.data');
            Route::get('/sales/export', [ReportController::class, 'exportSales'])->name('export');
            Route::get('/sales/preview', [ReportController::class, 'exportSalesPreview'])->name('preview');
            Route::get('/sales/metrics', [ReportController::class, 'dashboardMetrics'])->name('metrics');
        });

        // ===========================================
        // MÓDULOS DE CONTENIDO (Banners, Aliados Comerciales, Promociones)
        // ===========================================
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('banners', BannerController::class);
        Route::resource('commercial-allies', CommercialAllyController::class);
        Route::resource('promotions', PromotionController::class);

        // ===========================================
        // PAYOUTS (TODAS LAS RUTAS UNIFICADAS)
        // ===========================================
        Route::prefix('payouts')->name('payouts.')->group(function () {

            // VISTAS PRINCIPALES
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

            // ACCIONES POST
            Route::post('/generar-bnc', [PayoutController::class, 'generarArchivoBNC'])->name('generar-bnc');
            Route::post('/confirmar', [PayoutController::class, 'confirmarPagos'])->name('confirmar');
            Route::post('/{payoutId}/revertir', [PayoutController::class, 'revertirPago'])->name('revertir');
            Route::post('/procesar-lote', [PayoutController::class, 'procesarLote'])->name('procesar-lote');
            Route::get('/{payoutId}/confirmar', [PayoutController::class, 'confirmarIndividualForm'])->name('confirmar-individual-form');
            Route::post('/{payoutId}/confirmar-individual', [PayoutController::class, 'confirmarPagoIndividual'])->name('confirmar-individual');
            Route::post('/simular-confirmacion', [PayoutController::class, 'simularConfirmacion'])->name('simular-confirmacion');
            Route::get('/payouts/resumen-aliado/{aliadoId}/detalle', [PayoutController::class, 'detalleAliadoJson'])->name('payouts.detalle-aliado-json');
            Route::delete('admin/payouts/{payoutId}', [PayoutController::class, 'destroy'])->name('destroy');
            // DESCARGAS Y ARCHIVOS
            Route::get('/descargar-bnc/{archivo}', [PayoutController::class, 'descargarArchivoBNC'])->name('descargar-bnc');
            Route::delete('/archivos/{archivo}', [PayoutController::class, 'eliminarArchivo'])->name('eliminar-archivo');

            // AJAX / JSON
            Route::get('/datos-graficos', [PayoutController::class, 'datosGraficos'])->name('datos-graficos');
            Route::get('/buscar', [PayoutController::class, 'buscar'])->name('buscar');
            Route::get('/stats', [PayoutController::class, 'getStats'])->name('stats');
            Route::get('/exportar-reporte', [PayoutController::class, 'exportarReporte'])->name('exportar-reporte');

            // POR ALIADO
            Route::get('/aliado/{aliadoId}', [PayoutController::class, 'porAliado'])->name('por-aliado');
        });
    });

/*
|--------------------------------------------------------------------------
| RUTAS DE TRANSACCIONES (UNIFICADAS)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('transacciones')->name('transacciones.')->group(function () {

    // Rutas principales (usando controlador)
    Route::get('/', [AdminController::class, 'transaccionesIndex'])->name('index');
    Route::get('/mis-transacciones', [AdminController::class, 'transaccionesIndex'])->name('mis-transacciones');
    Route::get('/exportar', [AdminController::class, 'transaccionesExportar'])->name('exportar');

    // Rutas con parámetros
    Route::get('/{id}/detalle', [AdminController::class, 'transaccionDetalle'])->name('detalle')->where('id', '[0-9]+');

    // 👇 CORREGIDA: Sin el prefijo 'admin/transacciones' adicional
    Route::get('/{id}/comprobante', [AdminController::class, 'transaccionComprobante'])->name('comprobante')->where('id', '[0-9]+');

    // Acciones de admin (requieren admin)
    Route::middleware([\App\Http\Middleware\AdminMiddleware::class])->group(function () {
        Route::post('/{id}/aprobar', [AdminController::class, 'transaccionAprobar'])->name('aprobar')->where('id', '[0-9]+');
        Route::post('/{id}/rechazar', [AdminController::class, 'transaccionRechazar'])->name('rechazar')->where('id', '[0-9]+');

        // 👇 CORREGIDAS: Quitar 'transacciones/' del path porque ya estamos en prefijo 'transacciones'
        Route::post('/aprobar-masivas', [AdminController::class, 'aprobarMasivas'])->name('aprobar-masivas');
        Route::post('/rechazar-masivas', [AdminController::class, 'rechazarMasivas'])->name('rechazar-masivas');
    });
});

// Rutas de pruebas

Route::get('/admin/settings/test-2fa', [SettingsController::class, 'testCurrentCode'])->name('settings.test-2fa');

Route::get('/test-success', function() {
    return '✅ Login exitoso! Usuario: ' . Auth::user()->email . ' ID: ' . Auth::id();
})->middleware('auth');

/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACIÓN (Breeze/Jetstream)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
