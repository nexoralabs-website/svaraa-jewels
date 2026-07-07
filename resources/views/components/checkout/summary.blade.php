@props(['items', 'total'])

<div class="bg-gray-50 p-8 border border-gray-100 sticky top-8">
    <h3 class="text-lg font-serif text-gray-900 mb-6">Order Summary</h3>
    
    {{-- Items List --}}
    <div class="space-y-4 mb-6 pb-6 border-b border-gray-200 max-h-[40vh] overflow-y-auto pr-2">
        @foreach($items as $item)
            <div class="flex gap-4">
                <div class="w-16 h-16 bg-white border border-gray-100 flex-shrink-0">
                    <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 flex justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 line-clamp-1">{{ $item->product->name }}</h4>
                        <p class="text-xs text-gray-500 mt-1">Qty: {{ $item->quantity }}</p>
                    </div>
                    <p class="text-sm font-medium text-[#C8A35D]">₹{{ number_format($item->product->price * $item->quantity, 2) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Totals --}}
    <div class="space-y-4 mb-6 pb-6 border-b border-gray-200">
        <div class="flex justify-between text-sm text-gray-600">
            <span>Subtotal</span>
            <span>₹{{ number_format($total, 2) }}</span>
        </div>
        <div class="flex justify-between text-sm text-gray-600">
            <span>Shipping</span>
            <span class="text-green-600">Free</span>
        </div>
        <div class="flex justify-between text-sm text-gray-600">
            <span>Taxes</span>
            <span>Calculated automatically</span>
        </div>
    </div>
    
    <div class="flex justify-between items-center mb-8">
        <span class="text-base font-medium text-gray-900">Total</span>
        <span class="text-2xl font-medium text-[#6E0F12]">₹{{ number_format($total, 2) }}</span>
    </div>

    <button type="submit" class="w-full bg-[#6E0F12] text-white text-center px-8 py-4 uppercase tracking-widest text-sm font-medium hover:bg-[#520b0d] transition-colors shadow-sm flex items-center justify-center gap-2">
        Place Order
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
        </svg>
    </button>
</div>
