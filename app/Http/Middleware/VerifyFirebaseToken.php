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
        $this->client = new Google_Client();
        $this->client->setClientId(env('FIREBASE_CLIENT_ID'));
        
        Log::info('✅ VerifyFirebaseToken inicializado', [
            'client_id' => env('FIREBASE_CLIENT_ID'),
            'project_id' => env('FIREBASE_PROJECT_ID')
        ]);
    }

    public function handle(Request $request, Closure $next)
    {
        Log::info('========== VERIFY FIREBASE TOKEN MIDDLEWARE ==========');
        Log::info('URL: ' . $request->fullUrl());
        Log::info('Method: ' . $request->method());
        
        // Ver todos los headers
        Log::info('Headers completos:', $request->headers->all());
        
        $token = $request->bearerToken();
        
        Log::info('Token presente: ' . ($token ? 'SI' : 'NO'));
        
        if ($token) {
            Log::info('Token (primeros 50 chars): ' . substr($token, 0, 50));
            Log::info('Token longitud: ' . strlen($token));
            
            // 🔍 Decodificar el token para ver su contenido (sin verificar)
            try {
                $tokenParts = explode('.', $token);
                if (count($tokenParts) === 3) {
                    $payload = json_decode(base64_decode($tokenParts[1]), true);
                    Log::info('📦 Contenido del token (sin verificar):', [
                        'iss' => $payload['iss'] ?? 'N/A',
                        'aud' => $payload['aud'] ?? 'N/A',
                        'sub' => $payload['sub'] ?? 'N/A',
                        'email' => $payload['email'] ?? 'N/A',
                        'exp' => $payload['exp'] ?? 'N/A',
                        'iat' => $payload['iat'] ?? 'N/A'
                    ]);
                    
                    // Verificar si el audience coincide con nuestro client_id
                    if (isset($payload['aud'])) {
                        Log::info('🔍 Comparación aud vs client_id:', [
                            'token_aud' => $payload['aud'],
                            'client_id' => env('FIREBASE_CLIENT_ID'),
                            'coinciden' => ($payload['aud'] === env('FIREBASE_CLIENT_ID')) ? 'SI' : 'NO'
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error decodificando token: ' . $e->getMessage());
            }
        }
        
        if (!$token) {
            Log::error('❌ Token no proporcionado');
            return response()->json([
                'success' => false,
                'message' => 'Token no proporcionado'
            ], 401);
        }
        
        try {
            Log::info('🔍 Intentando verificar token con Google Client...');
            
            // Intentar verificar el token
            $payload = $this->client->verifyIdToken($token);
            
            if ($payload) {
                Log::info('✅ Token VÁLIDO - Payload completo:', $payload);
                
                $request->merge([
                    'firebase_user' => [
                        'uid' => $payload['sub'],
                        'email' => $payload['email'] ?? null,
                        'name' => $payload['name'] ?? null,
                    ]
                ]);
                
                return $next($request);
            } else {
                Log::error('❌ Token INVÁLIDO - verifyIdToken devolvió null');
                
                // Intentar verificar si el problema es el algoritmo
                Log::info('🔧 Intentando con algoritmo alternativo...');
                
                // Probar con diferentes opciones
                $this->client->setVerifyConfig([
                    'audience' => env('FIREBASE_CLIENT_ID'),
                    'allowed_algorithms' => ['RS256', 'ES256']
                ]);
                
                $payload2 = $this->client->verifyIdToken($token);
                if ($payload2) {
                    Log::info('✅ Token válido con configuración alternativa');
                } else {
                    Log::error('❌ Token sigue siendo inválido');
                }
            }
            
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
            Log::error('Tipo de excepción: ' . get_class($e));
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error de autenticación: ' . $e->getMessage()
            ], 401);
        }
    }
}