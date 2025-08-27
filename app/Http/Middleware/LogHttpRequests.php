<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogHttpRequests
{
    public function handle($request, Closure $next)
    {
        // Log de la petición
        Log::info('Petición HTTP:', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        $response = $next($request);

        // Log de la respuesta
        Log::info('Respuesta HTTP:', [
            'status' => $response->status(),
            'content' => $response->getContent(),
        ]);

        return $response;
    }
}