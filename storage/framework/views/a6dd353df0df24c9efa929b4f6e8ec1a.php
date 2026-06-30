<?php
    $cartItems = collect($cartItems);

    $initialCartItems = $cartItems->map(function ($item) {
        $imageUrl = asset('images/placeholder.jpg');
        if (!empty($item->product->thumbnail)) {
            $imageUrl = asset('storage/' . $item->product->thumbnail);
        } elseif (!empty($item->product->images) && $item->product->images->count() > 0) {
            $imageUrl = asset('storage/' . $item->product->images->first()->image);
        }

        return [
            'id' => $item->id,
            'product_id' => $item->product_id ?? $item->product->id,
            'name' => $item->product->name,
            'price' => (float) $item->product->price,
            'quantity' => (int) $item->quantity,
            'image' => $imageUrl,
            'slug' => $item->product->slug ?? '',
            'stock' => $item->product->stock ?? 99,
            'category' => $item->product->category?->name ?? 'Jewelry',
        ];
    })->values()->toArray();
    
    $cartCount = $cartItems->sum('quantity');
    $taxAmount = $cartTotal * 0.18;
    $grandTotal = $cartTotal + $taxAmount;
?>

<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

     <?php $__env->slot('title', null, []); ?> Your Cart | Svaraa Jewels <?php $__env->endSlot(); ?>

    
    <div class="bg-[#FFFFF0] py-12 border-b border-[#C8A35D]/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-4xl font-serif text-[#6E0F12]">Shopping Cart</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success') || session('error')): ?>
        <div x-data="{ show: true }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="mb-8">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cartItems->isEmpty()): ?>
            <div class="text-center py-20 bg-gray-50 border border-gray-100 rounded-lg max-w-3xl mx-auto">
                <div class="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center mb-6 shadow-sm border border-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-10 h-10 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-serif text-gray-900 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-8 max-w-md mx-auto">Looks like you haven't added any premium jewelry to your cart yet.</p>
                <a href="<?php echo e(route('products.index')); ?>" class="inline-block bg-[#6E0F12] text-white px-8 py-3.5 uppercase tracking-widest text-sm font-medium hover:bg-[#520b0d] transition-colors shadow-sm">
                    Explore Collection
                </a>
            </div>
        <?php else: ?>
            
            <div class="flex flex-col lg:flex-row gap-12 xl:gap-16">
                
                <div class="w-full lg:w-2/3">
                    <div class="hidden sm:grid grid-cols-12 gap-4 pb-4 border-b border-gray-200 text-xs font-medium text-gray-500 uppercase tracking-widest">
                        <div class="col-span-8">Product</div>
                        <div class="col-span-4 text-right">Total</div>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal60cd16147e26fd5e9b98712a6da7bf43 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal60cd16147e26fd5e9b98712a6da7bf43 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.cart.item','data' => ['item' => $item]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('cart.item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['item' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal60cd16147e26fd5e9b98712a6da7bf43)): ?>
<?php $attributes = $__attributesOriginal60cd16147e26fd5e9b98712a6da7bf43; ?>
<?php unset($__attributesOriginal60cd16147e26fd5e9b98712a6da7bf43); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal60cd16147e26fd5e9b98712a6da7bf43)): ?>
<?php $component = $__componentOriginal60cd16147e26fd5e9b98712a6da7bf43; ?>
<?php unset($__componentOriginal60cd16147e26fd5e9b98712a6da7bf43); ?>
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>

                
                <div class="w-full lg:w-1/3">
                    <?php if (isset($component)) { $__componentOriginala43fe0927865d6b5b9f57d85816152d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala43fe0927865d6b5b9f57d85816152d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.cart.summary','data' => ['subtotal' => $cartTotal,'tax' => $taxAmount,'total' => $grandTotal,'count' => $cartCount]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('cart.summary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['subtotal' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cartTotal),'tax' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($taxAmount),'total' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($grandTotal),'count' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($cartCount)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala43fe0927865d6b5b9f57d85816152d0)): ?>
<?php $attributes = $__attributesOriginala43fe0927865d6b5b9f57d85816152d0; ?>
<?php unset($__attributesOriginala43fe0927865d6b5b9f57d85816152d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala43fe0927865d6b5b9f57d85816152d0)): ?>
<?php $component = $__componentOriginala43fe0927865d6b5b9f57d85816152d0; ?>
<?php unset($__componentOriginala43fe0927865d6b5b9f57d85816152d0); ?>
<?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$cartItems->isEmpty()): ?>
    <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur border-t border-gray-200 shadow-lg z-40">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div>
                <span class="text-xs text-gray-500 uppercase tracking-widest">Total</span>
                <p class="text-lg font-medium text-[#6E0F12]">&#8377;<?php echo e(number_format($grandTotal, 2)); ?></p>
            </div>
            <a href="<?php echo e(route('checkout.index')); ?>" class="bg-[#C8A35D] text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-[#b8934d] transition-colors">
                Checkout
            </a>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/pages/cart/index.blade.php ENDPATH**/ ?>