@props(['product'])

@php
    $images = collect([]);
    $images->push($product->thumbnail_url);
    if ($product->images && $product->images->count()) {
        foreach ($product->images as $img) {
            if (!empty($img->image)) {
                $images->push($img->image_url);
            }
        }
    }
    if ($images->isEmpty()) {
        $images->push(asset('images/placeholders/product-coming-soon.svg'));
    }
@endphp

<div x-data="imageGallery({{ $images->toJson() }})" class="flex flex-col-reverse lg:flex-row gap-4">
    {{-- Thumbnails Slider (Vertical on desktop, horizontal on mobile) --}}
    <div class="flex lg:flex-col gap-4 overflow-x-auto lg:overflow-y-auto w-full lg:w-24 flex-shrink-0 hide-scrollbar pb-2 lg:pb-0">
        <template x-for="(image, index) in images" :key="index">
            <button 
                @click="activeImage = index"
                class="relative aspect-square w-20 lg:w-full flex-shrink-0 border-2 transition-all duration-200 overflow-hidden"
                :class="activeImage === index ? 'border-[#6E0F12]' : 'border-transparent hover:border-[#C8A35D]'"
            >
                <img :src="image" :alt="'Thumbnail ' + index" class="w-full h-full object-cover object-center bg-gray-50">
                <div 
                    class="absolute inset-0 bg-white/40 transition-opacity"
                    :class="activeImage === index ? 'opacity-0' : 'opacity-100 hover:opacity-0'"
                ></div>
            </button>
        </template>
    </div>

    {{-- Main Image with Zoom --}}
    <div class="relative w-full aspect-square lg:aspect-[4/5] bg-gray-50 overflow-hidden cursor-crosshair border border-gray-100 group"
         @mousemove="zoomImage"
         @mouseleave="resetZoom"
    >
        <img 
            :src="images[activeImage]" 
            :alt="'{{ $product->name }}'" 
            class="w-full h-full object-cover object-center transition-transform duration-200 ease-out"
            :style="`transform-origin: ${zoomOriginX}% ${zoomOriginY}%; transform: scale(${isZoomed ? 2 : 1})`"
            @mouseenter="isZoomed = true"
        >
        
        {{-- Wishlist Button overlay --}}
        <button class="absolute top-4 right-4 p-3 bg-white/80 backdrop-blur rounded-full text-gray-400 hover:text-[#6E0F12] transition-colors shadow-sm z-10">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
            </svg>
        </button>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('imageGallery', (images) => ({
            images: images,
            activeImage: 0,
            isZoomed: false,
            zoomOriginX: 50,
            zoomOriginY: 50,
            
            zoomImage(e) {
                if (!this.isZoomed) return;
                
                const rect = e.target.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                
                this.zoomOriginX = x;
                this.zoomOriginY = y;
            },
            
            resetZoom() {
                this.isZoomed = false;
                this.zoomOriginX = 50;
                this.zoomOriginY = 50;
            }
        }))
    })
</script>

<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
