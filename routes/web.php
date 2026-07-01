<?php

use App\Http\Controllers\Admin\FailedJobRetryController;
use App\Http\Controllers\Admin\OperationsExportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
})->name('home');
// SEO
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt',  [SeoController::class, 'robots'])->name('seo.robots');

// Search
Route::get('/search',         [SearchController::class, 'index'])->name('search.index');
Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');

// Product and Category routes
Route::get('/shop', [ProductController::class, 'index'])->name('products.index');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/about', function () {
    return view('pages.static.about');
})->name('about');

// Debug route - remove after debugging
Route::get('/debug/storage', function () {
    return [
        'public_exists'=>Storage::disk('public')->exists('products'),
        'files'=>Storage::disk('public')->allFiles('products'),
        'url'=>Storage::url('products'),
    ];
})->middleware('auth');

Route::get('/contact', function () {
    return view('pages.static.contact');
})->name('contact');

Route::get('/terms-and-conditions', function () {
    return view('pages.static.terms');
})->name('terms');

Route::get('/privacy-policy', function () {
    return view('pages.static.privacy');
})->name('privacy-policy');

Route::get('/shipping-policy', function () {
    return view('pages.static.shipping');
})->name('shipping-policy');

Route::get('/refund-policy', function () {
    return view('pages.static.refund');
})->name('refund-policy');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('addresses')->name('addresses.')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\AddressController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\AddressController::class, 'store'])->name('store');
    Route::put('/{address}', [\App\Http\Controllers\AddressController::class, 'update'])->name('update');
    Route::delete('/{address}', [\App\Http\Controllers\AddressController::class, 'destroy'])->name('destroy');
    Route::post('/{address}/set-default', [\App\Http\Controllers\AddressController::class, 'setDefault'])->name('set-default');
});

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('index');
    Route::post('/process', [\App\Http\Controllers\CheckoutController::class, 'process'])->name('process');
    Route::get('/success/{order}', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('success');
    Route::post('/coupon/apply',  [CouponController::class, 'apply'])->name('coupon.apply')->middleware('throttle:coupon');
    Route::post('/coupon/remove', [CouponController::class, 'remove'])->name('coupon.remove');
});

// Cart routes (accessible by guests)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Razorpay routes (public for webhook; verify/create use auth-gated session check)
Route::post('/payment/create', [RazorpayController::class, 'createOrder'])->middleware('throttle:payment_create')->name('payment.create');
Route::post('/payment/verify', [RazorpayController::class, 'verifyPayment'])->middleware('throttle:payment_verify')->name('payment.verify');
Route::get('/payment/status/{order}', [RazorpayController::class, 'paymentStatus'])->name('payment.status');
Route::post('/payment/retry/{order}', [RazorpayController::class, 'retryPayment'])->name('payment.retry');
Route::post('/webhooks/razorpay', [RazorpayController::class, 'webhook'])->middleware('throttle:payment_webhook')->name('payment.webhook');

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/operations/export', OperationsExportController::class)->name('admin.operations.export');
    Route::post('/admin/operations/failed-job-retry/{uuid}', FailedJobRetryController::class)->name('admin.operations.failed-job-retry');

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
    });

    // Reviews (authenticated, purchased users only)
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Refunds
    Route::get('/orders/{order}/refund',  [RefundController::class, 'create'])->name('refunds.create');
    Route::post('/orders/{order}/refund', [RefundController::class, 'store'])->name('refunds.store');

    // Invoice download — gated via OrderPolicy::downloadInvoice
    Route::get('/orders/{order}/invoice', function (\App\Models\Order $order) {
        abort_if(! auth()->check(), 403);
        abort_unless(auth()->user()->can('downloadInvoice', $order), 403);
        abort_unless(in_array($order->payment_status, ['captured', 'refunded']), 422, 'Invoice not available yet.');
        return app(\App\Services\InvoiceService::class)->download($order);
    })->name('orders.invoice');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AccountController::class, 'index'])->name('index');
        Route::get('/orders', [\App\Http\Controllers\AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [\App\Http\Controllers\AccountController::class, 'showOrder'])->name('orders.show');
    });

    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WishlistController::class, 'index'])->name('index');
        Route::post('/add', [\App\Http\Controllers\WishlistController::class, 'add'])->name('add');
        Route::delete('/remove/{product_id}', [\App\Http\Controllers\WishlistController::class, 'remove'])->name('remove');
    });
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';