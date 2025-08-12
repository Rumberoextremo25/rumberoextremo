<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AllyController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\BcvRatesController;
use App\Http\Controllers\PaymentSettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas para Subcategorías
// GET /api/subcategories
// GET /api/subcategories/{id}
// POST /api/subcategories
// PUT/PATCH /api/subcategories/{id}
// DELETE /api/subcategories/{id}
Route::middleware('auth:sanctum')->group(function () {
    // Rutas protegidas
    Route::apiResource('products', ProductController::class); // Si tu dashboard usa esto
    // ... otras rutas de gestión
});

// Puedes mantener esta ruta si la usas para desarrollo, pero considera los seeders
Route::post('populate-db', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh --seed');
    return response()->json(['success' => true, 'message' => 'Base de datos refrescada y sembrada.']);
});

// Ruta para obtener todos los aliados (GET /api/aliados)
Route::get('/aliados', [AllyController::class, 'index']);
Route::get('/aliados/{user_id}', [AllyController::class, 'show']);

//Rutas para el HomeFragment de la Aplicación
Route::get('home-data', [HomeController::class, 'index']);

Route::get('/test-route', function () {
    return 'Test successful!';
});

// Rutas públicas (no requieren autenticación)
Route::post('login', [AuthController::class, 'login']);

// Rutas protegidas por Sanctum (requieren un token de acceso válido)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

// Rutas para el controlador de Payment
Route::prefix('payments')->group(function () {
    // Ruta para iniciar un pago C2P (Pago Móvil)
    Route::post('/c2p', [PaymentController::class, 'initiateC2PPayment']);
    // Ruta para procesar un pago con tarjeta (VPOS)
    Route::post('/card', [PaymentController::class, 'processCardPayment']);
});

// --- Rutas para los Webhooks (WebhookController) ---
Route::prefix('webhooks')->group(function () {
    // Webhook para notificaciones de pagos C2P
    Route::post('/bnc/c2p', [WebhookController::class, 'handleC2PWebhook']);
    // Webhook para notificaciones de pagos con tarjeta (VPOS)
    Route::post('/bnc/card', [WebhookController::class, 'handleCardWebhook']);
});

// --- Ruta para obtener la lista de Bancos (BankController) ---
Route::get('/banks', [BankController::class, 'index']);
// --- Ruta para obtener las tasas del BCV (BankController) ---
Route::get('/Services/BCVRates', [BankController::class, 'getBcvRates']);

// Rutas protegidas que requieren autenticación con token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Otros endpoints de usuario, ej:
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
});

//Ruta para guardar los detalles de Pago Móvil (C2P)

Route::post('/user/c2p-settings', [PaymentSettingsController::class, 'saveC2PDetails']);
Route::get('/user/c2p-settings', [PaymentSettingsController::class, 'getC2PDetails']);

Route::get('/user', function (Request $request) {
    return $request->user();
});
