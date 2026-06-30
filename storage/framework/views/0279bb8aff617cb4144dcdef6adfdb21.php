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

     <?php $__env->slot('title', null, []); ?> <?php echo e($category->name); ?> Jewelry | Svaraa Jewels <?php $__env->endSlot(); ?>

    
    <div class="relative bg-gray-900 h-80 flex items-center justify-center">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->image): ?>
            <img src="<?php echo e(asset('storage/' . $category->image)); ?>" alt="<?php echo e($category->name); ?>" class="absolute inset-0 w-full h-full object-cover opacity-40">
        <?php else: ?>
            <div class="absolute inset-0 w-full h-full bg-[#6E0F12] opacity-80"></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <div class="relative z-10 text-center px-4">
            <h1 class="text-4xl md:text-6xl font-serif text-white mb-4 drop-shadow-md"><?php echo e($category->name); ?></h1>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category->description): ?>
                <p class="text-white/90 max-w-2xl mx-auto text-lg drop-shadow"><?php echo e($category->description); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col lg:flex-row gap-12">
            
            <div class="w-full lg:w-1/4 flex-shrink-0">
                <?php if (isset($component)) { $__componentOriginal893d81a32242bbd2dca1042c3240badd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal893d81a32242bbd2dca1042c3240badd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shop.filter-sidebar','data' => ['categories' => $categories,'filters' => $filters,'isCategoryPage' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shop.filter-sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['categories' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($categories),'filters' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters),'isCategoryPage' => true]); ?>
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
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($products->count() > 0): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
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
                        <p class="text-gray-500 mb-6">There are currently no products in this category.</p>
                        <a href="<?php echo e(route('products.index')); ?>" class="inline-block border border-[#6E0F12] text-[#6E0F12] px-6 py-2.5 hover:bg-[#6E0F12] hover:text-white transition-colors font-medium">
                            View All Earrings
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
<?php /**PATH /var/www/html/resources/views/pages/shop/category.blade.php ENDPATH**/ ?>