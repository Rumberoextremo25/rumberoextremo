<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AllyController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Api\BncController;
use App\Services\BncApiService;
use Illuminate\Support\Facades\Http;

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

// Rutas de administración para payouts
Route::prefix('admin')->name('admin.')->group(function () {
    // Página principal de payouts
    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
    
    // Generar archivo BNC
    Route::post('/payouts/generate-bnc', [PayoutController::class, 'generateBncFile'])->name('payouts.generate_bnc');
    
    // Confirmar pagos
    Route::post('/payouts/confirm', [PayoutController::class, 'confirmPayouts'])->name('payouts.confirm');
    
    // ✅ AGREGAR ESTA RUTA SI LA NECESITAS
    Route::get('/payouts/pending', [PayoutController::class, 'pending'])->name('payouts.pending');
});

// O si prefieres resource routes:
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('payouts', PayoutController::class)->only([
        'index', 'create', 'store', 'show'
    ]);
    
    // Rutas adicionales para payouts
    Route::post('payouts/generate-bnc', [PayoutController::class, 'generateBncFile'])->name('payouts.generate_bnc');
    Route::post('payouts/confirm', [PayoutController::class, 'confirmPayouts'])->name('payouts.confirm');
    Route::get('payouts/pending', [PayoutController::class, 'pending'])->name('payouts.pending');
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
    // Pagos principales
    Route::post('/c2p', [PaymentController::class, 'initiateC2PPayment']);
    Route::post('/card', [PaymentController::class, 'processCardPayment']);
    Route::post('/p2p', [PaymentController::class, 'processP2PPayment']);
    
    // Gestión de pagos a proveedores
    Route::prefix('payouts')->group(function () {
        Route::post('/generar-archivo', [PaymentController::class, 'generarArchivoPagosBNC']);
        Route::get('/descargar/{archivo}', [PaymentController::class, 'descargarArchivoBNC']);
        Route::post('/confirmar', [PaymentController::class, 'confirmarPagosProcesados']);
        Route::get('/pendientes', [PaymentController::class, 'obtenerPagosPendientes']);
        Route::get('/filtro', [PaymentController::class, 'obtenerPagosPorFiltro']);
    });
});

// --- Rutas para los Webhooks (WebhookController) ---
Route::prefix('webhooks')->group(function () {
    // Webhook para notificaciones de pagos C2P
    Route::post('/bnc/c2p', [WebhookController::class, 'handleC2PWebhook']);

    // Webhook para notificaciones de pagos con tarjeta (VPOS)
    Route::post('/bnc/card', [WebhookController::class, 'handleCardWebhook']);

    // Webhook para notificaciones de pagos P2P
    Route::post('/bnc/p2p', [WebhookController::class, 'handleP2PWebhook']);
});

// --- Ruta para obtener la lista de Bancos (BankController) ---
Route::prefix('banks')->group(function () {
    Route::post('/List', [BankController::class, 'index']);
    Route::get('/Services/BCVRates', [BankController::class, 'getBcvRates']);
});

// Rutas protegidas que requieren autenticación con token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    // Otros endpoints de usuario, ej:
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
});

// Rutas de prueba para la API de BNC

Route::get('/test-bnc-auth', function () {
    try {
        $service = new App\Services\BncApiService();

        // Test 1: Configuración
        $config = [
            'auth_url' => env('BNC_AUTH_API_URL'),
            'client_guid' => env('BNC_CLIENT_GUID'),
            'master_key_length' => env('BNC_MASTER_KEY') ? strlen(env('BNC_MASTER_KEY')) : 0,
            'merchant_id' => env('BNC_MERCHANT_ID'),
            'has_master_key' => !empty(env('BNC_MASTER_KEY'))
        ];

        // Test 2: DataCypher
        $cypher = new App\Services\DataCypher(env('BNC_MASTER_KEY'));
        $encryptionTest = $cypher->testCompatibilityWithLegacy();

        // Test 3: Intento real de token
        $token = $service->getSessionToken();

        // Test 4: Probar bancos (opcional)
        $banks = $service->getBanksFromBnc();

        return response()->json([
            'config' => $config,
            'encryption_test' => $encryptionTest,
            'token' => $token ? substr($token, 0, 50) . '...' : null,
            'token_length' => $token ? strlen($token) : 0,
            'banks_count' => is_array($banks) ? count($banks) : 0,
            'banks_sample' => is_array($banks) ? array_slice($banks, 0, 3) : null,
            'success' => !is_null($token)
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'timestamp' => now()->toISOString()
        ], 500);
    }
});

// Crea una ruta temporal para debug
Route::get('/debug-bnc', function () {
    return [
        'BNC_AUTH_API_URL' => env('BNC_AUTH_API_URL'),
        'BNC_CLIENT_GUID' => env('BNC_CLIENT_GUID'),
        'BNC_MASTER_KEY' => substr(env('BNC_MASTER_KEY'), 0, 10) . '...',
        'master_key_length' => strlen(env('BNC_MASTER_KEY')),
        'app_env' => env('APP_ENV')
    ];
});

Route::get('/test-bnc-connection', function () {
    try {
        $url = 'https://servicios.bncenlinea.com:16500/api/Auth/LogOn';

        // Test de conectividad básica
        $response = Http::timeout(10)->get($url);

        return response()->json([
            'url' => $url,
            'status' => $response->status(),
            'headers' => $response->headers(),
            'body' => $response->body()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'url' => $url
        ], 500);
    }
});


// Ruta de prueba para el cifrado P2P usando DataCypher

Route::get('/test-p2p-encryption', function () {
    $cypher = new App\Services\DataCypher(env('BNC_MASTER_KEY'));

    $testData = [
        "BeneficiaryBankCode" => 191,
        "BeneficiaryCellPhone" => "584242207524",
        "BeneficiaryID" => "V23000760",
        "BeneficiaryName" => "María Rodríguez Solís",
        "Amount" => 15.8,
        "Description" => "Pago de servicios profesionales septiembre 2024",
        "BeneficiaryEmail" => null
    ];

    // ✅ JSON corregido
    $jsonData = json_encode($testData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $result = $cypher->encryptLegacyFormat($testData);

    return response()->json([
        'test_data' => $testData,
        'json_data' => json_encode($testData),
        'encrypted_value' => $result['value'],
        'value_length' => strlen($result['value']),
        'validation_hash' => $result['validation']
    ]);
});

// Ruta de prueba para comparar tokens (autorización vs cliente)

Route::get('/debug-tokens', function () {
    $service = new App\Services\BncApiService();
    
    // Token de autorización BNC
    $authToken = $service->getSessionToken();
    
    // Token del cliente (ejemplo)
    $clientToken = "1655508";
    
    return response()->json([
        'auth_token_length' => $authToken ? strlen($authToken) : 0,
        'auth_token_preview' => $authToken ? substr($authToken, 0, 50) . '...' : null,
        'client_token' => $clientToken,
        'difference' => 'auth_token va en header, client_token va en datos'
    ]);
});
