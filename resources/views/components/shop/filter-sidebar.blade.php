@props(['categories', 'filters' => [], 'isCategoryPage' => false])

<div class="space-y-8" x-data="{
    priceMin: {{ request('min_price', 0) }},
    priceMax: {{ request('max_price', 10000) }},
    applyFilters() {
        let url = new URL(window.location.href);
        url.searchParams.set('min_price', this.priceMin);
        url.searchParams.set('max_price', this.priceMax);
        if (this.inStock) {
            url.searchParams.set('in_stock', 1);
        } else {
            url.searchParams.delete('in_stock');
        }
        window.location.href = url.toString();
    },
    inStock: {{ request('in_stock') ? 'true' : 'false' }},
}">
    {{-- Search --}}
    @if(!$isCategoryPage)
    <div>
        <h3 class="text-sm font-medium text-gray-900 uppercase tracking-widest mb-4 border-b border-[#C8A35D]/30 pb-2">Search</h3>
        <form action="{{ route('products.index') }}" method="GET" class="relative">
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}"
                placeholder="Search earrings..." 
                class="w-full border border-gray-200 pl-4 pr-10 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] transition-colors outline-none"
            >
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#6E0F12]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </button>
        </form>
    </div>
    @endif

    {{-- Price Range --}}
    <div>
        <h3 class="text-sm font-medium text-gray-900 uppercase tracking-widest mb-4 border-b border-[#C8A35D]/30 pb-2">Price Range</h3>
        <div class="flex items-center space-x-2">
            <input type="number" min="0" max="100000" step="10" x-model.number="priceMin" class="w-1/2 border border-gray-200 rounded px-2 py-1 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D]" placeholder="Min">
            <span class="text-gray-500">-</span>
            <input type="number" min="0" max="100000" step="10" x-model.number="priceMax" class="w-1/2 border border-gray-200 rounded px-2 py-1 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D]" placeholder="Max">
        </div>
        <button type="button" @click="applyFilters()" class="mt-3 w-full bg-[#6E0F12] text-white py-2 rounded hover:bg-[#520b0d] transition-colors">Apply</button>
    </div>

    {{-- Availability --}}
    <div>
        <h3 class="text-sm font-medium text-gray-900 uppercase tracking-widest mb-4 border-b border-[#C8A35D]/30 pb-2">Availability</h3>
        <label class="inline-flex items-center">
            <input type="checkbox" x-model="inStock" class="form-checkbox h-4 w-4 text-[#6E0F12] border-gray-300 rounded">
            <span class="ml-2 text-sm text-gray-700">In Stock Only</span>
        </label>
    </div>

    {{-- Info Section (Placeholder for Premium Quality) --}}
    <div class="bg-gray-50 p-6 border border-gray-100">
        <h3 class="text-sm font-medium text-[#6E0F12] uppercase tracking-widest mb-3">Premium Quality</h3>
        <p class="text-xs text-gray-600 leading-relaxed">
            All our jewelry is crafted with precision and certified for purity. Experience the luxury of Svaraa.
        </p>
    </div>
</div>
