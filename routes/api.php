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
    // Nueva ruta agregada para el método processP2PPayment
    Route::post('/p2p', [PaymentController::class, 'processP2PPayment']);
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
            'master_key_length' => strlen(env('BNC_MASTER_KEY'))
        ];
        
        // Test 2: DataCypher
        $cypher = new App\Services\DataCypher(env('BNC_MASTER_KEY'));
        $encryptionTest = $cypher->testEncryption();
        
        // Test 3: Intento real
        $token = $service->getSessionToken();
        
        return response()->json([
            'config' => $config,
            'encryption_test' => $encryptionTest,
            'token' => $token ? substr($token, 0, 50) . '...' : null,
            'token_length' => $token ? strlen($token) : 0,
            'success' => !is_null($token)
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
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
