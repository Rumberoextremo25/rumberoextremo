<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AllyController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\RumberoAIController;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ===========================================
// RUTAS PÚBLICAS (NO REQUIEREN AUTENTICACIÓN)
// ===========================================

// Autenticación
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// Home / Datos públicos
Route::get('/home-data', [HomeController::class, 'index'])->name('api.home-data');

// Bancos
Route::prefix('banks')->name('api.banks.')->group(function () {
    Route::post('/list', [BankController::class, 'index'])->name('list');
    Route::get('/daily-dollar-rate', [BankController::class, 'getDailyDollarRate'])->name('daily-dollar-rate');
});

// Aliados públicos (sin autenticación)
Route::get('/aliados', [AllyController::class, 'index'])->name('api.aliados.index');
Route::get('/aliados/{user_id}', [AllyController::class, 'show'])->name('api.aliados.show');

// RumberoAI - Rutas públicas
Route::get('/categorias', [RumberoAIController::class, 'getCategorias'])->name('api.categorias');
Route::post('/ia/chat', [RumberoAIController::class, 'chat'])->name('api.ia.chat');

// ===========================================
// RUTAS PROTEGIDAS CON SANCTUM
// ===========================================
Route::middleware('auth:sanctum')->group(function () {

    // ===========================================
    // USUARIO Y AUTENTICACIÓN
    // ===========================================
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('api.user');

    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::put('/user/profile', [AuthController::class, 'updateProfile'])->name('api.user.profile');

    // ===========================================
    // PRODUCTOS (CRUD)
    // ===========================================
    Route::apiResource('products', ProductController::class)->names([
        'index' => 'api.products.index',
        'store' => 'api.products.store',
        'show' => 'api.products.show',
        'update' => 'api.products.update',
        'destroy' => 'api.products.destroy',
    ]);

    // ===========================================
    // RUMBERO AI - RUTAS PROTEGIDAS
    // ===========================================
    Route::prefix('ia')->name('api.ia.')->group(function () {
        Route::post('/activar-descuento', [RumberoAIController::class, 'activarDescuento'])->name('activar-descuento');
        Route::get('/promociones', [RumberoAIController::class, 'promocionesActivas'])->name('promociones');
        Route::get('/historial', [RumberoAIController::class, 'historial'])->name('historial');
        Route::get('/mis-descuentos', [RumberoAIController::class, 'misDescuentos'])->name('mis-descuentos');
        Route::post('/usar-descuento/{codigo}', [RumberoAIController::class, 'usarDescuento'])->name('usar-descuento');
    });

    // ===========================================
    // PAGOS
    // ===========================================

    Route::prefix('pagos')->middleware('firebase.auth')->group(function () {
        
    });

    Route::prefix('pagos')->name('api.pagos.')->group(function () {
        Route::post('/c2p', [PaymentController::class, 'initiateC2PPayment']);
        Route::post('/tarjeta', [PaymentController::class, 'processCardPayment']);
        Route::post('/p2p', [PaymentController::class, 'validateP2PPayment']);
        // Payouts (requieren ser admin)
        Route::prefix('payouts')->middleware(['admin'])->name('payouts.')->group(function () {
            Route::get('/pendientes', [PaymentController::class, 'obtenerPagosPendientes'])->name('pendientes');
            Route::get('/filtro', [PaymentController::class, 'obtenerPagosPorFiltro'])->name('filtro');
            Route::get('/estadisticas', [PaymentController::class, 'obtenerEstadisticasPayouts'])->name('estadisticas');
            Route::post('/generar-archivo-bnc', [PaymentController::class, 'generarArchivoPagosBNC'])->name('generar-archivo-bnc');
            Route::post('/confirmar', [PaymentController::class, 'confirmarPagosProcesados'])->name('confirmar');
            Route::get('/descargar-archivo-bnc/{archivo}', [PaymentController::class, 'descargarArchivoBNC'])->name('descargar-archivo-bnc');
            Route::post('/revertir/{payoutId}', [PaymentController::class, 'revertirPago'])->name('revertir');
        });
    });
});


/***********
// ===========================================
// RUTAS DE DESARROLLO / DEBUG
// ===========================================

// Ruta para poblar base de datos (solo desarrollo)
Route::post('/populate-db', function () {
    if (!app()->environment('local', 'development')) {
        return response()->json(['error' => 'Ruta solo disponible en desarrollo'], 403);
    }
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh --seed');
    return response()->json(['success' => true, 'message' => 'Base de datos refrescada y sembrada.']);
})->name('api.populate-db');

// Ruta de prueba simple
Route::get('/test-route', function () {
    return response()->json(['message' => 'API funcionando correctamente', 'timestamp' => now()]);
})->name('api.test-route');

// ===========================================
// RUTAS DE DEBUG PARA BNC (solo desarrollo)
// ===========================================
if (app()->environment('local', 'development')) {

    // Debug de configuración BNC
    Route::get('/debug-bnc', function () {
        return response()->json([
            'BNC_AUTH_API_URL' => env('BNC_AUTH_API_URL'),
            'BNC_CLIENT_GUID' => env('BNC_CLIENT_GUID'),
            'BNC_MASTER_KEY' => substr(env('BNC_MASTER_KEY'), 0, 10) . '...',
            'master_key_length' => strlen(env('BNC_MASTER_KEY')),
            'app_env' => env('APP_ENV')
        ]);
    })->name('api.debug-bnc');

    // Test de conexión BNC
    Route::get('/test-bnc-connection', function () {
        try {
            $url = 'https://servicios.bncenlinea.com:16000/api/Auth/LogOn';
            $response = Http::timeout(10)->get($url);

            return response()->json([
                'url' => $url,
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'url' => $url
            ], 500);
        }
    })->name('api.test-bnc-connection');

    // Debug de tokens
    Route::get('/debug-tokens', function () {
        $service = app(\App\Services\BncApiService::class);
        $authToken = $service->getSessionToken();
        $clientToken = "1655508";

        return response()->json([
            'auth_token_length' => $authToken ? strlen($authToken) : 0,
            'auth_token_preview' => $authToken ? substr($authToken, 0, 50) . '...' : null,
            'client_token' => $clientToken,
            'difference' => 'auth_token va en header, client_token va en datos'
        ]);
    })->name('api.debug-tokens');

    // Test de encriptación AES
    Route::get('/test-aes-encryption', function () {
        $encryptionKey = env('BNC_MASTER_KEY');
        $dataToEncrypt = "TestString123";

        $cypher = new \App\Services\DataCypher($encryptionKey);

        $encryptedData = $cypher->encryptAES($dataToEncrypt);
        $decryptedData = $cypher->decryptAES($encryptedData);

        return response()->json([
            'original_data' => $dataToEncrypt,
            'encrypted_data' => $encryptedData,
            'decryption_success' => $decryptedData === $dataToEncrypt
        ]);
    })->name('api.test-aes-encryption');

    // Test de compatibilidad legacy
    Route::get('/test/legacy-compatibility', function () {
        try {
            $bncService = app(\App\Services\BncApiService::class);
            $result = $bncService->verifyLegacyCompatibility();

            return response()->json([
                'success' => $result,
                'message' => $result ? '✅ Compatibilidad legacy verificada' : '❌ Error en compatibilidad legacy',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    })->name('api.legacy-compatibility');

    // API de debug 2FA - SIN AUTENTICACIÓN (SOLO LOCAL)
    /*****  Route::prefix('debug-2fa')->group(function () {

        // Listar usuarios
        Route::get('users', function () {
            try {
                $users = User::select('id', 'name', 'email', 'two_factor_enabled')->get();
                return response()->json([
                    'success' => true,
                    'users' => $users
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });

        // Ver códigos de un usuario
        Route::get('check/{userId?}', function ($userId = null) {
            try {
                if (!$userId) {
                    $user = User::first();
                } else {
                    $user = User::find($userId);
                }

                if (!$user) {
                    return response()->json(['error' => 'Usuario no encontrado'], 404);
                }

                $google2fa = new PragmaRX\Google2FA\Google2FA();

                $window = floor(time() / 30);
                $codes = [];

                for ($i = -3; $i <= 3; $i++) {
                    try {
                        $code = $google2fa->getCurrentOtp($user->two_factor_secret, $window + $i);
                        $codes[] = [
                            'period' => $i,
                            'time' => date('Y-m-d H:i:s', ($window + $i) * 30),
                            'code' => $code
                        ];
                    } catch (\Exception $e) {
                        $codes[] = [
                            'period' => $i,
                            'error' => $e->getMessage()
                        ];
                    }
                }

                return response()->json([
                    'success' => true,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'two_factor_enabled' => $user->two_factor_enabled,
                        'secret_preview' => substr($user->two_factor_secret ?? '', 0, 10) . '...',
                        'secret_length' => strlen($user->two_factor_secret ?? ''),
                    ],
                    'server_time' => date('Y-m-d H:i:s'),
                    'timestamp' => time(),
                    'timezone' => date_default_timezone_get(),
                    'expected_codes' => $codes
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });

        // Regenerar secreto
        Route::post('regenerate/{userId?}', function ($userId = null) {
            try {
                if (!$userId) {
                    $user = User::first();
                } else {
                    $user = User::find($userId);
                }

                if (!$user) {
                    return response()->json(['error' => 'Usuario no encontrado'], 404);
                }

                $google2fa = new PragmaRX\Google2FA\Google2FA();

                // Generar nuevo secreto de 32 caracteres
                $newSecret = $google2fa->generateSecretKey(32);
                $user->two_factor_secret = $newSecret;
                $user->two_factor_enabled = false;
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Secreto regenerado exitosamente',
                    'user_id' => $user->id,
                    'new_secret' => $newSecret,
                    'qr_url' => $google2fa->getQRCodeUrl(
                        config('app.name'),
                        $user->email,
                        $newSecret
                    )
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });

        // Desactivar 2FA
        Route::post('disable/{userId?}', function ($userId = null) {
            try {
                if (!$userId) {
                    $user = User::first();
                } else {
                    $user = User::find($userId);
                }

                if (!$user) {
                    return response()->json(['error' => 'Usuario no encontrado'], 404);
                }

                $user->two_factor_enabled = false;
                $user->two_factor_secret = null;
                $user->two_factor_recovery_codes = null;
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => '2FA desactivado para usuario: ' . $user->email
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });
    });
}
 ***************/
