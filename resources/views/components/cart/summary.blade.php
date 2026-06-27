@props(['subtotal', 'tax', 'total', 'count'])

<div class="bg-[#FDFBF7] rounded-xl border border-[#E8DCCB] p-6 sticky top-28">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-serif text-[#2E1A12]">Order Summary</h3>
        <span class="text-sm text-gray-400">{{ $count }} item{{ $count !== 1 ? 's' : '' }}</span>
    </div>

    <div class="space-y-3 mb-6 pb-6 border-b border-[#E8DCCB]">
        <div class="flex justify-between text-sm text-gray-600">
            <span>Subtotal</span>
            <span>&#8377;{{ number_format($subtotal, 2) }}</span>
        </div>
        <div class="flex justify-between text-sm text-gray-600">
            <span>Shipping</span>
            @if($subtotal > 50000)
                <span class="text-green-600 font-medium">FREE</span>
            @else
                <span>&#8377;500.00</span>
            @endif
        </div>
        @if($tax > 0)
        <div class="flex justify-between text-sm text-gray-600">
            <span>GST (18%)</span>
            <span>&#8377;{{ number_format($tax, 2) }}</span>
        </div>
        @endif
    </div>

    <div class="flex justify-between items-center mb-8">
        <span class="text-base font-medium text-[#2E1A12]">Estimated Total</span>
        <span class="text-2xl font-serif font-medium text-[#6E0F12]">&#8377;{{ number_format($total, 2) }}</span>
    </div>

    <div class="space-y-3">
        <a href="{{ route('checkout.index') }}"
           class="block w-full bg-[#C8A35D] text-white text-center py-3.5 rounded-full font-medium
                  hover:bg-[#b8934d] transition-colors shadow-sm">
            Proceed to Checkout
        </a>
        <a href="{{ route('products.index') }}"
           class="block w-full border border-[#E8DCCB] text-[#2E1A12] text-center py-3.5 rounded-full
                  text-sm font-medium hover:border-[#C8A35D] hover:text-[#C8A35D] transition-colors">
            Continue Shopping
        </a>
    </div>

    <div class="mt-6 pt-4 border-t border-[#E8DCCB] flex items-center justify-center gap-2 text-xs text-gray-400">
        <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        Secure Checkout
    </div>
</div>
