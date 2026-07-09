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
use Illuminate\Support\Str;
use League\Flysystem\UnableToWriteFile;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Temporary storage debug route - REMOVE AFTER DEBUGGING
Route::get('/storage-debug', function () {
    // Get first product with thumbnail for real test file
    $product = App\Models\Product::whereNotNull('thumbnail')->first();
    $testFilePath = $product ? $product->thumbnail : 'products/test.jpg';
    
    return response()->json([
        'driver' => config('filesystems.disks.public.driver'),
        'default_disk' => config('filesystems.default'),
        'filesystem_public_disk' => env('FILESYSTEM_PUBLIC_DISK'),
        'supabase_bucket' => env('SUPABASE_BUCKET'),
        'endpoint' => env('SUPABASE_ENDPOINT'),
        'test_file_path' => $testFilePath,
        'url' => Storage::disk('public')->url($testFilePath),
        'exists' => Storage::disk('public')->exists($testFilePath),
        'files' => Storage::disk('public')->files('products')
    ]);
});

// TEMPORARY OBJECT KEY MISMATCH DEBUG ROUTE - REMOVE AFTER DEBUGGING
Route::get('/storage-keys-debug', function () {
    $disk = Illuminate\Support\Facades\Storage::disk('public');
    
    $output = [
        'first_product' => null,
        'first_product_image' => null,
        'product_thumbnail_details' => null,
        'product_image_details' => null,
        'all_files' => [
            'root' => $disk->allFiles('/'),
            'products' => $disk->allFiles('products')
        ],
        'directories' => $disk->directories('/'),
        'exists_checks' => [
            'products' => $disk->exists('products'),
            '/products' => $disk->exists('/products')
        ],
        'files_checks' => [
            'root' => $disk->files('/'),
            'empty' => $disk->files(''),
            'products' => $disk->files('products')
        ],
        'test_url' => $disk->url('products/test.jpg'),
        'disk_config' => config('filesystems.disks.public'),
        'disk_config_runtime' => method_exists($disk, 'getConfig') ? $disk->getConfig() : 'method_not_available'
    ];

    try {
        $product = \App\Models\Product::first();
        if ($product) {
            $output['first_product'] = [
                'id' => $product->id,
                'thumbnail' => $product->thumbnail
            ];
            
            $trimmedThumbnail = trim($product->thumbnail);
            $output['product_thumbnail_details'] = [
                'raw' => $product->thumbnail,
                'trimmed' => $trimmedThumbnail,
                'url_raw' => $disk->url($product->thumbnail),
                'url_trimmed' => $disk->url($trimmedThumbnail),
                'exists_raw' => $disk->exists($product->thumbnail),
                'exists_trimmed' => $disk->exists($trimmedThumbnail)
            ];
            
            $output['thumbnail_test_url'] = $disk->url($product->thumbnail);
            $output['trimmed_thumbnail_test_url'] = $disk->url($trimmedThumbnail);
        }
    } catch (\Exception $e) {
        $output['first_product_error'] = $e->getMessage();
        $output['first_product_trace'] = $e->getTraceAsString();
    }

    try {
        $productImage = \App\Models\ProductImage::first();
        if ($productImage) {
            $output['first_product_image'] = [
                'id' => $productImage->id,
                'image' => $productImage->image
            ];
            
            $trimmedImage = trim($productImage->image);
            $output['product_image_details'] = [
                'raw' => $productImage->image,
                'trimmed' => $trimmedImage,
                'url_raw' => $disk->url($productImage->image),
                'url_trimmed' => $disk->url($trimmedImage),
                'exists_raw' => $disk->exists($productImage->image),
                'exists_trimmed' => $disk->exists($trimmedImage)
            ];
        }
    } catch (\Exception $e) {
        $output['first_product_image_error'] = $e->getMessage();
        $output['first_product_image_trace'] = $e->getTraceAsString();
    }

    return response()->json($output);
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
]);

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
Route::post('/payment/create', [RazorpayController::class, 'createOrder'])->middleware(['throttle:payment_create', 'payments.enabled'])->name('payment.create');
Route::post('/payment/verify', [RazorpayController::class, 'verifyPayment'])->middleware(['throttle:payment_verify', 'payments.enabled'])->name('payment.verify');
Route::get('/payment/status/{order}', [RazorpayController::class, 'paymentStatus'])->name('payment.status');
Route::post('/payment/retry/{order}', [RazorpayController::class, 'retryPayment'])->middleware('payments.enabled')->name('payment.retry');
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

// TEMPORARY DEBUG ROUTE FOR S3 UPLOAD EXCEPTION CAPTURE - REMOVE AFTER DEBUGGING
Route::get('/s3-upload-exception-debug', function () {
    $disk = Illuminate\Support\Facades\Storage::disk('public');
    $output = [];
    $testKey = 'products/debug-test-file-' . Illuminate\Support\Str::random(16) . '.txt';
    $testContent = 'This is a test file for S3 upload debugging';
    
    $output['config'] = config('filesystems.disks.public');
    $output['test_key'] = $testKey;
    
    // First, temporarily set throw=true on the disk config to get exceptions
    $originalConfig = $output['config'];
    $tempConfig = array_merge($originalConfig, ['throw' => true]);
    
    try {
        // Recreate disk with throw=true temporarily
        $tempDisk = Illuminate\Support\Facades\Storage::build($tempConfig);
        
        $startTime = microtime(true);
        $output['upload_started'] = $startTime;
        
        // Try to upload test file
        $uploadResult = $tempDisk->put($testKey, $testContent, 'public');
        
        $output['upload_result'] = $uploadResult;
        $output['upload_elapsed'] = round(microtime(true) - $startTime, 4);
        
        // If success, check exists and get URL
        if ($uploadResult) {
            $output['exists_after_upload'] = $tempDisk->exists($testKey);
            $output['url_after_upload'] = $tempDisk->url($testKey);
        }
    } catch (\League\Flysystem\UnableToWriteFile $e) {
        $output['exception_type'] = 'UnableToWriteFile';
        $output['flysystem_exception'] = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];
        
        $previous = $e->getPrevious();
        if ($previous instanceof \Aws\S3\Exception\S3Exception) {
            $output['aws_s3_exception'] = [
                'class' => get_class($previous),
                'message' => $previous->getMessage(),
                'code' => $previous->getCode(),
                'aws_error_code' => $previous->getAwsErrorCode(),
                'aws_error_type' => $previous->getAwsErrorType(),
                'http_status_code' => $previous->getStatusCode(),
                'response_body' => $previous->getResponse() ? (string)$previous->getResponse()->getBody() : 'no response',
                'request_id' => $previous->getRequestId(),
                'host_id' => $previous->getHostId(),
                'bucket' => $previous->get('bucket') ?? 'not available',
                'endpoint' => $tempConfig['endpoint'] ?? 'not available',
                'key' => $testKey,
            ];
        } elseif ($previous) {
            $output['previous_exception'] = [
                'class' => get_class($previous),
                'message' => $previous->getMessage(),
                'code' => $previous->getCode(),
                'trace' => $previous->getTraceAsString(),
            ];
        }
    } catch (\Exception $e) {
        $output['general_exception'] = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];
    }
    
    // Check log files for any logged exceptions
    $logFiles = [
        storage_path('logs/laravel.log'),
    ];
    
    $output['log_check'] = [];
    foreach ($logFiles as $logFile) {
        if (file_exists($logFile)) {
            $output['log_check'][$logFile] = [
                'exists' => true,
                'last_50_lines' => file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ? array_slice(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -50) : 'empty log',
            ];
        } else {
            $output['log_check'][$logFile] = ['exists' => false];
        }
    }
    
    // Also check what files are currently in the bucket
    try {
        $output['bucket_files'] = $disk->allFiles('/');
        $output['bucket_directories'] = $disk->directories('/');
    } catch (Exception $e) {
        $output['bucket_list_error'] = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
        ];
    }
    
    return response()->json($output, JSON_PRETTY_PRINT);
})->withoutMiddleware([
    Illuminate\Session\Middleware\StartSession::class,
    Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    Illuminate\Cookie\Middleware\EncryptCookies::class,
    Illuminate\View\Middleware\ShareErrorsFromSession::class,
    Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    Illuminate\Routing\Middleware\SubstituteBindings::class,
]);

// Temporary storage upload debug route
Route::get('/storage-upload-debug', function () {
    $result = [
        'upload_result' => null,
        'exception_class' => null,
        'exception_message' => null,
        'previous_exception_class' => null,
        'previous_exception_message' => null,
        'aws_error_code' => null,
        'aws_error_type' => null,
        'http_status_code' => null,
        'request_id' => null,
        'host_id' => null,
        'endpoint' => null,
        'bucket' => null,
        'object_key' => null,
        'exists' => null,
        'url' => null,
    ];

    $disk = Storage::disk('public');
    $testKey = 'debug-test-' . Str::random(16) . '.txt';
    $testContent = 'Test upload content';

    try {
        // Upload test content
        $result['upload_result'] = $disk->put($testKey, $testContent);

        if ($result['upload_result']) {
            $result['exists'] = $disk->exists($testKey);
            $result['url'] = $disk->url($testKey);
            $result['object_key'] = $testKey;
            $result['bucket'] = config('filesystems.disks.public.bucket');
            $result['endpoint'] = config('filesystems.disks.public.endpoint');
            $disk->delete($testKey);
        }
    } catch (\Throwable $e) {
        $result['exception_class'] = get_class($e);
        $result['exception_message'] = $e->getMessage();

        $previous = $e->getPrevious();
        if ($previous) {
            $result['previous_exception_class'] = get_class($previous);
            $result['previous_exception_message'] = $previous->getMessage();

            if ($previous instanceof \Aws\S3\Exception\S3Exception) {
                $result['aws_error_code'] = $previous->getAwsErrorCode();
                $result['aws_error_type'] = $previous->getAwsErrorType();
                $result['http_status_code'] = $previous->getStatusCode();
                $result['request_id'] = $previous->getRequestId();
                $result['host_id'] = $previous->getHostId();
                $result['endpoint'] = config('filesystems.disks.public.endpoint');
                $result['bucket'] = config('filesystems.disks.public.bucket');
                $result['object_key'] = $testKey;
            }
        }
    }

    return response()->json($result);
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
]);

// TEMP DEBUG ROUTE: Minimal test
Route::get('/storage-list-debug', function () {
    return response()->json([
        'debug' => true,
        'timestamp' => now()->toDateTimeString(),
        'commit' => 'b8a96b5',
        'php_version' => PHP_VERSION,
        'laravel' => app()->version(),
    ]);
})->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
]);

require __DIR__.'/auth.php';