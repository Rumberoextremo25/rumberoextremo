<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // LOG 1: Verificar si llega al middleware
        Log::info('AdminMiddleware: Iniciando verificación', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'authenticated' => Auth::check()
        ]);

        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            Log::warning('AdminMiddleware: Usuario no autenticado, redirigiendo a login');
            return redirect()->route('login')->with('error', 'Debes iniciar sesión');
        }

        $user = Auth::user();
        
        // LOG 2: Ver datos del usuario
        Log::info('AdminMiddleware: Usuario autenticado', [
            'user_id' => $user->id,
            'user_type' => $user->user_type,
            'user_role' => $user->role ?? 'no definido',
            'email' => $user->email
        ]);

        // Verificar si el usuario es admin
        // PRUEBA: Cambia temporalmente a true para ver si el problema es la condición
        $isAdmin = ($user->user_type === 'admin' || $user->role === 'admin');
        
        Log::info('AdminMiddleware: Verificación de admin', [
            'user_type' => $user->user_type,
            'user_role' => $user->role ?? 'no definido',
            'is_admin' => $isAdmin ? 'SI' : 'NO'
        ]);

        if (!$isAdmin) {
            Log::warning('AdminMiddleware: Usuario no es admin, redirigiendo a dashboard');
            return redirect()->route('dashboard')->with('error', 'No tienes permisos de administrador');
        }

        Log::info('AdminMiddleware: Acceso permitido');
        return $next($request);
    }
}
