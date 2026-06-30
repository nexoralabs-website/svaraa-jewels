<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['product']));

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

foreach (array_filter((['product']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="space-y-8" x-data="{ quantity: 1, inWishlist: false }">
    
    <div>
        <p class="text-sm tracking-widest text-[#C8A35D] uppercase mb-2"><?php echo e($product->category->name ?? 'Jewelry'); ?></p>
        <h1 class="text-3xl md:text-4xl font-serif text-gray-900 mb-4"><?php echo e($product->name); ?></h1>
        <p class="text-2xl text-[#6E0F12] font-medium">₹<?php echo e(number_format($product->price, 2)); ?></p>
    </div>

    
    <div class="prose prose-sm text-gray-600 leading-relaxed">
        <p><?php echo e($product->description); ?></p>
    </div>

    
    <div class="border-t border-b border-gray-100 py-6">
        <h3 class="text-sm font-medium text-gray-900 uppercase tracking-widest mb-4">Product Specifications</h3>
        <dl class="grid grid-cols-2 gap-x-4 gap-y-4 text-sm">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->purity): ?>
            <div>
                <dt class="text-gray-500">Gold Purity</dt>
                <dd class="font-medium text-gray-900 mt-1"><?php echo e($product->purity); ?></dd>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($product->weight): ?>
            <div>
                <dt class="text-gray-500">Approx. Weight</dt>
                <dd class="font-medium text-gray-900 mt-1"><?php echo e($product->weight); ?>g</dd>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <div>
                <dt class="text-gray-500">Availability</dt>
                <dd class="font-medium <?php echo e($product->stock > 0 ? 'text-green-600' : 'text-red-500'); ?> mt-1">
                    <?php echo e($product->stock > 0 ? "In Stock ($product->stock available)" : 'Out of Stock'); ?>

                </dd>
            </div>
            
            <div>
                <dt class="text-gray-500">Shipping</dt>
                <dd class="font-medium text-gray-900 mt-1">Free Insured Delivery</dd>
            </div>
        </dl>
    </div>

    
    <div class="space-y-6" x-show="true" x-cloak>
        
        <div>
            <label class="block text-sm font-medium text-gray-900 uppercase tracking-widest mb-3">Quantity</label>
            <div class="flex items-center border border-gray-200 w-max bg-white">
                <button type="button" @click="if(quantity > 1) quantity--" class="px-4 py-2 text-gray-500 hover:text-[#6E0F12] hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                    </svg>
                </button>
                <input type="number" x-model="quantity" min="1" max="<?php echo e($product->stock); ?>" class="w-16 text-center border-x border-gray-200 py-2 focus:ring-0 outline-none -moz-appearance: textfield;">
                <button type="button" @click="if(quantity < <?php echo e($product->stock); ?>) quantity++" class="px-4 py-2 text-gray-500 hover:text-[#6E0F12] hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </button>
            </div>
        </div>

        
        <div class="flex flex-col sm:flex-row gap-4 pt-4">
            <button type="button" @click="$store.cart.add(<?php echo e($product->id); ?>, quantity)" class="flex-1 bg-[#6E0F12] text-white px-8 py-4 uppercase tracking-widest text-sm font-medium hover:bg-[#520b0d] transition-colors flex justify-center items-center gap-2" <?php echo e($product->stock <= 0 ? 'disabled' : ''); ?>>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                Add to Cart
            </button>
            <button type="button" @click="inWishlist = !inWishlist" class="sm:w-auto px-8 py-4 border text-sm uppercase tracking-widest font-medium transition-colors flex justify-center items-center gap-2" :class="inWishlist ? 'border-green-500 text-green-600 bg-green-50' : 'border-[#C8A35D] text-[#C8A35D] hover:bg-[#C8A35D] hover:text-white'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" :class="inWishlist ? 'fill-current' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                <span x-text="inWishlist ? 'Added to Wishlist' : 'Add to Wishlist'"></span>
            </button>
        </div>
    </div>

    
    <div class="grid grid-cols-3 gap-4 pt-6 border-t border-gray-100">
        <div class="text-center">
            <div class="mx-auto w-10 h-10 bg-[#FFFFF0] rounded-full flex items-center justify-center mb-2 text-[#C8A35D]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                </svg>
            </div>
            <p class="text-xs text-gray-500">Certified Authentic</p>
        </div>
        <div class="text-center">
            <div class="mx-auto w-10 h-10 bg-[#FFFFF0] rounded-full flex items-center justify-center mb-2 text-[#C8A35D]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.902 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
            </div>
            <p class="text-xs text-gray-500">Secure Delivery</p>
        </div>
        <div class="text-center">
            <div class="mx-auto w-10 h-10 bg-[#FFFFF0] rounded-full flex items-center justify-center mb-2 text-[#C8A35D]">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
            </div>
            <p class="text-xs text-gray-500">15-Day Returns</p>
        </div>
    </div>
</div>

<style>
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
</style>
<?php /**PATH /var/www/html/resources/views/components/product/details.blade.php ENDPATH**/ ?>