<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([]));

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

foreach (array_filter(([]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="text-center py-20">
    <img src="<?php echo e(asset('images/empty-state.png')); ?>" alt="No products" class="mx-auto mb-6 w-48 h-48 object-cover" />
    <h3 class="text-2xl font-serif text-[#2E1A12] mb-2">No earrings found</h3>
    <p class="text-gray-600 mb-6">We couldn’t find any earrings matching your criteria. Try adjusting the filters or explore our collection.</p>
    <a href="<?php echo e(route('products.index')); ?>" class="inline-block bg-[#6E0F12] text-white px-6 py-3 rounded-full hover:bg-[#520b0d] transition-colors">
        Reset Filters
    </a>
</div>
<?php /**PATH /var/www/html/resources/views/components/shop/empty-state.blade.php ENDPATH**/ ?>