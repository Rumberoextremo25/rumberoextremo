<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe una ruta a usuarios con rol de administrador.
 *
 * Cambios de seguridad respecto a la versión anterior:
 *  - Dejó de registrar el email y el rol del usuario en cada petición (el log
 *    acumulaba datos personales y crecía sin límite).
 *  - Un usuario autenticado sin permisos recibe un 403 (antes se le
 *    redirigía al dashboard, lo que ocultaba el intento de escalada y
 *    permitía bucles de redirección).
 *  - Se comprueba también el estado de la cuenta.
 *  - Se registra un único evento de seguridad (sin datos personales).
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }

            return redirect()->route('login')->with('error', 'Debes iniciar sesión.');
        }

        // Cuenta desactivada: fuera de la aplicación, aunque el rol sea admin
        if (! $this->isActive($user)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'La cuenta está inactiva.'], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Tu cuenta está inactiva. Contacta al administrador.');
        }

        if (! $this->isAdmin($user)) {
            Log::warning('security.admin_access_denied', [
                'user_id' => $user->getAuthIdentifier(),
                'method' => $request->method(),
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'No tienes permisos de administrador.'], 403);
            }

            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }

    private function isAdmin($user): bool
    {
        return in_array('admin', [
            $user->role ?? null,
            $user->user_type ?? null,
        ], true)
            || (method_exists($user, 'hasRole') && $user->hasRole('admin'));
    }

    private function isActive($user): bool
    {
        // Solo se bloquea con estados explícitos: así un `status` nulo (datos
        // antiguos) no deja fuera a usuarios válidos.
        return ! in_array(strtolower((string) ($user->status ?? 'activo')), [
            'inactivo',
            'inactive',
            'suspendido',
            'suspended',
            'bloqueado',
            'blocked',
            'disabled',
        ], true);
    }
}
