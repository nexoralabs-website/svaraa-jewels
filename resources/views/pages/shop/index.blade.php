<x-layouts.app>
    <x-slot:title>Shop Earrings | Svaraa Jewels</x-slot:title>

    {{-- Page Header --}}
    <div class="bg-[#FFFFF0] py-16 border-b border-[#C8A35D]/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-serif text-[#6E0F12] mb-4">Shop Earrings</h1>
            <p class="text-gray-600 max-w-2xl mx-auto text-lg">Discover our premium range of meticulously crafted earrings.</p>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col lg:flex-row gap-12">
            {{-- Sidebar --}}
            <div class="w-full lg:w-1/4 flex-shrink-0">
                <x-shop.filter-sidebar :categories="$categories" :filters="$filters" />
            </div>

            {{-- Product Grid --}}
            <div class="w-full lg:w-3/4">
                {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 pb-4 border-b border-gray-100 gap-4">
    <p class="text-gray-500 text-sm">
        Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results
    </p>

    <!-- Active filter badges -->
    <div class="flex flex-wrap gap-2 items-center">
        @if(request('search'))
            <span class="inline-flex items-center bg-[#E8DCCB] text-[#2E1A12] px-3 py-1 rounded-full text-xs font-medium">
                Search: "{{ request('search') }}"
                <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="ml-1 text-gray-500 hover:text-gray-700">&times;</a>
            </span>
        @endif
        @if(request('min_price') || request('max_price'))
            <span class="inline-flex items-center bg-[#E8DCCB] text-[#2E1A12] px-3 py-1 rounded-full text-xs font-medium">
                Price: ₹{{ request('min_price', 0) }} - ₹{{ request('max_price', '∞') }}
                <a href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null]) }}" class="ml-1 text-gray-500 hover:text-gray-700">&times;</a>
            </span>
        @endif
        @if(request('in_stock'))
            <span class="inline-flex items-center bg-[#E8DCCB] text-[#2E1A12] px-3 py-1 rounded-full text-xs font-medium">
                In Stock
                <a href="{{ request()->fullUrlWithQuery(['in_stock' => null]) }}" class="ml-1 text-gray-500 hover:text-gray-700">&times;</a>
            </span>
        @endif
        @if(request()->except(['page','sort','search','min_price','max_price','in_stock']))
            <a href="{{ route('products.index') }}" class="ml-4 text-[#6E0F12] underline hover:text-[#2E1A12]">Clear All Filters</a>
        @endif
    </div>

    <div class="flex items-center gap-2">
        <x-shop.sort-dropdown :currentSort="$filters['sort'] ?? 'newest'" />
        <!-- Grid/List toggle placeholder -->
        <button type="button" class="p-2 border border-gray-200 rounded hover:bg-[#E8DCCB] transition-colors" title="Grid/List view">
            <svg class="w-5 h-5 text-[#2E1A12]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>
</div>

                {{-- Grid --}}
                @if($products->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
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
                        <p class="text-gray-500 mb-6">We couldn't find anything matching your current filters.</p>
                        <a href="{{ route('products.index') }}" class="inline-block border border-[#6E0F12] text-[#6E0F12] px-6 py-2.5 hover:bg-[#6E0F12] hover:text-white transition-colors font-medium">
                            Clear Filters
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
