<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ProductService;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected ReviewService  $reviewService
    ) {}

    public function index(Request $request): View
    {
        $filters = [
            'category'  => $request->get('category', 'earrings'),
            'search'    => $request->get('search'),
            'sort'      => $request->get('sort'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'in_stock'  => $request->get('in_stock'),
        ];

        $products   = $this->productService->getFilteredProducts($filters);
        $categories = $this->productService->getActiveCategories();

        return view('pages.shop.index', compact('products', 'categories', 'filters'));
    }

    public function show(string $slug): View
    {
        $product         = $this->productService->getProductBySlug($slug);
        $relatedProducts = $this->productService->getRelatedProducts($product, 4);

        $reviews      = $this->reviewService->getProductReviews($product);
        $distribution = $this->reviewService->getRatingDistribution($product);

        $canReview   = false;
        $hasReviewed = false;

        if ($user = auth()->user()) {
            $canReview   = $this->reviewService->hasUserPurchasedProduct($user, $product);
            $hasReviewed = $this->reviewService->hasUserReviewedProduct($user, $product);
        }

        return view('pages.product.show', compact(
            'product',
            'relatedProducts',
            'reviews',
            'distribution',
            'canReview',
            'hasReviewed'
        ));
    }

    /**
     * Legacy method — redirects non-earring categories to the main shop.
     */
    public function category(Request $request, string $slug): View
    {
        if ($slug !== 'earrings') {
            return redirect()->route('products.index', 301);
        }

        $category = Category::where('slug', 'earrings')->firstOrFail();

        $filters = [
            'category'  => $slug,
            'search'    => $request->get('search'),
            'sort'      => $request->get('sort'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'in_stock'  => $request->get('in_stock'),
        ];

        $products   = $this->productService->getFilteredProducts($filters);
        $categories = $this->productService->getActiveCategories();

        return view('pages.shop.category', compact('products', 'categories', 'filters', 'category'));
    }
}
