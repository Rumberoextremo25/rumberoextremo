<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Si la respuesta es JSON y es una petición web normal
        if ($response->headers->get('content-type') === 'application/json' && 
            !$request->expectsJson() &&
            !$request->is('api/*')) {
            
            // Redirigir a la página que debería ir
            if ($response->getData()->redirect ?? false) {
                return redirect($response->getData()->redirect);
            }
        }
        
        return $response;
    }
}
