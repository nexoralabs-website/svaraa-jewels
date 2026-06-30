<div class="card-premium group product-premium">
    <a href="<?php echo e(route('products.show', $product->slug)); ?>">
        <div class="relative overflow-hidden rounded-t-xl">
            <div class="aspect-square bg-[#E8DCCB] flex items-center justify-center product-image-container">
                <img src="<?php echo e($product->thumbnail_url); ?>" alt="<?php echo e($product->name); ?>" class="w-full h-full object-cover transition-all duration-500 group-hover:scale-110 group-hover:rotate-1" />
            </div>
            <div class="absolute top-3 right-3">
                <button class="p-2 bg-white/90 rounded-full shadow-sm hover:bg-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#2E1A12]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-5">
            <p class="text-sm text-[#7B6755] mb-1"><?php echo e($product->category?->name); ?></p>
            <h3 class="text-lg font-semibold text-[#2E1A12] mb-2" style="font-family: 'Playfair Display', serif;"><?php echo e($product->name); ?></h3>
            <div class="flex items-center justify-between">
                <span class="text-xl font-bold text-[#6E0F12]">₹<?php echo e(number_format($product->price, 0)); ?></span>
                <span class="text-xs text-[#7B6755]"><?php echo e($product->purity); ?></span>
            </div>
            <div class="product-cta mt-4 opacity-0 transform translate-y-2 transition-all duration-300 group-hover:opacity-100 group-hover:translate-y-0">
                <a href="<?php echo e(route('products.show', $product->slug)); ?>" class="block text-center rounded-full bg-[#6E0F12] px-4 py-2 text-sm font-semibold text-white hover:bg-[#4f0b0e] transition-colors">
                    View Product
                </a>
            </div>
        </div>
    </a>
</div>
<?php /**PATH /var/www/html/resources/views/components/products/card.blade.php ENDPATH**/ ?>