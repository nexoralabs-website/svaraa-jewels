<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['categories', 'filters' => [], 'isCategoryPage' => false]));

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

foreach (array_filter((['categories', 'filters' => [], 'isCategoryPage' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="space-y-8" x-data="{
    priceMin: <?php echo e(request('min_price', 0)); ?>,
    priceMax: <?php echo e(request('max_price', 10000)); ?>,
    applyFilters() {
        let url = new URL(window.location.href);
        url.searchParams.set('min_price', this.priceMin);
        url.searchParams.set('max_price', this.priceMax);
        if (this.inStock) {
            url.searchParams.set('in_stock', 1);
        } else {
            url.searchParams.delete('in_stock');
        }
        window.location.href = url.toString();
    },
    inStock: <?php echo e(request('in_stock') ? 'true' : 'false'); ?>,
}">
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isCategoryPage): ?>
    <div>
        <h3 class="text-sm font-medium text-gray-900 uppercase tracking-widest mb-4 border-b border-[#C8A35D]/30 pb-2">Search</h3>
        <form action="<?php echo e(route('products.index')); ?>" method="GET" class="relative">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('sort')): ?>
                <input type="hidden" name="sort" value="<?php echo e(request('sort')); ?>">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('category')): ?>
                <input type="hidden" name="category" value="<?php echo e(request('category')); ?>">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <input 
                type="text" 
                name="search" 
                value="<?php echo e(request('search')); ?>"
                placeholder="Search earrings..." 
                class="w-full border border-gray-200 pl-4 pr-10 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] transition-colors outline-none"
            >
            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#6E0F12]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </button>
        </form>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div>
        <h3 class="text-sm font-medium text-gray-900 uppercase tracking-widest mb-4 border-b border-[#C8A35D]/30 pb-2">Price Range</h3>
        <div class="flex items-center space-x-2">
            <input type="number" min="0" max="100000" step="10" x-model.number="priceMin" class="w-1/2 border border-gray-200 rounded px-2 py-1 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D]" placeholder="Min">
            <span class="text-gray-500">-</span>
            <input type="number" min="0" max="100000" step="10" x-model.number="priceMax" class="w-1/2 border border-gray-200 rounded px-2 py-1 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D]" placeholder="Max">
        </div>
        <button type="button" @click="applyFilters()" class="mt-3 w-full bg-[#6E0F12] text-white py-2 rounded hover:bg-[#520b0d] transition-colors">Apply</button>
    </div>

    
    <div>
        <h3 class="text-sm font-medium text-gray-900 uppercase tracking-widest mb-4 border-b border-[#C8A35D]/30 pb-2">Availability</h3>
        <label class="inline-flex items-center">
            <input type="checkbox" x-model="inStock" class="form-checkbox h-4 w-4 text-[#6E0F12] border-gray-300 rounded">
            <span class="ml-2 text-sm text-gray-700">In Stock Only</span>
        </label>
    </div>

    
    <div class="bg-gray-50 p-6 border border-gray-100">
        <h3 class="text-sm font-medium text-[#6E0F12] uppercase tracking-widest mb-3">Premium Quality</h3>
        <p class="text-xs text-gray-600 leading-relaxed">
            All our jewelry is crafted with precision and certified for purity. Experience the luxury of Svaraa.
        </p>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/components/shop/filter-sidebar.blade.php ENDPATH**/ ?>