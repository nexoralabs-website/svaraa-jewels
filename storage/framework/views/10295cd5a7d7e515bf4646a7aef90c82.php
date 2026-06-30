<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['items', 'total']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['items', 'total']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="bg-gray-50 p-8 border border-gray-100 sticky top-8">
    <h3 class="text-lg font-serif text-gray-900 mb-6">Order Summary</h3>
    
    
    <div class="space-y-4 mb-6 pb-6 border-b border-gray-200 max-h-[40vh] overflow-y-auto pr-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="flex gap-4">
                <div class="w-16 h-16 bg-white border border-gray-100 flex-shrink-0">
                    <img src="<?php echo e($item->product->thumbnail ? asset('storage/' . $item->product->thumbnail) : asset('images/placeholder.jpg')); ?>" alt="<?php echo e($item->product->name); ?>" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 flex justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 line-clamp-1"><?php echo e($item->product->name); ?></h4>
                        <p class="text-xs text-gray-500 mt-1">Qty: <?php echo e($item->quantity); ?></p>
                    </div>
                    <p class="text-sm font-medium text-[#C8A35D]">₹<?php echo e(number_format($item->product->price * $item->quantity, 2)); ?></p>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    
    <div class="space-y-4 mb-6 pb-6 border-b border-gray-200">
        <div class="flex justify-between text-sm text-gray-600">
            <span>Subtotal</span>
            <span>₹<?php echo e(number_format($total, 2)); ?></span>
        </div>
        <div class="flex justify-between text-sm text-gray-600">
            <span>Shipping</span>
            <span class="text-green-600">Free</span>
        </div>
        <div class="flex justify-between text-sm text-gray-600">
            <span>Taxes</span>
            <span>Calculated automatically</span>
        </div>
    </div>
    
    <div class="flex justify-between items-center mb-8">
        <span class="text-base font-medium text-gray-900">Total</span>
        <span class="text-2xl font-medium text-[#6E0F12]">₹<?php echo e(number_format($total, 2)); ?></span>
    </div>

    <button type="submit" class="w-full bg-[#6E0F12] text-white text-center px-8 py-4 uppercase tracking-widest text-sm font-medium hover:bg-[#520b0d] transition-colors shadow-sm flex items-center justify-center gap-2">
        Place Order
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
        </svg>
    </button>
</div>
<?php /**PATH /var/www/html/resources/views/components/checkout/summary.blade.php ENDPATH**/ ?>