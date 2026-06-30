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

     <?php $__env->slot('title', null, []); ?> Order Details | Svaraa Jewels <?php $__env->endSlot(); ?>

    <div class="bg-[#FFFFF0] py-12 border-b border-[#C8A35D]/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-serif text-[#6E0F12]">Order Details</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
            <!-- Sidebar -->
            <div class="w-full lg:w-1/4">
                <?php if (isset($component)) { $__componentOriginald469a6454e7d0322d5c7465b7fb2ae65 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald469a6454e7d0322d5c7465b7fb2ae65 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.account.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('account.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald469a6454e7d0322d5c7465b7fb2ae65)): ?>
<?php $attributes = $__attributesOriginald469a6454e7d0322d5c7465b7fb2ae65; ?>
<?php unset($__attributesOriginald469a6454e7d0322d5c7465b7fb2ae65); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald469a6454e7d0322d5c7465b7fb2ae65)): ?>
<?php $component = $__componentOriginald469a6454e7d0322d5c7465b7fb2ae65; ?>
<?php unset($__componentOriginald469a6454e7d0322d5c7465b7fb2ae65); ?>
<?php endif; ?>
            </div>

            <!-- Content -->
            <div class="w-full lg:w-3/4">
                <div class="mb-6">
                    <a href="<?php echo e(route('account.orders')); ?>" class="text-sm text-gray-500 hover:text-[#6E0F12] inline-flex items-center gap-1 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Back to Orders
                    </a>
                </div>

                <div class="bg-white p-8 border border-gray-100 shadow-sm mb-8">
                    <div class="flex flex-wrap justify-between items-start gap-4 mb-8 pb-6 border-b border-gray-100">
                        <div>
                            <h2 class="text-xl font-serif text-[#6E0F12] mb-1">Order <?php echo e($order->order_number); ?></h2>
                            <p class="text-sm text-gray-500">Placed on <?php echo e($order->created_at->format('F d, Y \a\t h:i A')); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 mb-2">
                                Status:
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e(in_array($order->order_status?->value ?? $order->order_status, ['completed', 'delivered', 'shipped']) ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'); ?>">
                                    <?php echo e(ucfirst((string) ($order->order_status?->value ?? $order->order_status))); ?>

                                </span>
                            </p>
                            <p class="text-sm font-medium text-gray-900">
                                Payment:
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e(in_array($order->payment_status, ['captured', 'paid', 'refunded']) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                    <?php echo e(ucfirst((string) $order->payment_status)); ?>

                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-6 mb-8">
                        <h3 class="text-lg font-serif text-gray-900 mb-4">Items Summary</h3>
                        <div class="divide-y divide-gray-100 border-t border-gray-100">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="py-4 flex gap-6">
                                    <div class="w-20 h-20 bg-gray-50 border border-gray-100 flex-shrink-0">
                                        <img src="<?php echo e($item->product->thumbnail ? asset('storage/' . $item->product->thumbnail) : asset('images/placeholder.jpg')); ?>" alt="<?php echo e($item->product->name); ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 flex flex-col justify-between">
                                        <div class="flex justify-between">
                                            <div>
                                                <h4 class="font-medium text-gray-900">
                                                    <a href="<?php echo e(route('products.show', $item->product)); ?>" class="hover:text-[#6E0F12] transition-colors"><?php echo e($item->product->name); ?></a>
                                                </h4>
                                                <p class="text-sm text-gray-500 mt-1">Qty: <?php echo e($item->quantity); ?></p>
                                            </div>
                                            <p class="font-medium text-[#C8A35D]">₹<?php echo e(number_format($item->price, 2)); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t border-gray-100 pt-8">
                        <!-- Shipping Details -->
                        <div>
                            <h3 class="text-lg font-serif text-gray-900 mb-4">Shipping Address</h3>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->address): ?>
                                <div class="text-sm text-gray-600 leading-relaxed bg-gray-50 p-4 rounded border border-gray-100">
                                    <p class="font-medium text-gray-900 mb-1"><?php echo e($order->address->full_name); ?></p>
                                    <p><?php echo e($order->address->address_line); ?></p>
                                    <p><?php echo e($order->address->city); ?>, <?php echo e($order->address->state); ?> <?php echo e($order->address->pincode); ?></p>
                                    <p><?php echo e($order->address->country); ?></p>
                                    <p class="mt-2 text-gray-500">Phone: <?php echo e($order->address->phone); ?></p>
                                </div>
                            <?php else: ?>
                                <p class="text-sm text-gray-500">Address details not available.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Order Total -->
                        <div>
                            <h3 class="text-lg font-serif text-gray-900 mb-4">Order Total</h3>
                            <div class="bg-gray-50 p-6 rounded border border-gray-100 space-y-3">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Subtotal</span>
                                    <span>₹<?php echo e(number_format($order->subtotal, 2)); ?></span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600 pb-3 border-b border-gray-200">
                                    <span>Shipping</span>
                                    <span class="text-green-600"><?php echo e($order->shipping > 0 ? '₹'.number_format($order->shipping, 2) : 'Free'); ?></span>
                                </div>
                                <div class="flex justify-between items-center pt-1">
                                    <span class="font-medium text-gray-900">Total</span>
                                    <span class="text-xl font-medium text-[#6E0F12]">₹<?php echo e(number_format($order->total, 2)); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
<?php /**PATH /var/www/html/resources/views/pages/account/order-detail.blade.php ENDPATH**/ ?>