<?php

namespace App\Providers;

use App\Services\CartService;
use App\Models\Wishlist;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(CartService $cartService): void
    {
        if (app()->environment('production')) {
            DB::disableQueryLog();

            $appUrl = config('app.url');
            $scheme = strtolower((string) parse_url($appUrl, PHP_URL_SCHEME));

            if ($scheme === 'https') {
                URL::forceScheme('https');
            }
        }

        // TEMP DEBUG: Log database queries
        \Illuminate\Support\Facades\DB::listen(function ($query) {
            \Illuminate\Support\Facades\Log::info('[DB DEBUG] Query executed:', [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ]);
        });

        // Temporary diagnostics: log Filament / auth failures
        Event::listen(Failed::class, function (Failed $event) {
            Log::channel('daily')->warning('Auth failed', [
                'email' => $event->credentials['email'] ?? null,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'guard' => $event->guard,
            ]);
        });

        // Ensure the forms CSS (file-upload, etc.) is loaded even without ->viteTheme()
        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): string => '<link href="' . asset('css/filament/forms/index.css') . '" rel="stylesheet" data-navigate-track />',
        );

        // Debug: log Alpine/chunk state to browser console (temporary)
        FilamentView::registerRenderHook(
            PanelsRenderHook::SCRIPTS_AFTER,
            fn (): string => '<script data-navigate-once>
                console.group(\'[Filament Debug] file-upload\');
                console.log(\'Alpine:\', typeof window.Alpine);
                console.log(\'Livewire:\', typeof window.Livewire);
                let el = document.querySelector(\'[x-data*="fileUploadFormComponent"]\');
                console.log(\'FileUpload DOM node found:\', !!el);
                if (el) console.log(\'Has _x_dataStack:\', !!el._x_dataStack);
                let chunkUrl = el?.getAttribute(\'x-load-src\');
                console.log(\'Chunk URL:\', chunkUrl);
                if (chunkUrl) fetch(chunkUrl, {method: \'HEAD\'}).then(r =>
                    console.log(\'Chunk HTTP\', r.status)
                ).catch(e => console.log(\'Chunk fetch error\', e));
                console.groupEnd();
            </script>',
        );

        // Share cart + wishlist data with all views.
        // Cart uses session/DB via CartService (already cached internally).
        // Wishlist query is only fired when the user is authenticated.
        View::composer('*', function ($view) use ($cartService) {
            $view->with('cartCount', $cartService->getCartCount());
            $view->with('alpineCartItems', $cartService->getCartItemsForAlpine());

            if (auth()->check()) {
                // Eager-load product + first image only — avoids N+1 in sidebar
                $wishlistItems = Wishlist::where('user_id', auth()->id())
                    ->with(['product' => fn ($q) => $q->select('id', 'name', 'slug', 'price', 'thumbnail')
                        ->with(['images' => fn ($qi) => $qi->where('is_primary', true)->select('product_id', 'image')])])
                    ->get();
                $view->with('wishlistCount', $wishlistItems->count());
                $view->with('wishlistItems', $wishlistItems);
            } else {
                $view->with('wishlistCount', 0);
                $view->with('wishlistItems', collect());
            }
        });

        // Rate limiters
        RateLimiter::for('payment_webhook', fn (Request $request) =>
            Limit::perMinute(100)->by($request->ip()));

        RateLimiter::for('payment_create', fn (Request $request) =>
            Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('payment_verify', fn (Request $request) =>
            Limit::perMinute(10)->by($request->ip()));

        // Coupon apply/remove: prevent brute-force
        RateLimiter::for('coupon', fn (Request $request) =>
            Limit::perMinute(20)->by(optional(auth()->user())->id ?: $request->ip()));
    }
}
