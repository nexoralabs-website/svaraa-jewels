<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class CartService
{
    protected const SESSION_KEY = 'cart';
    protected const TAX_RATE = 0.18; // 18% GST for India

    public function getCartItems(): Collection
    {
        if (Auth::check()) {
            return Cart::with(['product', 'product.images', 'product.category'])
                ->where('user_id', Auth::id())
                ->get();
        }

        return $this->getSessionCartItems();
    }

    public function getCartTotal(): float
    {
        return $this->getCartItems()->sum(function ($item) {
            $price = $item->product->price ?? 0;
            return $price * $item->quantity;
        });
    }

    public function getCartCount(): int
    {
        return $this->getCartItems()->sum('quantity');
    }

    public function getCartSummary(): array
    {
        $items = $this->getCartItems();
        $subtotal = $items->sum(fn ($item) => ($item->product->price ?? 0) * $item->quantity);

        return [
            'count' => $items->sum('quantity'),
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'items' => $items,
        ];
    }

    public function getCartItemsForAlpine(): array
    {
        return $this->getCartItems()->map(function ($item) {
            $imageUrl = asset('images/placeholder.jpg');
            if (!empty($item->product->thumbnail)) {
                $imageUrl = Storage::url($item->product->thumbnail);
            } elseif (!empty($item->product->images) && $item->product->images->count() > 0) {
                $imageUrl = Storage::url($item->product->images->first()->image);
            }

            return [
                'id' => $item->id,
                'product_id' => $item->product_id ?? $item->product->id ?? $item->product->product_id ?? null,
                'name' => $item->product->name,
                'price' => (float) $item->product->price,
                'quantity' => (int) $item->quantity,
                'image' => $imageUrl,
                'slug' => $item->product->slug ?? '',
                'stock' => $item->product->stock ?? 99,
                'category' => $item->product->category?->name ?? 'Jewelry',
            ];
        })->values()->toArray();
    }

    public function addToCart(int $productId, int $quantity = 1): void
    {
        $product = Product::with('images')->findOrFail($productId);

        $currentQuantity = $this->getExistingCartQuantity($productId);
        if ($currentQuantity + $quantity > $product->stock) {
            throw new \Exception("Insufficient stock. Only {$product->stock} of {$product->name} available.");
        }

        if (Auth::check()) {
            $this->upsertDbCart(Auth::id(), $productId, $quantity);
        } else {
            $this->updateSessionCart($productId, $quantity);
        }
    }

    public function updateCartItem(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($productId);
            return;
        }

        $product = Product::findOrFail($productId);

        if ($quantity > $product->stock) {
            throw new \Exception("Insufficient stock. Only {$product->stock} of {$product->name} available.");
        }

        if (Auth::check()) {
            Cart::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->update(['quantity' => $quantity]);
        } else {
            $cart = Session::get(self::SESSION_KEY, []);
            if (isset($cart[$productId])) {
                $cart[$productId] = $quantity;
                Session::put(self::SESSION_KEY, $cart);
            }
        }
    }

    public function removeFromCart(int $productId): void
    {
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->delete();
        } else {
            $cart = Session::get(self::SESSION_KEY, []);
            unset($cart[$productId]);
            Session::put(self::SESSION_KEY, $cart);
        }
    }

    public function clearCart(): void
    {
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())->delete();
        } else {
            Session::forget(self::SESSION_KEY);
        }
    }

    public function mergeGuestCartToUser(int $userId): void
    {
        $guestCart = Session::get(self::SESSION_KEY, []);
        if (empty($guestCart)) {
            return;
        }

        foreach ($guestCart as $productId => $quantity) {
            $product = Product::find($productId);
            if (!$product) {
                continue;
            }

            $adjustedQty = min($quantity, $product->stock);

            $this->upsertDbCart($userId, $productId, $adjustedQty);
        }

        Session::forget(self::SESSION_KEY);
    }

    private function getExistingCartQuantity(int $productId): int
    {
        $items = $this->getCartItems();
        $item = $items->firstWhere('product_id', $productId);
        if (!$item && $items->firstWhere('product.id', $productId)) {
            $item = $items->firstWhere('product.id', $productId);
        }
        return $item ? $item->quantity : 0;
    }

    private function getSessionCartItems(): BaseCollection
    {
        $cart = Session::get(self::SESSION_KEY, []);
        $items = BaseCollection::make();

        foreach ($cart as $productId => $quantity) {
            $product = Product::with('images', 'category')->find($productId);
            if ($product) {
                $items->push((object)[
                    'id' => "session_{$productId}",
                    'product_id' => $productId,
                    'product' => $product,
                    'quantity' => $quantity,
                ]);
            }
        }

        return $items;
    }

    private function upsertDbCart(int $userId, int $productId, int $quantity): void
    {
        $existing = Cart::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->update(['quantity' => $existing->quantity + $quantity]);
        } else {
            Cart::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }
    }

    private function updateSessionCart(int $productId, int $quantity): void
    {
        $cart = Session::get(self::SESSION_KEY, []);
        $cart[$productId] = ($cart[$productId] ?? 0) + $quantity;
        Session::put(self::SESSION_KEY, $cart);
    }
}
