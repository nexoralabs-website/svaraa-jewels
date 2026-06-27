<x-layouts.app>
    <x-slot:title>{{ $product->name }}</x-slot:title>

    <div class="container mx-auto px-4 py-12">
        <!-- Breadcrumb -->
        <nav class="mb-8 text-sm">
            <ol class="flex items-center space-x-2">
                <li><a href="{{ route('home') }}" class="text-[#7B6755] hover:text-[#6E0F12] transition-colors">Home</a></li>
                <li><span class="text-[#7B6755]">/</span></li>
                <li><a href="{{ route('products.index') }}" class="text-[#7B6755] hover:text-[#6E0F12] transition-colors">Products</a></li>
                <li><span class="text-[#7B6755]">/</span></li>
                <li class="text-[#2E1A12]">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="flex flex-col lg:flex-row gap-12">
            <!-- Product Images -->
            <div class="lg:w-1/2">
                <div class="card-premium p-4">
                    <div class="aspect-square bg-[#E8DCCB] rounded-lg flex items-center justify-center mb-4">
                        <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" id="mainImage" class="w-full h-full object-cover rounded-lg" />
                    </div>
                    @if($product->images->count() > 0)
                        <div class="grid grid-cols-4 gap-3">
                            <div class="aspect-square bg-[#E8DCCB] rounded-lg overflow-hidden cursor-pointer border-2 border-[#C8A35D]">
                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover" onclick="document.getElementById('mainImage').src='{{ $product->thumbnail_url }}'" />
                            </div>
                            @foreach($product->images as $image)
                                <div class="aspect-square bg-[#E8DCCB] rounded-lg overflow-hidden cursor-pointer border-2 border-transparent hover:border-[#C8A35D] transition-colors">
                                    <img src="{{ $image->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover" onclick="document.getElementById('mainImage').src='{{ $image->image_url }}'" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Product Info -->
            <div class="lg:w-1/2">
                <p class="text-[#C8A35D] font-medium mb-2">{{ $product->category?->name }}</p>
                <h1 class="text-3xl md:text-4xl font-bold text-[#2E1A12] mb-4" style="font-family: 'Playfair Display', serif;">{{ $product->name }}</h1>
                <div class="text-3xl font-bold text-[#6E0F12] mb-6">₹{{ number_format($product->price, 0) }}</div>

                <!-- Product Details -->
                <div class="mb-8 space-y-4">
                    <div class="flex items-center gap-4">
                        <span class="text-[#7B6755] w-24">Purity:</span>
                        <span class="text-[#2E1A12] font-medium">{{ $product->purity }}</span>
                    </div>
                    @if($product->weight)
                        <div class="flex items-center gap-4">
                            <span class="text-[#7B6755] w-24">Weight:</span>
                            <span class="text-[#2E1A12] font-medium">{{ $product->weight }} g</span>
                        </div>
                    @endif
                </div>

                <!-- Description -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-[#2E1A12] mb-3" style="font-family: 'Playfair Display', serif;">Description</h3>
                    <p class="text-[#7B6755] leading-relaxed">{{ $product->description }}</p>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <form action="{{ route('cart.add') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.74 4.435c.58 3.522-.997 6.977-4.385 8.106a.75.75 0 00-.084 1.472l4.14 2.07a2.25 2.25 0 002.966-1.012l2.49-4.979a2.25 2.25 0 00-1.34-3.076l-3.088-.943c-.624-.19-1.042-.782-1.042-1.429v-.375c0-1.036.84-1.875 1.875-1.875h5.25c1.036 0 1.875.84 1.875 1.875v.375c0 .647-.418 1.239-1.042 1.429l-3.088.943a2.25 2.25 0 00-1.34 3.076l2.49 4.979a2.25 2.25 0 002.966 1.012l4.14-2.07a.75.75 0 00-.084-1.472c-3.388-1.129-4.965-4.584-4.385-8.106l.74-4.435c.132-.493.577-.836 1.087-.836H21.75" />
                            </svg>
                            Add to Cart
                        </button>
                    </form>
                    <button class="btn-secondary flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        Wishlist
                    </button>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts->count() > 0)
            <div class="mt-20">
                <h2 class="text-3xl font-bold text-[#2E1A12] mb-10 text-center" style="font-family: 'Playfair Display', serif;">You May Also Like</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($relatedProducts as $relatedProduct)
                        <x-products.card :product="$relatedProduct" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
