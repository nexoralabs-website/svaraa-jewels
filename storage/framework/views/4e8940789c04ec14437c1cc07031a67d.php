<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['addresses']));

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

foreach (array_filter((['addresses']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="{ useExistingAddress: <?php echo e($addresses->count() > 0 ? 'true' : 'false'); ?> }">
    <div class="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
        <h2 class="text-xl font-serif text-gray-900">Shipping Address</h2>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($addresses->count() > 0): ?>
            <button type="button" @click="useExistingAddress = !useExistingAddress" class="text-sm font-medium text-[#6E0F12] hover:text-[#520b0d]">
                <span x-text="useExistingAddress ? 'Add New Address' : 'Use Existing Address'"></span>
            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($addresses->count() > 0): ?>
        <div x-show="useExistingAddress" x-transition.opacity class="space-y-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $addresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <label class="flex p-4 border cursor-pointer hover:border-[#C8A35D] transition-colors relative" :class="$refs.addressRadio<?php echo e($address->id); ?>.checked ? 'border-[#6E0F12] bg-[#FFFFF0]/30' : 'border-gray-200'">
                    <div class="flex items-center h-5">
                        <input type="radio" x-ref="addressRadio<?php echo e($address->id); ?>" name="address_id" value="<?php echo e($address->id); ?>" class="w-4 h-4 text-[#6E0F12] bg-gray-100 border-gray-300 focus:ring-[#6E0F12]" <?php echo e($loop->first ? 'checked' : ''); ?>>
                    </div>
                    <div class="ml-4 text-sm">
                        <span class="font-medium text-gray-900"><?php echo e($address->full_name); ?></span>
                        <p id="helper-radio-text" class="text-xs font-normal text-gray-500 mt-1">
                            <?php echo e($address->address_line); ?>, <?php echo e($address->city); ?>, <?php echo e($address->state); ?> <?php echo e($address->pincode); ?>, <?php echo e($address->country); ?>

                        </p>
                        <p class="text-xs text-gray-500 mt-1">Phone: <?php echo e($address->phone); ?></p>
                    </div>
                </label>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div x-show="!useExistingAddress" x-transition.opacity class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Full Name</label>
            <input type="text" name="full_name" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="John Doe">
        </div>
        
        <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Phone Number</label>
            <input type="text" name="phone" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="+1 (555) 000-0000">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Address Line</label>
            <input type="text" name="address_line" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="123 Luxury Lane, Apt 4B">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">City</label>
            <input type="text" name="city" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="New York">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">State / Province</label>
            <input type="text" name="state" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="NY">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Postal Code</label>
            <input type="text" name="pincode" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="10001">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Country</label>
            <input type="text" name="country" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="United States">
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/components/checkout/address-form.blade.php ENDPATH**/ ?>