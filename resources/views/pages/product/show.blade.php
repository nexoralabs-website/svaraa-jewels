<x-layouts.app>
    <x-slot:title>{{ $product->name }} | Svaraa Jewels</x-slot:title>
    <x-slot:seo>
        <x-seo-meta
            :title="$product->seo_title"
            :description="$product->seo_description"
            :canonical="route('products.show', $product->slug)"
            :og-image="$product->og_image ? Storage::url($product->og_image) : null"
            :schema="['@type' => 'Product', 'name' => $product->name, 'aggregateRating' => $product->review_count > 0 ? ['@type' => 'AggregateRating', 'ratingValue' => $product->average_rating, 'reviewCount' => $product->review_count] : null, 'offers' => ['@type' => 'Offer', 'price' => $product->price, 'priceCurrency' => 'INR', 'availability' => $product->stock > 0 ? 'InStock' : 'OutOfStock']]" />
    </x-slot:seo>

    {{-- Breadcrumb --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('home') }}" class="hover:text-[#6E0F12] transition-colors">Home</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <a href="{{ route('products.index') }}" class="hover:text-[#6E0F12] transition-colors">Shop</a>
                        </div>
                    </li>
                    @if($product->category)
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <a href="{{ route('categories.show', $product->category->slug) }}" class="hover:text-[#6E0F12] transition-colors">{{ $product->category->name }}</a>
                        </div>
                    </li>
                    @endif
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <span class="text-gray-400">{{ $product->name }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Product Section --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="flex flex-col lg:flex-row gap-12 xl:gap-16">
            <div class="w-full lg:w-1/2">
                <x-product.gallery :product="$product" />
            </div>
            <div class="w-full lg:w-1/2">
                <x-product.details :product="$product" />
            </div>
        </div>

        {{-- Reviews Section --}}
        <x-product.reviews
            :product="$product"
            :reviews="$reviews"
            :distribution="$distribution"
            :canReview="$canReview"
            :hasReviewed="$hasReviewed" />
    </div>

    {{-- Related Products --}}
    @if($relatedProducts && $relatedProducts->count() > 0)
    <div class="bg-gray-50 border-t border-gray-100 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-serif text-center text-gray-900 mb-10">You May Also Like</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($relatedProducts as $relatedProduct)
                    <x-shop.product-card :product="$relatedProduct" />
                @endforeach
            </div>
        </div>
    </div>
    @endif
</x-layouts.app>