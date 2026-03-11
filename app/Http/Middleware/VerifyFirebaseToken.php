<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Google_Client;
use Illuminate\Support\Facades\Log;

error_log("🚀 ARCHIVO VerifyFirebaseToken.php CARGADO");

class VerifyFirebaseToken
{
    public function handle(Request $request, Closure $next)
    {
        // ========== LOGS DE DEPURACIÓN ==========
        Log::info('========== VERIFY FIREBASE TOKEN MIDDLEWARE ==========');
        Log::info('URL: ' . $request->fullUrl());
        Log::info('Method: ' . $request->method());
        Log::info('Headers: ', $request->headers->all());
        
        $token = $request->bearerToken();
        
        Log::info('Token presente: ' . ($token ? 'SI' : 'NO'));
        
        if ($token) {
            Log::info('Token (primeros 50): ' . substr($token, 0, 50));
            Log::info('Token longitud: ' . strlen($token));
        }
        
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
            Log::info('Creando Google_Client...');
            $client = new Google_Client(['client_id' => env('FIREBASE_CLIENT_ID')]);
            
            Log::info('Configurando Auth...');
            $client->setAuthConfig([
                'project_id' => env('FIREBASE_PROJECT_ID'),
            ]);
            
            Log::info('Verificando token...');
            $payload = $client->verifyIdToken($token);
            
            if ($payload) {
                Log::info('✅ Token VÁLIDO');
                Log::info('Payload recibido:', $payload);
                
                $request->merge([
                    'firebase_user' => [
                        'uid' => $payload['sub'],
                        'email' => $payload['email'] ?? null,
                        'name' => $payload['name'] ?? null,
                    ]
                ]);
                
                return $next($request);
            }
            
            Log::error('❌ Token INVÁLIDO - verifyIdToken devolvió null');
            return response()->json([
                'success' => false,
                'message' => 'Token inválido'
            ], 401);
            
        } catch (\Exception $e) {
            Log::error('❌ EXCEPCIÓN: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error de autenticación: ' . $e->getMessage()
            ], 401);
        }
    }
}