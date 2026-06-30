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

     <?php $__env->slot('title', null, []); ?> Order Success | Svaraa Jewels <?php $__env->endSlot(); ?>

    <div class="bg-[#FFFFF0] min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl w-full bg-white p-10 text-center shadow-xl border border-[#C8A35D]/20 relative overflow-hidden">
            
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-[#6E0F12] via-[#C8A35D] to-[#6E0F12]"></div>
            
            <div class="mx-auto w-24 h-24 bg-[#FFFFF0] rounded-full flex items-center justify-center mb-8 border-2 border-[#C8A35D]/30 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-[#C8A35D]">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <h1 class="text-4xl font-serif text-[#6E0F12] mb-4">
                <?php echo e($order->payment_method === 'cod' || $order->payment_status === 'captured' ? 'Thank You!' : 'Payment Processing'); ?>

            </h1>
            <p class="text-xl text-gray-900 font-medium mb-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->payment_method === 'cod'): ?>
                    Your order has been placed successfully.
                <?php elseif($order->payment_status === 'captured'): ?>
                    Your payment has been confirmed successfully.
                <?php elseif($order->payment_status === 'failed'): ?>
                    Your payment could not be completed.
                <?php else: ?>
                    We are confirming your payment with Razorpay.
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
            <p class="text-gray-500 mb-8">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->payment_status === 'failed'): ?>
                    Your cart has been preserved so you can retry checkout.
                <?php else: ?>
                    We will send a confirmation email with your order details shortly.
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>
            
            <div class="bg-gray-50 p-6 rounded-lg border border-gray-100 mb-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-left">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-widest mb-1">Order Number</p>
                    <p class="font-medium text-gray-900"><?php echo e($order->order_number); ?></p>
                </div>
                <div class="sm:text-right">
                    <p class="text-xs text-gray-500 uppercase tracking-widest mb-1">Payment Status</p>
                    <p class="font-medium text-[#C8A35D]"><?php echo e(ucfirst($order->payment_status)); ?></p>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('orders.show', $order)); ?>" class="inline-block bg-[#C8A35D] text-white px-8 py-3.5 uppercase tracking-widest text-sm font-medium hover:bg-[#b8934d] transition-colors">
                    View Order
                </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($order->payment_status, ['captured', 'refunded'])): ?>
                <a href="<?php echo e(route('orders.invoice', $order)); ?>" class="inline-block border border-[#2E1A12] text-[#2E1A12] px-6 py-3.5 uppercase tracking-widest text-sm font-medium hover:bg-[#2E1A12] hover:text-white transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Invoice
                </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="<?php echo e(route('products.index')); ?>" class="inline-block border border-[#6E0F12] text-[#6E0F12] px-8 py-3.5 uppercase tracking-widest text-sm font-medium hover:bg-[#6E0F12] hover:text-white transition-colors">
                    Continue Shopping
                </a>
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

<?php /**PATH /var/www/html/resources/views/pages/checkout/success.blade.php ENDPATH**/ ?>