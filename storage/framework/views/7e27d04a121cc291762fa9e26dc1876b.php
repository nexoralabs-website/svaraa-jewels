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

     <?php $__env->slot('title', null, []); ?> Our Earrings Collection <?php $__env->endSlot(); ?>

    <div class="container mx-auto px-4 py-12">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-[#2E1A12] mb-4" style="font-family: 'Playfair Display', serif;">Our Exquisite Earrings</h1>
            <p class="text-[#7B6755] max-w-2xl mx-auto">Discover handcrafted earrings that blend timeless elegance with modern sophistication</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <div class="lg:w-64 flex-shrink-0">
                <div class="card-premium p-6 sticky top-28">
            <!-- Search -->
            <form action="<?php echo e(route('products.index')); ?>" method="GET" class="mb-6">
                <div class="relative">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search jewelry..." class="w-full pl-10 pr-4 py-2 border border-[#E8DCCB] rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-[#C8A35D]" />
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-[#7B6755]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('category')): ?>
                    <input type="hidden" name="category" value="<?php echo e(request('category')); ?>" />
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('sort')): ?>
                    <input type="hidden" name="sort" value="<?php echo e(request('sort')); ?>" />
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </form>

            <!-- Categories — earrings only, single entry -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-[#2E1A12] mb-4" style="font-family: 'Playfair Display', serif;">Category</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="<?php echo e(route('products.index', ['search' => request('search'), 'sort' => request('sort')])); ?>" class="text-[#6E0F12] font-medium transition-colors">All Earrings</a>
                    </li>
                </ul>
            </div>

            <!-- Sort -->
            <div>
                <h3 class="text-lg font-semibold text-[#2E1A12] mb-4" style="font-family: 'Playfair Display', serif;">Sort By</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="<?php echo e(route('products.index', ['category' => request('category'), 'search' => request('search'), 'sort' => 'newest'])); ?>" class="<?php echo e(request('sort', 'newest') === 'newest' ? 'text-[#6E0F12] font-medium' : 'text-[#7B6755] hover:text-[#6E0F12]'); ?> transition-colors">Newest Arrivals</a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('products.index', ['category' => request('category'), 'search' => request('search'), 'sort' => 'price-low'])); ?>" class="<?php echo e(request('sort') === 'price-low' ? 'text-[#6E0F12] font-medium' : 'text-[#7B6755] hover:text-[#6E0F12]'); ?> transition-colors">Price: Low to High</a>
                    </li>
                    <li>
                        <a href="<?php echo e(route('products.index', ['category' => request('category'), 'search' => request('search'), 'sort' => 'price-high'])); ?>" class="<?php echo e(request('sort') === 'price-high' ? 'text-[#6E0F12] font-medium' : 'text-[#7B6755] hover:text-[#6E0F12]'); ?> transition-colors">Price: High to Low</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

            <!-- Product Grid -->
            <div class="flex-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($products->count() > 0): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php if (isset($component)) { $__componentOriginalf292d5a086094ad3d5b286d01509943e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf292d5a086094ad3d5b286d01509943e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.products.card','data' => ['product' => $product]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('products.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf292d5a086094ad3d5b286d01509943e)): ?>
<?php $attributes = $__attributesOriginalf292d5a086094ad3d5b286d01509943e; ?>
<?php unset($__attributesOriginalf292d5a086094ad3d5b286d01509943e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf292d5a086094ad3d5b286d01509943e)): ?>
<?php $component = $__componentOriginalf292d5a086094ad3d5b286d01509943e; ?>
<?php unset($__componentOriginalf292d5a086094ad3d5b286d01509943e); ?>
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-12">
                        <?php echo e($products->appends(request()->except('page'))->links()); ?>

                    </div>
                <?php else: ?>
                    <div class="text-center py-20">
                        <h3 class="text-2xl font-bold text-[#2E1A12] mb-4" style="font-family: 'Playfair Display', serif;">No earrings found</h3>
                        <p class="text-[#7B6755] mb-8">We couldn't find any earrings matching your criteria.</p>
                        <a href="<?php echo e(route('products.index')); ?>" class="btn-primary inline-block">Browse All Earrings</a>
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
<?php /**PATH /var/www/html/resources/views/pages/products/index.blade.php ENDPATH**/ ?>