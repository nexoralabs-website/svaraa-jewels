@php
    $cartItems = collect($cartItems);

    $initialCartItems = $cartItems->map(function ($item) {
        $imageUrl = asset('images/placeholder.jpg');
        if (!empty($item->product->thumbnail)) {
            $imageUrl = asset('storage/' . $item->product->thumbnail);
        } elseif (!empty($item->product->images) && $item->product->images->count() > 0) {
            $imageUrl = asset('storage/' . $item->product->images->first()->image);
        }

        return [
            'id' => $item->id,
            'product_id' => $item->product_id ?? $item->product->id,
            'name' => $item->product->name,
            'price' => (float) $item->product->price,
            'quantity' => (int) $item->quantity,
            'image' => $imageUrl,
            'slug' => $item->product->slug ?? '',
            'stock' => $item->product->stock ?? 99,
            'category' => $item->product->category?->name ?? 'Jewelry',
        ];
    })->values()->toArray();
    
    $cartCount = $cartItems->sum('quantity');
    $taxAmount = $cartTotal * 0.18;
    $grandTotal = $cartTotal + $taxAmount;
@endphp

<x-layouts.app>
    <x-slot:title>Your Cart | Svaraa Jewels</x-slot:title>

    {{-- Page Header --}}
    <div class="bg-[#FFFFF0] py-12 border-b border-[#C8A35D]/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-4xl font-serif text-[#6E0F12]">Shopping Cart</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        {{-- Flash Messages --}}
        @if(session('success') || session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="mb-8">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('error') }}
                </div>
            @endif
        </div>
        @endif

        @if($cartItems->isEmpty())
            <div class="text-center py-20 bg-gray-50 border border-gray-100 rounded-lg max-w-3xl mx-auto">
                <div class="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center mb-6 shadow-sm border border-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-10 h-10 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-serif text-gray-900 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">Looks like you haven't added any premium jewelry to your cart yet.</p>
                <a href="{{ route('products.index') }}" class="inline-block bg-[#6E0F12] text-white px-8 py-3.5 uppercase tracking-widest text-sm font-medium hover:bg-[#520b0d] transition-colors shadow-sm">
                    Explore Collection
                </a>
            </div>
        @else
            {{-- Cart Layout --}}
            <div class="flex flex-col lg:flex-row gap-12 xl:gap-16">
                {{-- Items List --}}
                <div class="w-full lg:w-2/3">
                    <div class="hidden sm:grid grid-cols-12 gap-4 pb-4 border-b border-gray-200 text-xs font-medium text-gray-500 uppercase tracking-widest">
                        <div class="col-span-8">Product</div>
                        <div class="col-span-4 text-right">Total</div>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($cartItems as $item)
                            <x-cart.item :item="$item" />
                        @endforeach
                    </div>
                </div>

                {{-- Summary --}}
                <div class="w-full lg:w-1/3">
                    <x-cart.summary :subtotal="$cartTotal" :tax="$taxAmount" :total="$grandTotal" :count="$cartCount" />
                </div>
            </div>
        @endif
    </div>

    {{-- Mobile Sticky Footer --}}
    @if(!$cartItems->isEmpty())
    <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur border-t border-gray-200 shadow-lg z-40">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div>
                <span class="text-xs text-gray-500 uppercase tracking-widest">Total</span>
                <p class="text-lg font-medium text-[#6E0F12]">&#8377;{{ number_format($grandTotal, 2) }}</p>
            </div>
            <a href="{{ route('checkout.index') }}" class="bg-[#C8A35D] text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-[#b8934d] transition-colors">
                Checkout
            </a>
        </div>
    </div>
    @endif
</x-layouts.app>
