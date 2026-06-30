
<div
    x-data="{ open: false }"
    @open-wishlist.window="open = true"
    @keydown.escape.window="open = false"
    class="relative z-50"
>
    
    <div
        x-show="open"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm"
        style="display: none;"
    ></div>

    
    <div
        x-show="open"
        x-transition:enter="transition-transform ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col"
        style="display: none;"
    >
        
        <div class="flex items-center justify-between px-6 py-5 border-b border-[#C8A35D]/20">
            <h2 class="text-xl font-serif text-[#2E1A12] tracking-wide">
                My Wishlist
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($wishlistItems ?? []) > 0): ?>
                    <span class="text-sm font-normal text-gray-500">(<?php echo e(count($wishlistItems ?? [])); ?>)</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </h2>
            <button @click="open = false" class="p-1 text-gray-400 hover:text-[#6E0F12] transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        
        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $wishlistItems ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="flex gap-4 pb-6 border-b border-gray-100">
                    
                    <div class="w-20 h-20 flex-shrink-0 bg-gray-50 rounded-lg overflow-hidden">
                        <a href="<?php echo e(route('products.show', $item->product->slug)); ?>">
                            <img
                                src="<?php echo e(optional($item->product->images->first())->image ? asset('storage/' . optional($item->product->images->first())->image) : asset('images/placeholder.jpg')); ?>"
                                alt="<?php echo e($item->product->name); ?>"
                                class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                            >
                        </a>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-[#2E1A12] truncate font-serif">
                            <a href="<?php echo e(route('products.show', $item->product->slug)); ?>" class="hover:text-[#6E0F12] transition-colors">
                                <?php echo e($item->product->name); ?>

                            </a>
                        </h4>
                        <p class="text-xs text-gray-500 mt-0.5"><?php echo e($item->product->category->name ?? 'Jewelry'); ?></p>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($item->product->discount_price) && $item->product->discount_price < $item->product->price): ?>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-sm font-medium text-[#C8A35D]">₹<?php echo e(number_format($item->product->discount_price, 2)); ?></span>
                                <span class="text-xs text-gray-400 line-through">₹<?php echo e(number_format($item->product->price, 2)); ?></span>
                            </div>
                        <?php else: ?>
                            <p class="text-sm font-medium text-[#C8A35D] mt-1">₹<?php echo e(number_format($item->product->price, 2)); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <form action="<?php echo e(route('cart.add')); ?>" method="POST" class="mt-2">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="product_id" value="<?php echo e($item->product->id); ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="text-xs font-medium text-[#6E0F12] hover:text-[#C8A35D] transition-colors underline underline-offset-2">
                                Move to Cart
                            </button>
                        </form>
                    </div>
                    
                    <form action="<?php echo e(route('wishlist.remove', $item->product->id)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="p-1 text-gray-300 hover:text-red-500 transition-colors" title="Remove from wishlist">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </form>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                
                <div class="flex flex-col items-center justify-center h-full text-center py-16">
                    <svg class="w-20 h-20 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <h3 class="text-lg font-serif text-[#2E1A12] mb-2">Your wishlist is empty</h3>
                    <p class="text-sm text-gray-500 mb-6">Save your favorite pieces for later.</p>
                    <a href="<?php echo e(route('products.index')); ?>" @click="open = false" class="inline-block bg-[#6E0F12] text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-[#520b0d] transition-colors">
                        Explore Collection
                    </a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($wishlistItems ?? []) > 0): ?>
        <div class="border-t border-[#C8A35D]/20 px-6 py-5 bg-[#FDFBF7]">
            <a href="<?php echo e(route('wishlist.index')); ?>" class="block w-full bg-[#6E0F12] text-white text-center py-3 rounded-full font-medium hover:bg-[#520b0d] transition-colors">
                View Full Wishlist
            </a>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/components/wishlist/sidebar.blade.php ENDPATH**/ ?>