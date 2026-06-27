<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(protected SearchService $searchService) {}

    /**
     * Full search results page.
     */
    public function index(Request $request): View
    {
        $query   = trim($request->get('q', ''));
        $filters = $request->only(['category', 'min_price', 'max_price', 'in_stock']);

        if (blank($query)) {
            // LengthAwarePaginator instead of collect()->paginate() which doesn't exist
            $products = new LengthAwarePaginator([], 0, 12);
        } else {
            $products = $this->safeSearch($query, $filters);
        }

        return view('pages.search.index', [
            'query'    => $query,
            'products' => $products,
            'filters'  => $filters,
        ]);
    }

    /**
     * JSON autocomplete — returns ≤8 suggestions, cached 60s.
     */
    public function suggest(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = $this->searchService->suggest($query);

        return response()->json($suggestions);
    }

    private function safeSearch(string $query, array $filters): mixed
    {
        try {
            return $this->searchService->searchProducts($query, $filters);
        } catch (\Throwable $e) {
            // Meilisearch unavailable — fall back to DB search silently
            return $this->searchService->dbSearch($query, $filters);
        }
    }
}
