{{-- Cart Sidebar - Right slide-in drawer --}}
<div
    @open-cart.window="$store.cart.open = true"
    class="relative z-50"
>
    {{-- Overlay --}}
    <div
        x-show="$store.cart.open"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="$store.cart.open = false"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm"
        style="display: none;"
    ></div>

    {{-- Drawer --}}
    <div
        x-show="$store.cart.open"
        x-transition:enter="transition-transform ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col"
        style="display: none;"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-[#C8A35D]/20">
            <h2 class="text-xl font-serif text-[#2E1A12] tracking-wide">Shopping Cart (<span x-text="$store.cart.count"></span>)</h2>
            <button @click="$store.cart.open = false" class="p-1 text-gray-400 hover:text-[#6E0F12] transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-6">
            <template x-if="$store.cart.items.length === 0">
                {{-- Empty Cart --}}
                <div class="flex flex-col items-center justify-center h-full text-center py-16">
                    <svg class="w-20 h-20 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="text-lg font-serif text-[#2E1A12] mb-2">Your cart is empty</h3>
                    <p class="text-sm text-gray-500 mb-6">Add some beautiful pieces to your collection.</p>
                    <a href="{{ route('products.index') }}" @click="$store.cart.open = false" class="inline-block bg-[#6E0F12] text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-[#520b0d] transition-colors">
                        Start Shopping
                    </a>
                </div>
            </template>

            <template x-for="(item, index) in $store.cart.items" :key="item.id">
                <div class="flex gap-4 pb-6 border-b border-gray-100">
                    {{-- Image --}}
                    <div class="w-20 h-20 flex-shrink-0 bg-gray-50 rounded-lg overflow-hidden">
                        <img
                            x-bind:src="item.image || '/images/placeholder.jpg'"
                            x-bind:alt="item.name"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                    </div>
                    {{-- Details --}}
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-[#2E1A12] truncate font-serif" x-text="item.name"></h4>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="item.category"></p>
                        <p class="text-sm font-medium text-[#C8A35D] mt-1" x-text="'\u20b9' + item.price.toFixed(2)"></p>

                        {{-- Quantity Controls --}}
                         <div class="flex items-center gap-3 mt-2">
                             <button type="button" @click="$store.cart.update(item.product_id, Math.max(1, item.quantity - 1))" class="w-7 h-7 flex items-center justify-center border border-gray-200 rounded text-gray-500 hover:border-[#C8A35D] hover:text-[#C8A35D] transition-colors text-xs">−</button>
                             <span class="text-sm font-medium text-[#2E1A12] w-5 text-center" x-text="item.quantity"></span>
                             <button type="button" @click="$store.cart.update(item.product_id, item.quantity + 1)" class="w-7 h-7 flex items-center justify-center border border-gray-200 rounded text-gray-500 hover:border-[#C8A35D] hover:text-[#C8A35D] transition-colors text-xs">+</button>
                         </div>
                    </div>
                    {{-- Remove --}}
                    <button type="button" @click="$store.cart.remove(item.product_id)" class="p-1 text-gray-300 hover:text-red-500 transition-colors" title="Remove">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- Footer (Coupon + Totals + Buttons) --}}
        <template x-if="$store.cart.items.length > 0">
            <div class="border-t border-[#C8A35D]/20 px-6 py-5 space-y-4 bg-[#FDFBF7]">
                {{-- Coupon --}}
                <div class="flex gap-2">
                    <input type="text" placeholder="Coupon code" class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                    <button class="bg-[#2E1A12] text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-[#1a0f0a] transition-colors">Apply</button>
                </div>

                {{-- Totals --}}
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span x-text="'\u20b9' + $store.cart.total.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Shipping</span>
                        <span class="text-green-600">Free</span>
                    </div>
                    <div class="flex justify-between font-medium text-[#2E1A12] text-base pt-2 border-t border-gray-100">
                        <span>Total</span>
                        <span class="text-[#C8A35D]" x-text="'\u20b9' + $store.cart.total.toFixed(2)"></span>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="space-y-2">
                    <a href="{{ route('cart.index') }}" class="block w-full border border-[#C8A35D] text-[#C8A35D] text-center py-3 rounded-full font-medium hover:bg-[#C8A35D] hover:text-white transition-colors">
                        View Cart
                    </a>
                    <a href="{{ route('checkout.index') }}" class="block w-full bg-[#C8A35D] text-white text-center py-3 rounded-full font-medium hover:bg-[#b8934d] transition-colors">
                        Checkout
                    </a>
                </div>
            </div>
        </template>
    </div>
</div>
