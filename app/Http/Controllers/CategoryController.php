<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ProductService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function show(Request $request, string $slug)
    {
        if ($slug !== 'earrings') {
            return redirect()->route('products.index', 301);
        }

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

        $category = Category::where('slug', 'earrings')->firstOrFail();

        return view('pages.shop.category', compact('category', 'products', 'categories', 'filters'));
    }
}
