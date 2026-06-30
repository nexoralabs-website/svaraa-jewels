<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function index(): View
    {
        $summary = $this->cartService->getCartSummary();

        return view('pages.cart.index', [
            'cartItems' => $summary['items'],
            'cartTotal' => $summary['total'],
            'cartCount' => $summary['count'],
        ]);
    }

    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        try {
            $this->cartService->addToCart(
                $validated['product_id'],
                $validated['quantity'] ?? 1
            );
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        $summary = $this->cartService->getCartSummary();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart!',
                'cartCount' => $summary['count'],
                'cartTotal' => $summary['total'],
                'cartItems' => $this->transformCartItemsForResponse($summary['items']),
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart!');
    }

    public function update(Request $request, int $productId): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $this->cartService->updateCartItem($productId, $validated['quantity']);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        $summary = $this->cartService->getCartSummary();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart updated!',
                'cartCount' => $summary['count'],
                'cartTotal' => $summary['total'],
                'cartItems' => $this->transformCartItemsForResponse($summary['items']),
            ]);
        }

        return redirect()->back()->with('success', 'Cart updated!');
    }

    public function remove(Request $request, int $productId): RedirectResponse|JsonResponse
    {
        $this->cartService->removeFromCart($productId);

        $summary = $this->cartService->getCartSummary();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product removed from cart!',
                'cartCount' => $summary['count'],
                'cartTotal' => $summary['total'],
                'cartItems' => $this->transformCartItemsForResponse($summary['items']),
            ]);
        }

        return redirect()->back()->with('success', 'Product removed from cart!');
    }

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $this->cartService->clearCart();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared!',
                'cartCount' => 0,
                'cartTotal' => 0,
                'cartItems' => [],
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Cart cleared!');
    }

    private function transformCartItemsForResponse($items): array
    {
        return $items->map(function ($item) {
            $imageUrl = null;
            if ($item->product->thumbnail) {
                $imageUrl = Storage::url($item->product->thumbnail);
            } elseif ($item->product->images->isNotEmpty()) {
                $imageUrl = Storage::url($item->product->images->first()->image);
            } else {
                $imageUrl = asset('images/placeholder.jpg');
            }

            return [
                'id' => $item->id,
                'product_id' => $item->product_id ?? $item->product->id,
                'name' => $item->product->name,
                'price' => (float) $item->product->price,
                'quantity' => (int) $item->quantity,
                'image' => $imageUrl,
                'slug' => $item->product->slug,
                'stock' => $item->product->stock,
                'category' => $item->product->category?->name ?? 'Jewelry',
                'subtotal' => (float) $item->product->price * (int) $item->quantity,
            ];
        })->values()->toArray();
    }
}
