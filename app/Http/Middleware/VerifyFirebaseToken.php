<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Google_Client;
use Illuminate\Support\Facades\Log;

class VerifyFirebaseToken
{
    private $client;

    public function __construct()
    {
        // ✅ Inicializar Google Client solo con el client_id
        $this->client = new Google_Client();
        $this->client->setClientId(env('FIREBASE_CLIENT_ID'));
        
        // Log para verificar la configuración
        Log::info('✅ VerifyFirebaseToken inicializado', [
            'client_id' => env('FIREBASE_CLIENT_ID'),
            'project_id' => env('FIREBASE_PROJECT_ID')
        ]);
    }

    public function handle(Request $request, Closure $next)
    {
        // ========== LOGS DE DEPURACIÓN ==========
        Log::info('========== VERIFY FIREBASE TOKEN MIDDLEWARE ==========');
        Log::info('URL: ' . $request->fullUrl());
        Log::info('Method: ' . $request->method());
        
        // Obtener el token del header Authorization
        $token = $request->bearerToken();
        
        Log::info('Token presente: ' . ($token ? 'SI' : 'NO'));
        
        if (!$token) {
            Log::error('❌ Token no proporcionado');
            return response()->json([
                'success' => false,
                'message' => 'Token no proporcionado'
            ], 401);
        }
        
        try {
            // ✅ Verificar el token con Google Client
            Log::info('Verificando token con Google Client...');
            $payload = $this->client->verifyIdToken($token);
            
            if ($payload) {
                Log::info('✅ Token VÁLIDO', [
                    'uid' => $payload['sub'] ?? 'N/A',
                    'email' => $payload['email'] ?? 'N/A',
                    'name' => $payload['name'] ?? 'N/A'
                ]);
                
                // Agregar datos del usuario al request
                $request->merge([
                    'firebase_user' => [
                        'uid' => $payload['sub'],
                        'email' => $payload['email'] ?? null,
                        'name' => $payload['name'] ?? null,
                        'picture' => $payload['picture'] ?? null,
                    ]
                ]);
                
                // Continuar con la petición
                return $next($request);
            }
            
            // Si el payload es null, el token es inválido
            Log::error('❌ Token INVÁLIDO - verifyIdToken devolvió null');
            return response()->json([
                'success' => false,
                'message' => 'Token inválido'
            ], 401);
            
        } catch (\InvalidArgumentException $e) {
            // Error cuando el token está mal formado
            Log::error('❌ Token mal formado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Token mal formado'
            ], 401);
            
        } catch (\Exception $e) {
            // Cualquier otro error
            Log::error('❌ Error verificando token: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error de autenticación: ' . $e->getMessage()
            ], 401);
        }
    }
}