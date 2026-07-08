<x-layouts.app>
    <x-slot:title>{{ $category->name }} Jewelry | Svaraa Jewels</x-slot:title>

    {{-- Category Banner --}}
    <div class="relative bg-gray-900 h-80 flex items-center justify-center">
        @if($category->image)
            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="absolute inset-0 w-full h-full object-cover opacity-40">
        @else
            <div class="absolute inset-0 w-full h-full bg-[#6E0F12] opacity-80"></div>
        @endif
        
        <div class="relative z-10 text-center px-4">
            <h1 class="text-4xl md:text-6xl font-serif text-white mb-4 drop-shadow-md">{{ $category->name }}</h1>
            @if($category->description)
                <p class="text-white/90 max-w-2xl mx-auto text-lg drop-shadow">{{ $category->description }}</p>
            @endif
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col lg:flex-row gap-12">
            {{-- Sidebar --}}
            <div class="w-full lg:w-1/4 flex-shrink-0">
                <x-shop.filter-sidebar :categories="$categories" :filters="$filters" :isCategoryPage="true" />
            </div>

            {{-- Product Grid --}}
            <div class="w-full lg:w-3/4">
                {{-- Toolbar --}}
                <div class="flex flex-col sm:flex-row justify-between items-center mb-8 pb-4 border-b border-gray-100 gap-4">
                    <p class="text-gray-500 text-sm">
                        Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results
                    </p>
                    <x-shop.sort-dropdown :currentSort="$filters['sort'] ?? 'newest'" />
                </div>

                {{-- Grid --}}
                @if($products->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($products as $product)
                            <x-shop.product-card :product="$product" />
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-16 border-t border-gray-100 pt-8">
                        {{ $products->withQueryString()->links() }}
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="text-center py-24 bg-gray-50 border border-gray-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 mx-auto text-gray-400 mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <h3 class="text-xl font-serif text-gray-900 mb-2">No products found</h3>
                        <p class="text-gray-500 mb-6">There are currently no products in this category.</p>
                        <a href="{{ route('products.index') }}" class="inline-block border border-[#6E0F12] text-[#6E0F12] px-6 py-2.5 hover:bg-[#6E0F12] hover:text-white transition-colors font-medium">
                            View All Earrings
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
