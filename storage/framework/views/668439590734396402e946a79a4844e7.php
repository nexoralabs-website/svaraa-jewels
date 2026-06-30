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

     <?php $__env->slot('title', null, []); ?> Order History | Svaraa Jewels <?php $__env->endSlot(); ?>

    <div class="bg-[#FDFBF7] py-10 border-b border-[#C8A35D]/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-serif text-[#2E1A12]">Order History</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
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

            <div class="w-full lg:w-3/4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orders->count() > 0): ?>
                <div class="space-y-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $statusColors = [
                            'completed'  => 'bg-green-100 text-green-800',
                            'paid'       => 'bg-blue-100 text-blue-800',
                            'processing' => 'bg-blue-100 text-blue-800',
                            'shipped'    => 'bg-purple-100 text-purple-800',
                            'cancelled'  => 'bg-red-100 text-red-800',
                            'failed'     => 'bg-red-100 text-red-800',
                            'pending'    => 'bg-yellow-100 text-yellow-800',
                        ];
                        $statusColor = $statusColors[$order->order_status->value] ?? 'bg-gray-100 text-gray-700';
                    ?>
                    <div class="bg-white rounded-xl border border-[#E8DCCB] overflow-hidden hover:border-[#C8A35D]/50 transition-colors">
                        <div class="bg-[#FDFBF7] px-5 py-3 border-b border-[#E8DCCB] flex flex-wrap justify-between items-center gap-3">
                            <div class="flex gap-6 text-sm">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-0.5">Order</p>
                                    <p class="font-medium text-[#2E1A12] font-mono text-xs"><?php echo e($order->order_number); ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-0.5">Date</p>
                                    <p class="font-medium text-[#2E1A12]"><?php echo e($order->created_at->format('d M Y')); ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-0.5">Total</p>
                                    <p class="font-semibold text-[#6E0F12]">&#8377;<?php echo e(number_format($order->total, 2)); ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-0.5">Status</p>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($statusColor); ?>">
                                        <?php echo e(ucfirst($order->order_status->value)); ?>

                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="<?php echo e(route('account.orders.show', $order)); ?>"
                                   class="text-sm text-[#C8A35D] hover:text-[#b8934d] font-medium transition-colors">
                                    View Details &rarr;
                                </a>
                            </div>
                        </div>

                        <div class="px-5 py-4 flex items-center justify-between gap-4">
                            <div class="flex gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $order->items->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="w-12 h-12 rounded-lg border border-[#E8DCCB] overflow-hidden bg-gray-50 flex-shrink-0">
                                    <img src="<?php echo e($item->product?->thumbnail ? asset('storage/' . $item->product->thumbnail) : asset('images/placeholder.jpg')); ?>"
                                         alt="<?php echo e($item->product_name); ?>"
                                         class="w-full h-full object-cover">
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->items->count() > 4): ?>
                                <div class="w-12 h-12 rounded-lg border border-[#E8DCCB] bg-[#FDFBF7] flex items-center justify-center text-xs text-gray-500 font-medium">
                                    +<?php echo e($order->items->count() - 4); ?>

                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($order->payment_status, ['captured', 'refunded'])): ?>
                            <a href="<?php echo e(route('orders.invoice', $order)); ?>"
                               class="text-xs text-gray-500 hover:text-[#2E1A12] flex items-center gap-1 transition-colors"
                               title="Download Invoice">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Invoice
                            </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orders->hasPages()): ?>
                    <div class="mt-6"><?php echo e($orders->links()); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php else: ?>
                <?php if (isset($component)) { $__componentOriginal3607a477fdef7402bc742abad5df9c51 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3607a477fdef7402bc742abad5df9c51 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.empty-state','data' => ['icon' => 'orders','title' => 'No orders yet','description' => 'You haven\'t placed any orders yet. Explore our collection.','action' => route('products.index'),'actionLabel' => 'Start Shopping']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'orders','title' => 'No orders yet','description' => 'You haven\'t placed any orders yet. Explore our collection.','action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('products.index')),'action-label' => 'Start Shopping']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $attributes = $__attributesOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__attributesOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3607a477fdef7402bc742abad5df9c51)): ?>
<?php $component = $__componentOriginal3607a477fdef7402bc742abad5df9c51; ?>
<?php unset($__componentOriginal3607a477fdef7402bc742abad5df9c51); ?>
<?php endif; ?>
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
<?php /**PATH /var/www/html/resources/views/pages/account/orders.blade.php ENDPATH**/ ?>