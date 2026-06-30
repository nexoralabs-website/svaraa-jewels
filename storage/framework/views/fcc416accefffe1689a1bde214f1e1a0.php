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

     <?php $__env->slot('title', null, []); ?> Shop Earrings | Svaraa Jewels <?php $__env->endSlot(); ?>

    
    <div class="bg-[#FFFFF0] py-16 border-b border-[#C8A35D]/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-serif text-[#6E0F12] mb-4">Shop Earrings</h1>
            <p class="text-gray-600 max-w-2xl mx-auto text-lg">Discover our premium range of meticulously crafted earrings.</p>
        </div>
    </div>

    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col lg:flex-row gap-12">
            
            <div class="w-full lg:w-1/4 flex-shrink-0">
                <?php if (isset($component)) { $__componentOriginal893d81a32242bbd2dca1042c3240badd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal893d81a32242bbd2dca1042c3240badd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shop.filter-sidebar','data' => ['categories' => $categories,'filters' => $filters]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shop.filter-sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories),'filters' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal893d81a32242bbd2dca1042c3240badd)): ?>
<?php $attributes = $__attributesOriginal893d81a32242bbd2dca1042c3240badd; ?>
<?php unset($__attributesOriginal893d81a32242bbd2dca1042c3240badd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal893d81a32242bbd2dca1042c3240badd)): ?>
<?php $component = $__componentOriginal893d81a32242bbd2dca1042c3240badd; ?>
<?php unset($__componentOriginal893d81a32242bbd2dca1042c3240badd); ?>
<?php endif; ?>
            </div>

            
            <div class="w-full lg:w-3/4">
                
    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 pb-4 border-b border-gray-100 gap-4">
    <p class="text-gray-500 text-sm">
        Showing <?php echo e($products->firstItem() ?? 0); ?> - <?php echo e($products->lastItem() ?? 0); ?> of <?php echo e($products->total()); ?> results
    </p>

    <!-- Active filter badges -->
    <div class="flex flex-wrap gap-2 items-center">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('search')): ?>
            <span class="inline-flex items-center bg-[#E8DCCB] text-[#2E1A12] px-3 py-1 rounded-full text-xs font-medium">
                Search: "<?php echo e(request('search')); ?>"
                <a href="<?php echo e(request()->fullUrlWithQuery(['search' => null])); ?>" class="ml-1 text-gray-500 hover:text-gray-700">&times;</a>
            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('min_price') || request('max_price')): ?>
            <span class="inline-flex items-center bg-[#E8DCCB] text-[#2E1A12] px-3 py-1 rounded-full text-xs font-medium">
                Price: ₹<?php echo e(request('min_price', 0)); ?> - ₹<?php echo e(request('max_price', '∞')); ?>

                <a href="<?php echo e(request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null])); ?>" class="ml-1 text-gray-500 hover:text-gray-700">&times;</a>
            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('in_stock')): ?>
            <span class="inline-flex items-center bg-[#E8DCCB] text-[#2E1A12] px-3 py-1 rounded-full text-xs font-medium">
                In Stock
                <a href="<?php echo e(request()->fullUrlWithQuery(['in_stock' => null])); ?>" class="ml-1 text-gray-500 hover:text-gray-700">&times;</a>
            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->except(['page','sort','search','min_price','max_price','in_stock'])): ?>
            <a href="<?php echo e(route('products.index')); ?>" class="ml-4 text-[#6E0F12] underline hover:text-[#2E1A12]">Clear All Filters</a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="flex items-center gap-2">
        <?php if (isset($component)) { $__componentOriginalc39dee4ba456dfffd002e2ba3dd84968 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc39dee4ba456dfffd002e2ba3dd84968 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shop.sort-dropdown','data' => ['currentSort' => $filters['sort'] ?? 'newest']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shop.sort-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['currentSort' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters['sort'] ?? 'newest')]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc39dee4ba456dfffd002e2ba3dd84968)): ?>
<?php $attributes = $__attributesOriginalc39dee4ba456dfffd002e2ba3dd84968; ?>
<?php unset($__attributesOriginalc39dee4ba456dfffd002e2ba3dd84968); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc39dee4ba456dfffd002e2ba3dd84968)): ?>
<?php $component = $__componentOriginalc39dee4ba456dfffd002e2ba3dd84968; ?>
<?php unset($__componentOriginalc39dee4ba456dfffd002e2ba3dd84968); ?>
<?php endif; ?>
        <!-- Grid/List toggle placeholder -->
        <button type="button" class="p-2 border border-gray-200 rounded hover:bg-[#E8DCCB] transition-colors" title="Grid/List view">
            <svg class="w-5 h-5 text-[#2E1A12]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>
</div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($products->count() > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginal789b6d25aea4eb0119c589010e2d673d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal789b6d25aea4eb0119c589010e2d673d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shop.product-card','data' => ['product' => $product]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shop.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal789b6d25aea4eb0119c589010e2d673d)): ?>
<?php $attributes = $__attributesOriginal789b6d25aea4eb0119c589010e2d673d; ?>
<?php unset($__attributesOriginal789b6d25aea4eb0119c589010e2d673d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal789b6d25aea4eb0119c589010e2d673d)): ?>
<?php $component = $__componentOriginal789b6d25aea4eb0119c589010e2d673d; ?>
<?php unset($__componentOriginal789b6d25aea4eb0119c589010e2d673d); ?>
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>

                    
                    <div class="mt-16 border-t border-gray-100 pt-8">
                        <?php echo e($products->withQueryString()->links()); ?>

                    </div>
                <?php else: ?>
                    
                    <div class="text-center py-24 bg-gray-50 border border-gray-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 mx-auto text-gray-400 mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <h3 class="text-xl font-serif text-gray-900 mb-2">No products found</h3>
                        <p class="text-gray-500 mb-6">We couldn't find anything matching your current filters.</p>
                        <a href="<?php echo e(route('products.index')); ?>" class="inline-block border border-[#6E0F12] text-[#6E0F12] px-6 py-2.5 hover:bg-[#6E0F12] hover:text-white transition-colors font-medium">
                            Clear Filters
                        </a>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
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
<?php /**PATH /var/www/html/resources/views/pages/shop/index.blade.php ENDPATH**/ ?>