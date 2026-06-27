@props([])

<div class="text-center py-20">
    <img src="{{ asset('images/empty-state.png') }}" alt="No products" class="mx-auto mb-6 w-48 h-48 object-cover" />
    <h3 class="text-2xl font-serif text-[#2E1A12] mb-2">No earrings found</h3>
    <p class="text-gray-600 mb-6">We couldn’t find any earrings matching your criteria. Try adjusting the filters or explore our collection.</p>
    <a href="{{ route('products.index') }}" class="inline-block bg-[#6E0F12] text-white px-6 py-3 rounded-full hover:bg-[#520b0d] transition-colors">
        Reset Filters
    </a>
</div>
