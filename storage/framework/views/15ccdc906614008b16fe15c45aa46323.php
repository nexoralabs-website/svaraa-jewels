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

    
    <div class="bg-[#FDFBF7] border-b border-[#C8A35D]/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex text-sm text-gray-500">
                <a href="<?php echo e(route('home')); ?>" class="hover:text-[#6E0F12] transition-colors">Home</a>
                <span class="mx-2">/</span>
                <a href="<?php echo e(route('products.index')); ?>" class="hover:text-[#6E0F12] transition-colors">Shop</a>
                <span class="mx-2">/</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->category): ?>
                    <a href="<?php echo e(route('categories.show', $product->category->slug)); ?>" class="hover:text-[#6E0F12] transition-colors"><?php echo e($product->category->name); ?></a>
                    <span class="mx-2">/</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <span class="text-[#2E1A12] font-medium"><?php echo e($product->name); ?></span>
            </nav>
        </div>
    </div>

    
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12" x-data="{
        activeImage: 0,
        activeTab: 'description',
        quantity: 1,
        images: [
            <?php $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                '<?php echo e(asset('storage/' . $img->image)); ?>',
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($product->images->isEmpty()): ?>
                '<?php echo e(asset('images/placeholder.jpg')); ?>'
            <?php endif; ?>
        ]
    }">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
            
            <div class="space-y-4">
                
                <div class="aspect-square bg-gray-50 rounded-2xl overflow-hidden border border-[#C8A35D]/10">
                    <img
                        :src="images[activeImage]"
                        alt="<?php echo e($product->name); ?>"
                        class="w-full h-full object-cover object-center transition-all duration-500"
                    >
                </div>
                
                <div class="flex gap-3 overflow-x-auto pb-2">
                    <template x-for="(img, index) in images" :key="index">
                        <button
                            @click="activeImage = index"
                            class="w-20 h-20 flex-shrink-0 rounded-lg overflow-hidden border-2 transition-all duration-200"
                            :class="activeImage === index ? 'border-[#C8A35D] shadow-md' : 'border-gray-200 hover:border-[#C8A35D]/50'"
                        >
                            <img :src="img" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
            </div>

            
            <div class="flex flex-col">
                
                <p class="text-xs tracking-[0.2em] text-[#C8A35D] uppercase font-medium mb-3"><?php echo e($product->category->name ?? 'Svaraa Collection'); ?></p>

                
                <h1 class="text-3xl md:text-4xl font-serif text-[#2E1A12] mb-4 leading-tight"><?php echo e($product->name); ?></h1>

                
                <div class="flex items-center gap-2 mb-6">
                    <div class="flex">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = range(1, 5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $star): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <svg class="w-5 h-5 text-[#C8A35D]" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <span class="text-sm text-gray-500">(Reviews coming soon)</span>
                </div>

                
                <div class="mb-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($product->discount_price) && $product->discount_price < $product->price): ?>
                        <div class="flex items-center gap-3">
                            <span class="text-3xl font-bold text-[#C8A35D]">₹<?php echo e(number_format($product->discount_price, 2)); ?></span>
                            <span class="text-xl text-gray-400 line-through">₹<?php echo e(number_format($product->price, 2)); ?></span>
                            <span class="bg-[#C8A35D]/10 text-[#C8A35D] text-sm font-medium px-3 py-1 rounded-full">
                                Save <?php echo e(round((($product->price - $product->discount_price) / $product->price) * 100)); ?>%
                            </span>
                        </div>
                    <?php else: ?>
                        <span class="text-3xl font-bold text-[#C8A35D]">₹<?php echo e(number_format($product->price, 2)); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->short_description): ?>
                    <p class="text-gray-600 leading-relaxed mb-6"><?php echo e($product->short_description); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <div class="flex items-center gap-2 mb-8">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($product->stock ?? 0) > 0): ?>
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                        <span class="text-sm text-green-700 font-medium">In Stock (<?php echo e($product->stock); ?> available)</span>
                    <?php else: ?>
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        <span class="text-sm text-red-600 font-medium">Out of Stock</span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                
                <div class="border-t border-[#E8DCCB] mb-8"></div>

                
                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    
                    <div class="flex items-center border border-gray-200 rounded-full overflow-hidden">
                        <button @click="quantity = Math.max(1, quantity - 1)" class="w-12 h-12 flex items-center justify-center text-gray-500 hover:bg-[#E8DCCB] transition-colors">−</button>
                        <span class="w-12 h-12 flex items-center justify-center font-medium text-[#2E1A12] text-lg" x-text="quantity"></span>
                        <button @click="quantity = Math.min(<?php echo e($product->stock ?? 10); ?>, quantity + 1)" class="w-12 h-12 flex items-center justify-center text-gray-500 hover:bg-[#E8DCCB] transition-colors">+</button>
                    </div>
                    
                    <form action="<?php echo e(route('cart.add')); ?>" method="POST" class="flex-1">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
                        <input type="hidden" name="quantity" :value="quantity">
                        <button type="submit" class="w-full h-12 bg-[#6E0F12] text-white rounded-full font-medium hover:bg-[#520b0d] transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            Add to Cart
                        </button>
                    </form>
                </div>

                
                <div class="flex gap-4 mb-8">
                    <form action="<?php echo e(route('wishlist.add')); ?>" method="POST" class="flex-1">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
                        <button type="submit" class="w-full h-12 border border-[#2E1A12] text-[#2E1A12] rounded-full font-medium hover:bg-[#2E1A12] hover:text-white transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            Add to Wishlist
                        </button>
                    </form>
                    <a href="<?php echo e(route('checkout.index')); ?>" class="flex-1 h-12 bg-[#C8A35D] text-white rounded-full font-medium hover:bg-[#b8934d] transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Buy Now
                    </a>
                </div>

                
                <div class="grid grid-cols-3 gap-4 p-4 bg-[#FDFBF7] rounded-xl border border-[#E8DCCB]">
                    <div class="text-center">
                        <svg class="w-6 h-6 text-[#C8A35D] mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <p class="text-xs text-gray-600 font-medium">Certified</p>
                    </div>
                    <div class="text-center">
                        <svg class="w-6 h-6 text-[#C8A35D] mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <p class="text-xs text-gray-600 font-medium">Free Shipping</p>
                    </div>
                    <div class="text-center">
                        <svg class="w-6 h-6 text-[#C8A35D] mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <p class="text-xs text-gray-600 font-medium">Easy Returns</p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="mt-16 border-t border-[#E8DCCB] pt-12">
            
            <div class="flex gap-8 border-b border-gray-200 mb-8">
                <button
                    @click="activeTab = 'description'"
                    class="pb-4 text-sm font-medium uppercase tracking-widest transition-colors border-b-2 -mb-px"
                    :class="activeTab === 'description' ? 'text-[#6E0F12] border-[#C8A35D]' : 'text-gray-500 border-transparent hover:text-[#2E1A12]'"
                >
                    Description
                </button>
                <button
                    @click="activeTab = 'details'"
                    class="pb-4 text-sm font-medium uppercase tracking-widest transition-colors border-b-2 -mb-px"
                    :class="activeTab === 'details' ? 'text-[#6E0F12] border-[#C8A35D]' : 'text-gray-500 border-transparent hover:text-[#2E1A12]'"
                >
                    Details
                </button>
                <button
                    @click="activeTab = 'care'"
                    class="pb-4 text-sm font-medium uppercase tracking-widest transition-colors border-b-2 -mb-px"
                    :class="activeTab === 'care' ? 'text-[#6E0F12] border-[#C8A35D]' : 'text-gray-500 border-transparent hover:text-[#2E1A12]'"
                >
                    Care Instructions
                </button>
            </div>

            
            <div>
                <div x-show="activeTab === 'description'" x-transition class="prose prose-gray max-w-none">
                    <?php echo $product->description ?? '<p class="text-gray-600">A meticulously crafted piece from Svaraa Jewels. Each detail is carefully designed to bring out the timeless beauty of fine jewelry.</p>'; ?>

                </div>
                <div x-show="activeTab === 'details'" x-transition style="display: none;">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="flex justify-between py-3 border-b border-gray-100">
                                <span class="text-sm text-gray-500">SKU</span>
                                <span class="text-sm font-medium text-[#2E1A12]"><?php echo e($product->sku ?? 'SJ-' . str_pad($product->id, 5, '0', STR_PAD_LEFT)); ?></span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Category</span>
                                <span class="text-sm font-medium text-[#2E1A12]"><?php echo e($product->category->name ?? 'Jewelry'); ?></span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Availability</span>
                                <span class="text-sm font-medium <?php echo e(($product->stock ?? 0) > 0 ? 'text-green-600' : 'text-red-600'); ?>">
                                    <?php echo e(($product->stock ?? 0) > 0 ? 'In Stock' : 'Out of Stock'); ?>

                                </span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between py-3 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Material</span>
                                <span class="text-sm font-medium text-[#2E1A12]"><?php echo e($product->material ?? 'Premium Quality'); ?></span>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100">
                                <span class="text-sm text-gray-500">Weight</span>
                                <span class="text-sm font-medium text-[#2E1A12]"><?php echo e($product->weight ?? 'Varies'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="activeTab === 'care'" x-transition style="display: none;">
                    <div class="space-y-6 text-gray-600">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full bg-[#C8A35D]/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-[#C8A35D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-[#2E1A12] mb-1">Storage</h4>
                                <p class="text-sm">Store in a cool, dry place. Keep in the provided jewelry box to prevent tarnishing and scratches.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full bg-[#C8A35D]/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-[#C8A35D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-[#2E1A12] mb-1">Cleaning</h4>
                                <p class="text-sm">Use a soft, lint-free cloth to gently polish your jewelry. Avoid harsh chemicals and ultrasonic cleaners.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-full bg-[#C8A35D]/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-[#C8A35D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-medium text-[#2E1A12] mb-1">Precautions</h4>
                                <p class="text-sm">Remove jewelry before swimming, exercising, or sleeping. Avoid contact with perfumes, lotions, and hairsprays.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($relatedProducts) && $relatedProducts->count() > 0): ?>
    <section class="bg-[#FDFBF7] py-16 border-t border-[#E8DCCB]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-serif text-[#2E1A12] text-center mb-10">You May Also Like</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $relatedProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal789b6d25aea4eb0119c589010e2d673d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal789b6d25aea4eb0119c589010e2d673d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.shop.product-card','data' => ['product' => $related]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('shop.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['product' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($related)]); ?>
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
    </section>
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
<?php /**PATH /var/www/html/resources/views/pages/shop/show.blade.php ENDPATH**/ ?>