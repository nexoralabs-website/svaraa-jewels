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

     <?php $__env->slot('title', null, []); ?> <?php echo e($product->name); ?> | Svaraa Jewels <?php $__env->endSlot(); ?>
     <?php $__env->slot('seo', null, []); ?> 
        <?php if (isset($component)) { $__componentOriginal84f9df3f620371229981225e7ba608d7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal84f9df3f620371229981225e7ba608d7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.seo-meta','data' => ['title' => $product->seo_title,'description' => $product->seo_description,'canonical' => route('products.show', $product->slug),'ogImage' => $product->og_image ? asset('storage/' . $product->og_image) : null,'schema' => ['@type' => 'Product', 'name' => $product->name, 'aggregateRating' => $product->review_count > 0 ? ['@type' => 'AggregateRating', 'ratingValue' => $product->average_rating, 'reviewCount' => $product->review_count] : null, 'offers' => ['@type' => 'Offer', 'price' => $product->price, 'priceCurrency' => 'INR', 'availability' => $product->stock > 0 ? 'InStock' : 'OutOfStock']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('seo-meta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->seo_title),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->seo_description),'canonical' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('products.show', $product->slug)),'og-image' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->og_image ? asset('storage/' . $product->og_image) : null),'schema' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['@type' => 'Product', 'name' => $product->name, 'aggregateRating' => $product->review_count > 0 ? ['@type' => 'AggregateRating', 'ratingValue' => $product->average_rating, 'reviewCount' => $product->review_count] : null, 'offers' => ['@type' => 'Offer', 'price' => $product->price, 'priceCurrency' => 'INR', 'availability' => $product->stock > 0 ? 'InStock' : 'OutOfStock']])]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal84f9df3f620371229981225e7ba608d7)): ?>
<?php $attributes = $__attributesOriginal84f9df3f620371229981225e7ba608d7; ?>
<?php unset($__attributesOriginal84f9df3f620371229981225e7ba608d7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal84f9df3f620371229981225e7ba608d7)): ?>
<?php $component = $__componentOriginal84f9df3f620371229981225e7ba608d7; ?>
<?php unset($__componentOriginal84f9df3f620371229981225e7ba608d7); ?>
<?php endif; ?>
     <?php $__env->endSlot(); ?>

    
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="<?php echo e(route('home')); ?>" class="hover:text-[#6E0F12] transition-colors">Home</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <a href="<?php echo e(route('products.index')); ?>" class="hover:text-[#6E0F12] transition-colors">Shop</a>
                        </div>
                    </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->category): ?>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <a href="<?php echo e(route('categories.show', $product->category->slug)); ?>" class="hover:text-[#6E0F12] transition-colors"><?php echo e($product->category->name); ?></a>
                        </div>
                    </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                            <span class="text-gray-400"><?php echo e($product->name); ?></span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="flex flex-col lg:flex-row gap-12 xl:gap-16">
            <div class="w-full lg:w-1/2">
                <?php if (isset($component)) { $__componentOriginal005514b447bcd9947e21a03d4f167c9b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal005514b447bcd9947e21a03d4f167c9b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.product.gallery','data' => ['product' => $product]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('product.gallery'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal005514b447bcd9947e21a03d4f167c9b)): ?>
<?php $attributes = $__attributesOriginal005514b447bcd9947e21a03d4f167c9b; ?>
<?php unset($__attributesOriginal005514b447bcd9947e21a03d4f167c9b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal005514b447bcd9947e21a03d4f167c9b)): ?>
<?php $component = $__componentOriginal005514b447bcd9947e21a03d4f167c9b; ?>
<?php unset($__componentOriginal005514b447bcd9947e21a03d4f167c9b); ?>
<?php endif; ?>
            </div>
            <div class="w-full lg:w-1/2">
                <?php if (isset($component)) { $__componentOriginal1252f98e67dd613c084f3bd772813a81 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1252f98e67dd613c084f3bd772813a81 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.product.details','data' => ['product' => $product]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('product.details'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1252f98e67dd613c084f3bd772813a81)): ?>
<?php $attributes = $__attributesOriginal1252f98e67dd613c084f3bd772813a81; ?>
<?php unset($__attributesOriginal1252f98e67dd613c084f3bd772813a81); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1252f98e67dd613c084f3bd772813a81)): ?>
<?php $component = $__componentOriginal1252f98e67dd613c084f3bd772813a81; ?>
<?php unset($__componentOriginal1252f98e67dd613c084f3bd772813a81); ?>
<?php endif; ?>
            </div>
        </div>

        
        <?php if (isset($component)) { $__componentOriginal591a712a57de2e16bd8d043bdb2a16d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal591a712a57de2e16bd8d043bdb2a16d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.product.reviews','data' => ['product' => $product,'reviews' => $reviews,'distribution' => $distribution,'canReview' => $canReview,'hasReviewed' => $hasReviewed]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('product.reviews'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product),'reviews' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($reviews),'distribution' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($distribution),'canReview' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($canReview),'hasReviewed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasReviewed)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal591a712a57de2e16bd8d043bdb2a16d5)): ?>
<?php $attributes = $__attributesOriginal591a712a57de2e16bd8d043bdb2a16d5; ?>
<?php unset($__attributesOriginal591a712a57de2e16bd8d043bdb2a16d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal591a712a57de2e16bd8d043bdb2a16d5)): ?>
<?php $component = $__componentOriginal591a712a57de2e16bd8d043bdb2a16d5; ?>
<?php unset($__componentOriginal591a712a57de2e16bd8d043bdb2a16d5); ?>
<?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($relatedProducts && $relatedProducts->count() > 0): ?>
    <div class="bg-gray-50 border-t border-gray-100 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-serif text-center text-gray-900 mb-10">You May Also Like</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $relatedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedProduct): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal789b6d25aea4eb0119c589010e2d673d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal789b6d25aea4eb0119c589010e2d673d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shop.product-card','data' => ['product' => $relatedProduct]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shop.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($relatedProduct)]); ?>
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
<?php endif; ?><?php /**PATH /var/www/html/resources/views/pages/product/show.blade.php ENDPATH**/ ?>