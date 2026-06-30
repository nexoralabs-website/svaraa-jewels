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

     <?php $__env->slot('title', null, []); ?> Request Refund — Order #<?php echo e($order->order_number); ?> | Svaraa Jewels <?php $__env->endSlot(); ?>

    <div class="bg-[#FDFBF7] py-10 border-b border-[#E8DCCB]/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <a href="<?php echo e(route('orders.show', $order)); ?>"
                   class="text-sm text-[#C8A35D] hover:text-[#b8934d] font-medium flex items-center gap-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Order
                </a>
            </div>
            <h1 class="text-2xl md:text-3xl font-serif text-[#2E1A12] mt-4">Request a Refund</h1>
            <p class="text-gray-500 text-sm mt-1">Order #<?php echo e($order->order_number); ?></p>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm" role="alert">
            <?php echo e(session('error')); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="bg-white rounded-xl border border-[#E8DCCB] p-6 mb-8">
            <h2 class="text-base font-serif text-[#2E1A12] mb-4">Order Summary</h2>
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex justify-between">
                    <span>Order</span>
                    <span class="font-medium text-[#2E1A12] font-mono"><?php echo e($order->order_number); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Date</span>
                    <span><?php echo e($order->created_at->format('d M Y')); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Total</span>
                    <span class="font-semibold text-[#6E0F12]">&#8377;<?php echo e(number_format($order->total, 2)); ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Items</span>
                    <span><?php echo e($order->items->count()); ?> item(s)</span>
                </div>
            </div>
        </div>

        
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-8 flex gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-amber-800">
                <p class="font-medium mb-1">Refund Policy</p>
                <p>Refunds are processed within 3–5 business days after admin approval.
                   The amount will be credited to your original payment method.</p>
            </div>
        </div>

        
        <form action="<?php echo e(route('refunds.store', $order)); ?>" method="POST"
              class="bg-white rounded-xl border border-[#E8DCCB] p-6 md:p-8 space-y-6">
            <?php echo csrf_field(); ?>

            <div>
                <label for="reason"
                       class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-2">
                    Reason for Refund *
                </label>
                <textarea id="reason"
                          name="reason"
                          rows="5"
                          required
                          maxlength="1000"
                          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm
                                 focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D]
                                 outline-none transition-colors resize-none"
                          placeholder="Please describe the reason for your refund request (e.g. item damaged, wrong item received, quality issue)..."><?php echo e(old('reason')); ?></textarea>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-xs text-red-500 mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-xs text-gray-400 mt-1 text-right">
                    <span x-data="{ len: <?php echo e(strlen(old('reason', ''))); ?> }"
                          x-text="len + ' / 1000'"></span>
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-[#6E0F12] text-white py-3 rounded-full font-medium text-sm
                               hover:bg-[#520b0d] transition-colors text-center">
                    Submit Refund Request
                </button>
                <a href="<?php echo e(route('orders.show', $order)); ?>"
                   class="flex-1 border border-gray-200 text-gray-600 py-3 rounded-full font-medium text-sm
                          hover:border-gray-300 hover:bg-gray-50 transition-colors text-center">
                    Cancel
                </a>
            </div>
        </form>
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
<?php /**PATH /var/www/html/resources/views/pages/refunds/create.blade.php ENDPATH**/ ?>