@props(['product'])

<div class="group relative bg-white border border-[#E8DCCB]/50 rounded-xl
            transition-all duration-300 hover:shadow-lg hover:border-[#C8A35D]/40
            flex flex-col h-full overflow-hidden"
     x-data="{ quantity: 1 }">

    {{-- Image --}}
    <div class="relative aspect-square overflow-hidden bg-[#FDFBF7]">
        <a href="{{ route('products.show', $product->slug) }}" class="block w-full h-full">
            <img src="{{ $product->thumbnail_url }}"
                 alt="{{ $product->name }}"
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                 loading="lazy">
        </a>

        {{-- Out of stock badge --}}
        @if($product->stock <= 0)
        <div class="absolute top-2 left-2">
            <span class="bg-gray-800/80 text-white text-[10px] font-medium uppercase tracking-wider px-2 py-0.5 rounded-full">
                Out of Stock
            </span>
        </div>
        @endif

        {{-- Wishlist button --}}
        <div class="absolute top-2 right-2">
            <form action="{{ route('wishlist.add') }}" method="POST" class="inline-block">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit"
                        class="p-2 bg-white/90 backdrop-blur-sm rounded-full text-gray-400
                               hover:text-[#6E0F12] hover:bg-white transition-all duration-200 shadow-sm"
                        title="Add to Wishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.5" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Quick add (only when in stock) --}}
        @if($product->stock > 0)
        <button type="button"
                @click="$store.cart.add({{ $product->id }}, quantity)"
                class="absolute bottom-3 left-3 right-3 bg-[#2E1A12]/90 backdrop-blur-sm
                       text-white py-2 rounded-full text-xs font-medium uppercase tracking-wider
                       opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0
                       transition-all duration-300 hover:bg-[#1a0e09]">
            Quick Add to Cart
        </button>
        @endif
    </div>

    {{-- Info --}}
    <div class="p-4 flex flex-col flex-grow">
        <p class="text-[10px] tracking-widest text-[#C8A35D] uppercase mb-1.5 font-medium">
            {{ $product->category->name ?? 'Jewellery' }}
        </p>

        <h3 class="text-sm font-serif text-[#2E1A12] leading-tight mb-2 line-clamp-2
                   group-hover:text-[#6E0F12] transition-colors">
            <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
        </h3>

        {{-- Real star rating (uses eager-loaded aggregate) --}}
        @php
            $avg   = $product->average_rating;   // uses withAvg from ProductService
            $count = $product->review_count;      // uses withCount from ProductService
        @endphp
        @if($count > 0)
        <div class="flex items-center gap-1.5 mb-3" aria-label="{{ number_format($avg, 1) }} out of 5 stars">
            <div class="flex gap-0.5">
                @for($s = 1; $s <= 5; $s++)
                <svg class="w-3 h-3 {{ $s <= round($avg) ? 'text-[#C8A35D]' : 'text-gray-200' }}"
                     fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                @endfor
            </div>
            <span class="text-[10px] text-gray-400">({{ $count }})</span>
        </div>
        @else
        <div class="mb-3 h-5"></div>{{-- spacer to keep card height uniform --}}
        @endif

        <div class="mt-auto flex items-center justify-between">
            <p class="text-base font-semibold text-[#6E0F12]">
                ₹{{ number_format($product->price, 2) }}
            </p>
            @if($product->stock > 0 && $product->stock <= 5)
            <span class="text-[10px] text-orange-500 font-medium">Only {{ $product->stock }} left</span>
            @endif
        </div>
    </div>
</div>
