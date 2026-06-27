<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(protected ReviewService $reviewService) {}

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title'  => 'nullable|string|max:120',
            'review' => 'nullable|string|max:2000',
        ]);

        try {
            $this->reviewService->submit(auth()->user(), $product, $validated);

            return back()->with('success', 'Your review has been submitted and is pending approval. Thank you!');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
