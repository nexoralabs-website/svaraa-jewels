<?php

use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrustProxies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/razorpay',
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
            TrustProxies::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Log all unhandled exceptions with context
        $exceptions->report(function (\Throwable $e) {
            Log::error('unhandled_exception', [
                'class'   => get_class($e),
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'url'     => request()->fullUrl(),
                'user'    => auth()->id(),
            ]);
        });

        // Return JSON for AJAX/API requests on HTTP errors
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'HTTP ' . $e->getStatusCode(),
                ], $e->getStatusCode());
            }
        });

        // Payment-related 422/500 errors — return JSON for Razorpay JS
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

    })->create();
