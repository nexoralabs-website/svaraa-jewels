@props(['item'])

<div class="flex flex-col sm:flex-row items-start sm:items-center py-6 border-b border-gray-100 gap-6">
    {{-- Image --}}
    <div class="w-24 h-24 bg-gray-50 flex-shrink-0 border border-gray-100">
        <a href="{{ route('products.show', $item->product->slug ?? '#') }}">
            @php
                $imagePath = asset('images/placeholder.jpg');
                if (!empty($item->product->thumbnail)) {
                    $imagePath = asset('storage/' . $item->product->thumbnail);
                } elseif (!empty($item->product->images) && $item->product->images->count() > 0) {
                    $imagePath = asset('storage/' . $item->product->images->first()->image);
                }
            @endphp
            <img 
                src="{{ $imagePath }}" 
                alt="{{ $item->product->name }}" 
                class="w-full h-full object-cover object-center"
                loading="lazy"
            >
        </a>
    </div>

    {{-- Details --}}
    <div class="flex-1 min-w-0">
        <div class="flex flex-col sm:flex-row justify-between gap-2">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-widest mb-1">
                    {{ $item->product->category->name ?? 'Jewelry' }}
                </p>
                <h3 class="text-lg font-serif text-gray-900 truncate">
                    <a href="{{ route('products.show', $item->product->slug ?? '#') }}" class="hover:text-[#6E0F12] transition-colors">
                        {{ $item->product->name }}
                    </a>
                </h3>
            </div>
            <p class="text-lg font-medium text-[#C8A35D] sm:text-right">
                &#8377;{{ number_format($item->product->price, 2) }}
            </p>
        </div>

        <div class="mt-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            {{-- Quantity Controls --}}
            <div 
                class="flex items-center"
                x-data="{
                    quantity: {{ $item->quantity }},
                    stock: {{ $item->product->stock ?? 99 }},
                    updating: false,
                    async changeQty(delta) {
                        const newQty = this.quantity + delta;
                        if (newQty < 1 || newQty > this.stock) return;
                        this.updating = true;
                        this.quantity = newQty;
                        try {
                            await $store.cart.update({{ $item->product->id }}, newQty);
                        } catch (e) {
                            this.quantity -= delta;
                        }
                        this.updating = false;
                    },
                    async setQty() {
                        if (this.quantity < 1) this.quantity = 1;
                        if (this.quantity > this.stock) this.quantity = this.stock;
                        this.updating = true;
                        try {
                            await $store.cart.update({{ $item->product->id }}, this.quantity);
                        } catch (e) {
                            this.quantity = {{ $item->quantity }};
                        }
                        this.updating = false;
                        this.$refs.rawQty.value = this.quantity;
                    }
                }"
            >
                <div class="flex items-center border border-gray-200 bg-white rounded-sm">
                    <button 
                        type="button" 
                        @click="changeQty(-1)"
                        :disabled="quantity <= 1 || updating"
                        class="px-3 py-1.5 text-gray-500 hover:text-[#6E0F12] hover:bg-gray-50 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                        </svg>
                    </button>
                    <input 
                        type="number" 
                        x-ref="rawQty"
                        x-model.number="quantity"
                        @blur="setQty()"
                        @keydown.enter.prevent="setQty()"
                        min="1" 
                        :max="stock"
                        class="w-12 text-center border-x border-gray-200 py-1.5 focus:ring-0 outline-none text-sm -moz-appearance: textfield;"
                    >
                    <button 
                        type="button" 
                        @click="changeQty(1)"
                        :disabled="quantity >= stock || updating"
                        class="px-3 py-1.5 text-gray-500 hover:text-[#6E0F12] hover:bg-gray-50 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </button>
                </div>
                <svg x-show="updating" class="animate-spin w-4 h-4 ml-2 text-[#C8A35D]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>

            {{-- Subtotal & Remove --}}
            <div class="flex items-center gap-6 w-full sm:w-auto justify-between sm:justify-end">
                <div class="text-sm">
                    <span class="text-gray-500 mr-2 hidden sm:inline">Subtotal:</span>
                    <span class="font-medium text-gray-900">&#8377;{{ number_format($item->product->price * $item->quantity, 2) }}</span>
                </div>
                
                <button 
                    type="button" 
                    @click="$store.cart.remove({{ $item->product->id }})"
                    class="text-sm text-gray-400 hover:text-red-600 transition-colors flex items-center gap-1" 
                    title="Remove Item"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
