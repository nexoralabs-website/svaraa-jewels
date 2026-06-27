<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlistItems = Wishlist::where('user_id', $request->user()->id)
            ->with('product')
            ->get();

        return view('pages.wishlist.index', compact('wishlistItems'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $exists = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->exists();

        if (! $exists) {
            Wishlist::create([
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
            ]);
            return back()->with('success', 'Product added to wishlist.');
        }

        return back()->with('info', 'Product is already in your wishlist.');
    }

    public function remove(Request $request, $product_id)
    {
        Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $product_id)
            ->delete();

        return back()->with('success', 'Product removed from wishlist.');
    }
}
