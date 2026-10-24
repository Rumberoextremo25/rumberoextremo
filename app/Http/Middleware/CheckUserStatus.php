<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierra la sesión de los usuarios con la cuenta desactivada.
 *
 * Este middleware existía pero nunca se ejecutaba: estaba declarado en
 * app/Http/Kernel.php, un archivo que Laravel 11/12 ya no utiliza (el stack se
 * configura en bootstrap/app.php). Ahora sí está registrado en el grupo web.
 */
class CheckUserStatus
{
    /**
     * Estados que impiden el acceso. Un `status` nulo o desconocido NO bloquea:
     * así los registros antiguos sin estado siguen funcionando.
     */
    private const BLOCKED = [
        'inactivo',
        'inactive',
        'suspendido',
        'suspended',
        'bloqueado',
        'blocked',
        'disabled',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $status = strtolower((string) ($user->status ?? 'activo'));

            if (in_array($status, self::BLOCKED, true)) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'La cuenta está inactiva.'], 403);
                }

                return redirect()->route('login')
                    ->with('error', 'Tu cuenta está inactiva. Por favor contacta al administrador.');
            }
        }

        return $next($request);
    }
}
