<x-layouts.app>
    <x-slot:title>My Wishlist | Svaraa Jewels</x-slot:title>

    <div class="bg-[#FFFFF0] py-12 border-b border-[#C8A35D]/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl font-serif text-[#6E0F12]">My Wishlist</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        @if(session('success'))
            <div class="mb-8 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if($wishlistItems->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($wishlistItems as $item)
                    <div class="group relative bg-white border border-gray-100 hover:border-[#C8A35D]/50 transition-colors shadow-sm flex flex-col h-full overflow-hidden">
                        <!-- Image -->
                        <div class="relative aspect-square overflow-hidden bg-gray-50">
                            <a href="{{ route('products.show', $item->product) }}">
                                <img src="{{ $item->product->thumbnail ? asset('storage/' . $item->product->thumbnail) : asset('images/placeholder.jpg') }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            </a>
                            
                            <!-- Remove Button -->
                            <form action="{{ route('wishlist.remove', $item->product_id) }}" method="POST" class="absolute top-3 right-3 z-10">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 bg-white/90 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-white shadow-sm transition-all" title="Remove from wishlist">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-5 flex flex-col flex-1">
                            <h3 class="text-sm font-medium text-gray-900 mb-1">
                                <a href="{{ route('products.show', $item->product) }}" class="hover:text-[#6E0F12] transition-colors">{{ $item->product->name }}</a>
                            </h3>
                            <p class="text-[#C8A35D] font-medium text-sm mb-4">₹{{ number_format($item->product->price, 2) }}</p>
                            
                            <div class="mt-auto">
                                <form action="{{ route('cart.add', $item->product_id) }}" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full bg-[#6E0F12] text-white py-2.5 text-xs font-medium tracking-widest uppercase hover:bg-[#520b0d] transition-colors shadow-sm">
                                        Move to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-20 bg-gray-50 border border-gray-100 rounded-lg max-w-3xl mx-auto">
                <div class="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center mb-6 shadow-sm border border-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-[#C8A35D]">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-serif text-gray-900 mb-2">Your wishlist is empty</h2>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">Save your favorite pieces here to easily find them later or move them to your cart when you're ready.</p>
                <a href="{{ route('products.index') }}" class="inline-block bg-[#6E0F12] text-white px-8 py-3.5 uppercase tracking-widest text-sm font-medium hover:bg-[#520b0d] transition-colors shadow-sm">
                    Explore Collection
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>
