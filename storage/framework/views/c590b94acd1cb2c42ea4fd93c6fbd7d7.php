<nav x-data="{ mobileOpen: false, cartOpen: false, searchOpen: false }" class="bg-white border-b border-gray-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            
            <div class="shrink-0 flex items-center">
                <a href="<?php echo e(route('products.index')); ?>" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-[#6E0F12] rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                    </div>
                    <span class="text-lg font-serif font-bold text-[#2E1A12] tracking-wide hidden sm:block">Svaraa Jewels</span>
                </a>
            </div>

            
            <div class="hidden md:flex items-center space-x-10">
                <a href="<?php echo e(route('products.index')); ?>" class="text-sm font-medium text-gray-700 hover:text-[#6E0F12] transition-colors">
                    Earrings
                </a>
            </div>

            
            <div class="flex items-center gap-1 sm:gap-2">
                
                <button @click="searchOpen = !searchOpen" class="p-2 text-gray-500 hover:text-[#6E0F12] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </button>

                
                <a href="<?php echo e(route('wishlist.index')); ?>" class="p-2 text-gray-500 hover:text-[#6E0F12] transition-colors relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($wishlistCount > 0): ?>
                    <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-[#C8A35D] text-white text-[10px] font-bold rounded-full flex items-center justify-center"><?php echo e($wishlistCount); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </a>

                
                <button @click="$dispatch('open-cart')" class="p-2 text-gray-500 hover:text-[#6E0F12] transition-colors relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                    <span 
                        x-show="<?php echo e($cartCount); ?> > 0"
                        x-transition
                        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-[#6E0F12] text-white text-[10px] font-bold rounded-full flex items-center justify-center px-0.5"
                        x-text="<?php echo e($cartCount); ?>"
                    ></span>
                </button>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <div class="hidden sm:block ml-2 pl-2 border-l border-gray-200">
                    <a href="<?php echo e(route('dashboard')); ?>" class="text-sm font-medium text-gray-700 hover:text-[#6E0F12] transition-colors">
                        Account
                    </a>
                </div>
                <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="hidden sm:block text-sm font-medium text-gray-700 hover:text-[#6E0F12] transition-colors ml-2 pl-2 border-l border-gray-200">
                    Sign In
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <button @click="mobileOpen = !mobileOpen" class="-me-2 flex items-center justify-center p-2 md:hidden text-gray-500">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileOpen, 'inline-flex': !mobileOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !mobileOpen, 'inline-flex': mobileOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    
    <div :class="{'block': mobileOpen, 'hidden': !mobileOpen}" class="hidden md:hidden border-t border-gray-100 bg-white">
        <div class="px-4 pt-2 pb-4 space-y-2">
            <a href="<?php echo e(route('products.index')); ?>" class="block px-3 py-2 text-sm font-medium text-gray-700 hover:text-[#6E0F12] hover:bg-gray-50 rounded-md">Earrings</a>
        </div>
    </div>

    
    <div x-show="searchOpen" x-transition class="md:hidden border-t border-gray-100 bg-white px-4 py-3">
        <form action="<?php echo e(route('products.index')); ?>" method="GET">
            <input type="text" name="search" placeholder="Search earrings..." class="w-full border border-gray-200 rounded-lg px-4 py-2 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
        </form>
    </div>

    
    <?php if (isset($component)) { $__componentOriginal3ba6d5857760cef0e67a54ddc4423557 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3ba6d5857760cef0e67a54ddc4423557 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.cart.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('cart.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3ba6d5857760cef0e67a54ddc4423557)): ?>
<?php $attributes = $__attributesOriginal3ba6d5857760cef0e67a54ddc4423557; ?>
<?php unset($__attributesOriginal3ba6d5857760cef0e67a54ddc4423557); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3ba6d5857760cef0e67a54ddc4423557)): ?>
<?php $component = $__componentOriginal3ba6d5857760cef0e67a54ddc4423557; ?>
<?php unset($__componentOriginal3ba6d5857760cef0e67a54ddc4423557); ?>
<?php endif; ?>
</nav>
<?php /**PATH /var/www/html/resources/views/layouts/navigation.blade.php ENDPATH**/ ?>