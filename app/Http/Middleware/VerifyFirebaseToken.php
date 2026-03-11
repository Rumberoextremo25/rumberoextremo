<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Google_Client;
use Illuminate\Support\Facades\Log;

class VerifyFirebaseToken
{
    private $client;

    public function __construct()
    {
        $this->client = new Google_Client(['client_id' => env('FIREBASE_CLIENT_ID')]);
        
        // Configuración correcta para Firebase
        $this->client->setAuthConfig([
            'type' => 'service_account',
            'project_id' => env('FIREBASE_PROJECT_ID'),
            'client_id' => env('FIREBASE_CLIENT_ID'),
            // Si tienes un archivo de credenciales, puedes usar:
            // $this->client->setAuthConfig(storage_path('app/firebase-credentials.json'));
        ]);
        
        // Configurar el cliente para aceptar tokens de Firebase
        $this->client->setApplicationName('RumberoExtremo');
    }

    public function handle(Request $request, Closure $next)
    {
        // ========== LOGS DE DEPURACIÓN ==========
        Log::info('========== VERIFY FIREBASE TOKEN MIDDLEWARE ==========');
        Log::info('URL: ' . $request->fullUrl());
        Log::info('Method: ' . $request->method());
        
        $token = $request->bearerToken();
        
        Log::info('Token presente: ' . ($token ? 'SI' : 'NO'));
        Log::info('FIREBASE_PROJECT_ID: ' . env('FIREBASE_PROJECT_ID', 'NO DEFINIDO'));
        Log::info('FIREBASE_CLIENT_ID: ' . env('FIREBASE_CLIENT_ID', 'NO DEFINIDO'));
        
        if (!$token) {
            Log::error('❌ Token no proporcionado');
            return response()->json([
                'success' => false,
                'message' => 'Token no proporcionado'
            ], 401);
        }
        
        try {
            Log::info('Intentando verificar token...');
            
            // 🔴 CORRECCIÓN IMPORTANTE: Verificar el token correctamente
            $payload = $this->client->verifyIdToken($token);
            
            if ($payload) {
                Log::info('✅ Token VÁLIDO');
                Log::info('Usuario autenticado:', [
                    'uid' => $payload['sub'] ?? 'N/A',
                    'email' => $payload['email'] ?? 'N/A',
                    'name' => $payload['name'] ?? 'N/A'
                ]);
                
                // Agregar el usuario autenticado al request
                $request->merge([
                    'firebase_user' => [
                        'uid' => $payload['sub'],
                        'email' => $payload['email'] ?? null,
                        'name' => $payload['name'] ?? null,
                        'picture' => $payload['picture'] ?? null,
                    ]
                ]);
                
                // También puedes autenticar al usuario en Laravel si tienes un modelo User
                $user = User::updateOrCreate(
                       ['firebase_uid' => $payload['sub']],
                      ['email' => $payload['email'], 'name' => $payload['name'] ?? '']
                   );
                   Auth::login($user);
                
                return $next($request);
            }
            
            Log::error('❌ Token INVÁLIDO - Payload vacío');
            return response()->json([
                'success' => false,
                'message' => 'Token inválido'
            ], 401);
            
        } catch (\InvalidArgumentException $e) {
            Log::error('❌ Token mal formado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Token mal formado'
            ], 401);
            
        } catch (\Exception $e) {
            Log::error('❌ EXCEPCIÓN GENERAL: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error de autenticación: ' . $e->getMessage()
            ], 401);
        }
    }
}