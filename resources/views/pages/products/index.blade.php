<x-layouts.app>
    <x-slot:title>Our Earrings Collection</x-slot:title>

    <div class="container mx-auto px-4 py-12">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-[#2E1A12] mb-4" style="font-family: 'Playfair Display', serif;">Our Exquisite Earrings</h1>
            <p class="text-[#7B6755] max-w-2xl mx-auto">Discover handcrafted earrings that blend timeless elegance with modern sophistication</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <div class="lg:w-64 flex-shrink-0">
                <div class="card-premium p-6 sticky top-28">
            <!-- Search -->
            <form action="{{ route('products.index') }}" method="GET" class="mb-6">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search jewelry..." class="w-full pl-10 pr-4 py-2 border border-[#E8DCCB] rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#C8A35D]" />
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-[#7B6755]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}" />
                @endif
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}" />
                @endif
            </form>

            <!-- Categories — earrings only, single entry -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-[#2E1A12] mb-4" style="font-family: 'Playfair Display', serif;">Category</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('products.index', ['search' => request('search'), 'sort' => request('sort')]) }}" class="text-[#6E0F12] font-medium transition-colors">All Earrings</a>
                    </li>
                </ul>
            </div>

            <!-- Sort -->
            <div>
                <h3 class="text-lg font-semibold text-[#2E1A12] mb-4" style="font-family: 'Playfair Display', serif;">Sort By</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('products.index', ['category' => request('category'), 'search' => request('search'), 'sort' => 'newest']) }}" class="{{ request('sort', 'newest') === 'newest' ? 'text-[#6E0F12] font-medium' : 'text-[#7B6755] hover:text-[#6E0F12]' }} transition-colors">Newest Arrivals</a>
                    </li>
                    <li>
                        <a href="{{ route('products.index', ['category' => request('category'), 'search' => request('search'), 'sort' => 'price-low']) }}" class="{{ request('sort') === 'price-low' ? 'text-[#6E0F12] font-medium' : 'text-[#7B6755] hover:text-[#6E0F12]' }} transition-colors">Price: Low to High</a>
                    </li>
                    <li>
                        <a href="{{ route('products.index', ['category' => request('category'), 'search' => request('search'), 'sort' => 'price-high']) }}" class="{{ request('sort') === 'price-high' ? 'text-[#6E0F12] font-medium' : 'text-[#7B6755] hover:text-[#6E0F12]' }} transition-colors">Price: High to Low</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

            <!-- Product Grid -->
            <div class="flex-1">
                @if($products->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($products as $product)
                            <x-products.card :product="$product" />
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-12">
                        {{ $products->appends(request()->except('page'))->links() }}
                    </div>
                @else
                    <div class="text-center py-20">
                        <h3 class="text-2xl font-bold text-[#2E1A12] mb-4" style="font-family: 'Playfair Display', serif;">No earrings found</h3>
                        <p class="text-[#7B6755] mb-8">We couldn't find any earrings matching your criteria.</p>
                        <a href="{{ route('products.index') }}" class="btn-primary inline-block">Browse All Earrings</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
