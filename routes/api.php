<?php

use App\Http\Controllers\Api\AllyController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\RumberoAIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ===========================================
// RUTAS PÚBLICAS (NO REQUIEREN AUTENTICACIÓN)
// ===========================================

// ========== AUTENTICACIÓN ==========
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/verify-2fa', [AuthController::class, 'verifyTwoFactor'])->name('verify-2fa');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('forgot-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
});

// ========== VERIFICACIÓN DE EMAIL ==========
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

// ========== HOME / DATOS PÚBLICOS ==========
Route::get('/home-data', [HomeController::class, 'index'])->name('api.home-data');

// ========== BANCOS ==========
Route::prefix('banks')->name('api.banks.')->group(function () {
    Route::post('/list', [BankController::class, 'index'])->name('list');
    Route::get('/daily-dollar-rate', [BankController::class, 'getDailyDollarRate'])->name('daily-dollar-rate');
});

// ========== ALIADOS PÚBLICOS ==========
Route::get('/aliados', [AllyController::class, 'index'])->name('api.aliados.index');
Route::get('/aliados/{user_id}', [AllyController::class, 'show'])->name('api.aliados.show');

// ========== RUMBERO AI - RUTAS PÚBLICAS ==========
Route::get('/categorias', [RumberoAIController::class, 'getCategorias'])->name('api.categorias');
Route::post('/ia/chat', [RumberoAIController::class, 'chat'])->name('api.ia.chat');

// ========== PAGOS PÚBLICOS ==========
Route::prefix('pagos')->name('api.pagos.')->group(function () {
    Route::post('/c2p', [PaymentController::class, 'initiateC2PPayment']);
    Route::post('/tarjeta', [PaymentController::class, 'processCardPayment']);
    Route::post('/solicitar', [PaymentController::class, 'solicitarDebito']);
    Route::post('/emitir', [PaymentController::class, 'emitirDebito']);
    Route::post('/reenviar-sms', [PaymentController::class, 'reenviarSms']);
});

// ===========================================
// RUTAS PROTEGIDAS CON SANCTUM (REQUIEREN AUTENTICACIÓN)
// ===========================================
Route::middleware('auth:sanctum')->group(function () {

    // ========== USUARIO Y PERFIL ==========
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('api.user');
    
    Route::get('/profile', [AuthController::class, 'profile'])->name('api.profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('api.profile.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    
    // ========== CAMBIO DE CONTRASEÑA ==========
    Route::post('/password/update', [AuthController::class, 'updatePassword'])->name('api.password.update');
    Route::post('/password/confirm', [AuthController::class, 'confirmPassword'])->name('api.password.confirm');
    
    // ========== VERIFICACIÓN DE EMAIL ==========
    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])->name('api.email.resend');
    
    // ========== 2FA ==========
    Route::prefix('2fa')->name('api.2fa.')->group(function () {
        Route::post('/enable', [AuthController::class, 'enableTwoFactor'])->name('enable');
        Route::post('/confirm', [AuthController::class, 'confirmTwoFactor'])->name('confirm');
        Route::post('/disable', [AuthController::class, 'disableTwoFactor'])->name('disable');
    });
    
    // ========== TIPO DE USUARIO ==========
    Route::get('/user-type', [AuthController::class, 'getUserType'])->name('api.user-type');
    
    // ========== PRODUCTOS ==========
    Route::apiResource('products', ProductController::class)->names([
        'index' => 'api.products.index',
        'store' => 'api.products.store',
        'show' => 'api.products.show',
        'update' => 'api.products.update',
        'destroy' => 'api.products.destroy',
    ]);
    
    // ========== RUMBERO AI - RUTAS PROTEGIDAS ==========
    Route::prefix('ia')->name('api.ia.')->group(function () {
        Route::post('/activar-descuento', [RumberoAIController::class, 'activarDescuento'])->name('activar-descuento');
        Route::get('/promociones', [RumberoAIController::class, 'promocionesActivas'])->name('promociones');
        Route::get('/historial', [RumberoAIController::class, 'historial'])->name('historial');
        Route::get('/mis-descuentos', [RumberoAIController::class, 'misDescuentos'])->name('mis-descuentos');
        Route::post('/usar-descuento/{codigo}', [RumberoAIController::class, 'usarDescuento'])->name('usar-descuento');
    });

    // ========== PAYOUTS (ADMIN) ==========
    Route::prefix('pagos/payouts')->name('api.pagos.payouts.')->middleware(['admin'])->group(function () {
        Route::get('/pendientes', [PaymentController::class, 'obtenerPagosPendientes'])->name('pendientes');
        Route::get('/filtro', [PaymentController::class, 'obtenerPagosPorFiltro'])->name('filtro');
        Route::get('/estadisticas', [PaymentController::class, 'obtenerEstadisticasPayouts'])->name('estadisticas');
        Route::post('/generar-archivo-bnc', [PaymentController::class, 'generarArchivoPagosBNC'])->name('generar-archivo-bnc');
        Route::post('/confirmar', [PaymentController::class, 'confirmarPagosProcesados'])->name('confirmar');
        Route::get('/descargar-archivo-bnc/{archivo}', [PaymentController::class, 'descargarArchivoBNC'])->name('descargar-archivo-bnc');
        Route::post('/revertir/{payoutId}', [PaymentController::class, 'revertirPago'])->name('revertir');
    });
});
