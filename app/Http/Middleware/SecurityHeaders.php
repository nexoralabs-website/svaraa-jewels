<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Standard security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Skip CSP for Filament admin (it loads many dynamic scripts)
        if (str_starts_with($request->path(), 'admin')) {
            return $response;
        }

        $isLocal   = app()->environment('local');
        $viteHost  = $isLocal ? 'localhost:5173' : '';
        $appUrl    = config('app.url', '');

        $csp  = "default-src 'self'; ";

        // Scripts: self + inline (Alpine.js) + Razorpay + local Vite HMR
        $csp .= "script-src 'self' 'unsafe-inline' ";
        if ($viteHost) {
            $csp .= "'unsafe-eval' http://{$viteHost} ws://{$viteHost} ";
        }
        $csp .= "https://checkout.razorpay.com https://api.razorpay.com; ";

        // Styles: self + inline (Tailwind JIT) + fonts
        $csp .= "style-src 'self' 'unsafe-inline' ";
        if ($viteHost) {
            $csp .= "http://{$viteHost} ";
        }
        $csp .= "https://fonts.bunny.net https://fonts.googleapis.com; ";

        // Fonts
        $csp .= "font-src 'self' https://fonts.bunny.net https://fonts.gstatic.com data:; ";

        // Connect: AJAX + search + payment
        $csp .= "connect-src 'self' ";
        if ($viteHost) {
            $csp .= "ws://{$viteHost} http://{$viteHost} ";
        }
        $csp .= "https://api.razorpay.com https://api.meilisearch.com; ";

        // Images: self + storage (same origin) + data URIs + blob
        $csp .= "img-src 'self' data: blob: " . ($appUrl ? $appUrl . " " : "") . "; ";

        // Frames: Razorpay checkout iframe
        $csp .= "frame-src https://api.razorpay.com https://checkout.razorpay.com; ";

        // Object: none
        $csp .= "object-src 'none'; ";

        // Form action: only self
        $csp .= "form-action 'self'; ";

        // Base URI: only self
        $csp .= "base-uri 'self';";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
