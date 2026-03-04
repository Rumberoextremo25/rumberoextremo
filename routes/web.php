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
/********************
Route::get('/admin/settings/test-2fa', [SettingsController::class, 'testCurrentCode'])->name('settings.test-2fa');

Route::get('/test-success', function() {
    return '✅ Login exitoso! Usuario: ' . Auth::user()->email . ' ID: ' . Auth::id();
})->middleware('auth');


// En routes/web.php o routes/api.php (temporal)
// RUTAS DE DEBUG TEMPORALES - QUITAR EN PRODUCCIÓN
Route::get('/debug-2fa-public/{userId?}', function($userId = null) {
    try {
        // Si no se especifica usuario, tomar el primer usuario
        if (!$userId) {
            $user = App\Models\User::first();
        } else {
            $user = App\Models\User::find($userId);
        }
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado']);
        }
        
        $google2fa = new PragmaRX\Google2FA\Google2FA();
        
        // Mostrar información del usuario
        $data = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'two_factor_enabled' => $user->two_factor_enabled,
                'two_factor_secret' => substr($user->two_factor_secret, 0, 10) . '...',
                'secret_length' => strlen($user->two_factor_secret ?? ''),
            ],
            'server' => [
                'time' => date('Y-m-d H:i:s'),
                'timestamp' => time(),
                'timezone' => date_default_timezone_get(),
            ]
        ];
        
        // Generar códigos esperados
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
        
        $data['expected_codes'] = $codes;
        
        return response()->json($data);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('debug.2fa.public');

// Ruta para regenerar secreto SIN autenticación
Route::post('/regenerate-2fa-secret-public/{userId?}', function($userId = null) {
    try {
        if (!$userId) {
            $user = App\Models\User::first();
        } else {
            $user = App\Models\User::find($userId);
        }
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado']);
        }
        
        $google2fa = new PragmaRX\Google2FA\Google2FA();
        
        // Generar nuevo secreto
        $newSecret = $google2fa->generateSecretKey(32);
        $user->two_factor_secret = $newSecret;
        $user->two_factor_enabled = false; // Desactivar hasta verificar
        $user->save();
        
        // Generar QR URL
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $newSecret
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Secreto regenerado',
            'user_id' => $user->id,
            'new_secret' => $newSecret,
            'qr_code_url' => $qrCodeUrl,
            'qr_manual' => "Otpauth://totp/" . urlencode(config('app.name')) . ":" . $user->email . "?secret=" . $newSecret . "&issuer=" . urlencode(config('app.name'))
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
})->name('regenerate.2fa.public');

// Ruta para desactivar 2FA completamente (último recurso)
Route::post('/disable-2fa-public/{userId?}', function($userId = null) {
    try {
        if (!$userId) {
            $user = App\Models\User::first();
        } else {
            $user = App\Models\User::find($userId);
        }
        
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado']);
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
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
})->name('disable.2fa.public');





Route::get('/debug-2fa-panel', function() {
    $users = App\Models\User::all(['id', 'name', 'email', 'two_factor_enabled']);
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>2FA Debug Panel</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body { font-family: Arial; padding: 20px; background: #f0f0f0; }
            .container { max-width: 1200px; margin: 0 auto; }
            .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #f5f5f5; }
            button { padding: 10px 20px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
            button.danger { background: #f44336; }
            .code { font-family: monospace; font-size: 18px; background: #f5f5f5; padding: 10px; border-radius: 4px; }
            .info { background: #e3f2fd; padding: 10px; border-radius: 4px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔧 Panel de Debug 2FA</h1>
            
            <div class="card">
                <h2>Usuarios</h2>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>2FA Activado</th>
                        <th>Acciones</th>
                    </tr>';
    
    foreach ($users as $user) {
        $html .= '
                    <tr>
                        <td>' . $user->id . '</td>
                        <td>' . htmlspecialchars($user->name) . '</td>
                        <td>' . htmlspecialchars($user->email) . '</td>
                        <td>' . ($user->two_factor_enabled ? '✅ Sí' : '❌ No') . '</td>
                        <td>
                            <button onclick="check2FA(' . $user->id . ')">Ver códigos</button>
                            <button onclick="regenerate2FA(' . $user->id . ')">Regenerar</button>
                            <button class="danger" onclick="disable2FA(' . $user->id . ')">Desactivar</button>
                        </td>
                    </tr>';
    }
    
    $html .= '
                </table>
            </div>
            
            <div class="card">
                <h2>Resultados</h2>
                <pre id="result" style="background: #333; color: #fff; padding: 10px; border-radius: 4px; overflow: auto;">Haz clic en una acción para ver resultados...</pre>
            </div>
        </div>
        
        <script>
        async function check2FA(userId) {
            document.getElementById("result").textContent = "Cargando...";
            try {
                const response = await fetch("/debug-2fa-public/" + userId);
                const data = await response.json();
                document.getElementById("result").textContent = JSON.stringify(data, null, 2);
            } catch(e) {
                document.getElementById("result").textContent = "Error: " + e.message;
            }
        }
        
        async function regenerate2FA(userId) {
            if(!confirm("¿Regenerar secreto 2FA? El usuario deberá escanear el nuevo QR.")) return;
            
            document.getElementById("result").textContent = "Cargando...";
            try {
                const response = await fetch("/regenerate-2fa-secret-public/" + userId, {
                    method: "POST"
                });
                const data = await response.json();
                document.getElementById("result").textContent = JSON.stringify(data, null, 2);
                
                if(data.qr_manual) {
                    alert("Nuevo secreto: " + data.new_secret + "\n\nEscanea este QR en tu app:\n" + data.qr_manual);
                }
            } catch(e) {
                document.getElementById("result").textContent = "Error: " + e.message;
            }
        }
        
        async function disable2FA(userId) {
            if(!confirm("¿DESACTIVAR 2FA? Esto permitirá el acceso sin código de verificación.")) return;
            
            document.getElementById("result").textContent = "Cargando...";
            try {
                const response = await fetch("/disable-2fa-public/" + userId, {
                    method: "POST"
                });
                const data = await response.json();
                document.getElementById("result").textContent = JSON.stringify(data, null, 2);
                setTimeout(() => location.reload(), 1000);
            } catch(e) {
                document.getElementById("result").textContent = "Error: " + e.message;
            }
        }
        </script>
    </body>
    </html>
    ';
    
    return response($html);
});

**********************/
/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACIÓN (Breeze/Jetstream)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
