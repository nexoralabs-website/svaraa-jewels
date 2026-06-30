<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['product']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['product']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="group relative bg-white border border-[#E8DCCB]/50 rounded-xl
            transition-all duration-300 hover:shadow-lg hover:border-[#C8A35D]/40
            flex flex-col h-full overflow-hidden"
     x-data="{ quantity: 1 }">

    
    <div class="relative aspect-square overflow-hidden bg-[#FDFBF7]">
        <a href="<?php echo e(route('products.show', $product->slug)); ?>" class="block w-full h-full">
            <img src="<?php echo e($product->thumbnail_url); ?>"
                 alt="<?php echo e($product->name); ?>"
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                 loading="lazy">
        </a>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->stock <= 0): ?>
        <div class="absolute top-2 left-2">
            <span class="bg-gray-800/80 text-white text-[10px] font-medium uppercase tracking-wider px-2 py-0.5 rounded-full">
                Out of Stock
            </span>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="absolute top-2 right-2">
            <form action="<?php echo e(route('wishlist.add')); ?>" method="POST" class="inline-block">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
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

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->stock > 0): ?>
        <button type="button"
                @click="$store.cart.add(<?php echo e($product->id); ?>, quantity)"
                class="absolute bottom-3 left-3 right-3 bg-[#2E1A12]/90 backdrop-blur-sm
                       text-white py-2 rounded-full text-xs font-medium uppercase tracking-wider
                       opacity-0 group-hover:opacity-100 translate-y-1 group-hover:translate-y-0
                       transition-all duration-300 hover:bg-[#1a0e09]">
            Quick Add to Cart
        </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="p-4 flex flex-col flex-grow">
        <p class="text-[10px] tracking-widest text-[#C8A35D] uppercase mb-1.5 font-medium">
            <?php echo e($product->category->name ?? 'Jewellery'); ?>

        </p>

        <h3 class="text-sm font-serif text-[#2E1A12] leading-tight mb-2 line-clamp-2
                   group-hover:text-[#6E0F12] transition-colors">
            <a href="<?php echo e(route('products.show', $product->slug)); ?>"><?php echo e($product->name); ?></a>
        </h3>

        
        <?php
            $avg   = $product->average_rating;   // uses withAvg from ProductService
            $count = $product->review_count;      // uses withCount from ProductService
        ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
        <div class="flex items-center gap-1.5 mb-3" aria-label="<?php echo e(number_format($avg, 1)); ?> out of 5 stars">
            <div class="flex gap-0.5">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($s = 1; $s <= 5; $s++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <svg class="w-3 h-3 <?php echo e($s <= round($avg) ? 'text-[#C8A35D]' : 'text-gray-200'); ?>"
                     fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <span class="text-[10px] text-gray-400">(<?php echo e($count); ?>)</span>
        </div>
        <?php else: ?>
        <div class="mb-3 h-5"></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mt-auto flex items-center justify-between">
            <p class="text-base font-semibold text-[#6E0F12]">
                ₹<?php echo e(number_format($product->price, 2)); ?>

            </p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->stock > 0 && $product->stock <= 5): ?>
            <span class="text-[10px] text-orange-500 font-medium">Only <?php echo e($product->stock); ?> left</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/components/shop/product-card.blade.php ENDPATH**/ ?>