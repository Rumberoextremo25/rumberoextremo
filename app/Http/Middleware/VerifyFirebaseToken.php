<?php
// app/Http/Middleware/VerifyFirebaseToken.php

namespace App\Http\Middleware;

use Closure;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyFirebaseToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token no proporcionado'
            ], 401);
        }
        
        try {
            $client = new Google_Client(['client_id' => env('FIREBASE_CLIENT_ID')]);
            $client->setAuthConfig([
                'project_id' => env('FIREBASE_PROJECT_ID'),
            ]);
            
            $payload = $client->verifyIdToken($token);
            
            if ($payload) {
                // Token válido
                $request->merge([
                    'firebase_user' => [
                        'uid' => $payload['sub'],
                        'email' => $payload['email'] ?? null,
                    ]
                ]);
                
                return $next($request);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Token inválido'
            ], 401);
            
        } catch (\Exception $e) {
            Log::error('Error verificando token Firebase: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error de autenticación'
            ], 401);
        }
    }
}