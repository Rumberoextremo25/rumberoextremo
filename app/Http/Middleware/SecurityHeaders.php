<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Añade las cabeceras de seguridad que faltaban en la aplicación.
 *
 * - Evita el sniffing de tipos MIME y el clickjacking.
 * - Limita las APIs del navegador que la aplicación no usa.
 * - Publica una Content-Security-Policy: en producción se aplica de forma
 *   estricta; en local se envía en modo "report-only" para no romper el
 *   desarrollo (las vistas usan mucho script y estilo en línea).
 * - Fuerza HSTS cuando la petición es segura.
 * - Prohíbe el cacheo de respuestas autenticadas (datos bancarios, pagos,
 *   comprobantes) y del panel /admin.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $headers = $response->headers;

        // --- Cabeceras base -------------------------------------------------
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $headers->set(
            'Permissions-Policy',
            'accelerometer=(), autoplay=(), camera=(), display-capture=(), encrypted-media=(), '
            . 'fullscreen=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), '
            . 'midi=(), payment=(), picture-in-picture=(), publickey-credentials-get=(), '
            . 'screen-wake-lock=(), sync-xhr=(), usb=(), xr-spatial-tracking=()'
        );

        // No revelar el stack tecnológico
        $headers->remove('X-Powered-By');
        $headers->remove('Server');

        // --- HSTS (solo sobre HTTPS) ---------------------------------------
        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // --- Aislamiento del panel -----------------------------------------
        if ($this->isAdminArea($request)) {
            // Sin caché en el backoffice (datos sensibles / por usuario)
            $headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private, max-age=0');
            $headers->set('Pragma', 'no-cache');
            $headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
            $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        } elseif ($request->user() !== null) {
            // Cualquier respuesta de un usuario autenticado: privada
            $headers->set('Cache-Control', 'no-store, private, max-age=0');
        }

        // --- Content-Security-Policy ---------------------------------------
        if ($this->isHtml($response) && ! $headers->has('Content-Security-Policy')) {
            $policy = $this->policy($request);
            $enforce = (bool) config('security.csp_enforce');

            $headers->set($enforce ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only', $policy);
        }

        return $response;
    }

    private function isAdminArea(Request $request): bool
    {
        return $request->is('admin', 'admin/*', 'transacciones', 'transacciones/*', 'profile*', 'password/*');
    }

    private function isHtml(Response $response): bool
    {
        $type = (string) $response->headers->get('Content-Type');

        return $type === '' || str_contains($type, 'text/html');
    }

    /**
     * Política de seguridad de contenido.
     *
     * Nota: 'unsafe-inline' y 'unsafe-eval' son necesarios porque las vistas
     * usan atributos onclick y bloques <script> en línea (y Firebase/Alpine los
     * requieren). La política sigue bloqueando la carga de scripts desde
     * cualquier origen que no esté en la lista.
     */
    private function policy(Request $request): string
    {
        $extra = trim((string) config('security.csp_extra_script_src', ''));

        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval' "
            . 'https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com '
            . 'https://www.gstatic.com https://apis.google.com https://www.google.com '
            . 'https://js.stripe.com';

        if ($extra !== '') {
            $scriptSrc .= ' ' . $extra;
        }

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "script-src {$scriptSrc}",
            "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://unpkg.com https://fonts.googleapis.com",
            "font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "img-src 'self' data: blob: https:",
            "media-src 'self' https: blob:",
            "connect-src 'self' https: wss:",
            "worker-src 'self' blob:",
            "frame-src 'self' https://www.google.com https://js.stripe.com https://www.youtube.com",
            "manifest-src 'self'",
        ];

        if ($request->isSecure()) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives) . ';';
    }
}
