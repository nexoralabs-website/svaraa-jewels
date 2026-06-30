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

     <?php $__env->slot('title', null, []); ?> Checkout | Svaraa Jewels <?php $__env->endSlot(); ?>

    <div class="bg-[#FDFBF7] py-10 border-b border-[#E8DCCB]/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-4xl font-serif text-[#2E1A12]">Checkout</h1>
        </div>
    </div>

    <form action="<?php echo e(route('checkout.process')); ?>" method="POST" id="checkoutForm"
          x-data="checkoutPage()" @submit.prevent="handleSubmit">
        <?php echo csrf_field(); ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error') || session('success')): ?>
            <div x-data="{ show: true }" x-show="show" class="mb-8">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg" role="alert"><?php echo e(session('error')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg" role="alert"><?php echo e(session('success')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div x-show="alert.message" x-cloak
                 class="mb-6 rounded-lg border px-4 py-3 text-sm flex items-center justify-between"
                 :class="{
                     'border-red-200 bg-red-50 text-red-700': alert.type === 'error',
                     'border-green-200 bg-green-50 text-green-700': alert.type === 'success',
                     'border-[#C8A35D]/40 bg-[#C8A35D]/10 text-[#2E1A12]': alert.type === 'processing'
                 }">
                <span x-text="alert.message"></span>
                <button type="button" x-show="alert.type !== 'processing'" @click="alert = {}" class="ml-4 opacity-60 hover:opacity-100">&times;</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                
                <div class="lg:col-span-2 space-y-8">

                    
                    <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB]/60 p-6 md:p-8">
                        <h2 class="text-lg font-serif text-[#2E1A12] mb-5">Contact Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1.5">Full Name *</label>
                                <input type="text" name="customer_name" x-model="form.customer_name" required
                                       class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1.5">Email *</label>
                                <input type="email" name="customer_email" x-model="form.customer_email" required
                                       class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none transition-colors">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1.5">Phone *</label>
                                <input type="tel" name="customer_phone" x-model="form.customer_phone" required
                                       class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none transition-colors">
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB]/60 p-6 md:p-8">
                        <h2 class="text-lg font-serif text-[#2E1A12] mb-5">Shipping Address</h2>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                            <input type="hidden" name="address_id" x-model="form.address_id">
                            <?php echo $__env->make('pages.checkout.addresses.index', [
                                'addresses' => $addresses ?? collect(),
                                'selectedAddressId' => $selectedAddressId ?? null,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1.5">Address Line *</label>
                                    <textarea name="shipping_address" x-model="form.shipping_address" rows="4" required
                                              class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none transition-colors"
                                              placeholder="Flat, building, street, area"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1.5">City *</label>
                                    <input type="text" name="city" x-model="form.city" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1.5">State *</label>
                                    <input type="text" name="state" x-model="form.state" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1.5">PIN Code *</label>
                                    <input type="text" name="pincode" x-model="form.pincode" required maxlength="10" class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1.5">Country *</label>
                                    <input type="text" name="country" x-model="form.country" value="India" required class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    
                    <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB]/60 p-6 md:p-8"
                         x-data="couponBox()" x-init="init()">
                        <h2 class="text-lg font-serif text-[#2E1A12] mb-5">
                            Discount Coupon
                            <span class="text-sm font-normal text-gray-400">(optional)</span>
                        </h2>

                        
                        <div x-show="applied" x-cloak
                             class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-3 mb-4">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-green-800" x-text="'Coupon ' + appliedCode + ' applied'"></p>
                                    <p class="text-xs text-green-600" x-text="'You save ' + appliedDiscountFmt"></p>
                                </div>
                            </div>
                            <button type="button" @click="removeCoupon()" :disabled="couponLoading"
                                    class="text-xs font-medium text-red-500 hover:text-red-700 hover:underline disabled:opacity-50 transition-colors">
                                <span x-text="couponLoading ? '...' : 'Remove'"></span>
                            </button>
                        </div>

                        
                        <div x-show="!applied" class="flex flex-col sm:flex-row gap-3">
                            <input type="text"
                                   x-model="couponCode"
                                   @keydown.enter.prevent="applyCoupon()"
                                   placeholder="Enter coupon code"
                                   :disabled="couponLoading"
                                   class="flex-1 border rounded-xl px-4 py-3 text-sm outline-none transition-colors uppercase tracking-wider
                                          focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] disabled:bg-gray-50 disabled:cursor-not-allowed"
                                   :class="couponError ? 'border-red-300 bg-red-50' : 'border-gray-200'">
                            <button type="button" @click="applyCoupon()"
                                    :disabled="couponLoading || !couponCode.trim()"
                                    class="sm:w-auto px-6 py-3 bg-[#2E1A12] text-white rounded-xl text-sm font-medium hover:bg-[#1a0e09] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 whitespace-nowrap">
                                <svg x-show="couponLoading" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-text="couponLoading ? 'Applying...' : 'Apply'"></span>
                            </button>
                        </div>
                        <p x-show="couponError" x-text="couponError" x-cloak
                           class="mt-2 text-xs text-red-600"></p>
                    </div>

                    
                    <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB]/60 p-6 md:p-8">
                        <h2 class="text-lg font-serif text-[#2E1A12] mb-5">Payment Method</h2>
                        <div class="space-y-3">
                            <label class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-colors"
                                   :class="form.payment_method === 'cod' ? 'border-[#C8A35D] bg-[#C8A35D]/5' : 'border-gray-200 hover:border-[#C8A35D]/50'">
                                <input type="radio" name="payment_method" value="cod" x-model="form.payment_method" class="text-[#C8A35D] focus:ring-[#C8A35D]">
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-[#2E1A12]">Cash on Delivery</span>
                                    <p class="text-xs text-gray-500 mt-0.5">Pay when you receive your order</p>
                                </div>
                                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded">Free</span>
                            </label>
                            <label class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-colors"
                                   :class="form.payment_method === 'card' ? 'border-[#C8A35D] bg-[#C8A35D]/5' : 'border-gray-200 hover:border-[#C8A35D]/50'">
                                <input type="radio" name="payment_method" value="card" x-model="form.payment_method" class="text-[#C8A35D] focus:ring-[#C8A35D]">
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-[#2E1A12]">Credit / Debit Card</span>
                                    <p class="text-xs text-gray-500 mt-0.5">Visa, Mastercard, RuPay</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-4 p-4 border rounded-xl cursor-pointer transition-colors"
                                   :class="form.payment_method === 'upi' ? 'border-[#C8A35D] bg-[#C8A35D]/5' : 'border-gray-200 hover:border-[#C8A35D]/50'">
                                <input type="radio" name="payment_method" value="upi" x-model="form.payment_method" class="text-[#C8A35D] focus:ring-[#C8A35D]">
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-[#2E1A12]">UPI</span>
                                    <p class="text-xs text-gray-500 mt-0.5">Google Pay, PhonePe, Paytm</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    
                    <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB]/60 p-6 md:p-8">
                        <h2 class="text-lg font-serif text-[#2E1A12] mb-4">Order Notes <span class="text-sm font-normal text-gray-400">(optional)</span></h2>
                        <textarea name="notes" x-model="form.notes" rows="3"
                                  class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none transition-colors resize-none"
                                  placeholder="Gift wrapping, delivery instructions, special requests..."></textarea>
                    </div>
                </div>

                
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB]/60 p-6 sticky top-28"
                         x-data="orderSummary()">
                        <h2 class="text-lg font-serif text-[#2E1A12] mb-5">Order Summary</h2>

                        
                        <div class="space-y-4 mb-5 max-h-60 overflow-y-auto pr-1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($cartItems ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="flex gap-3">
                                <div class="w-16 h-16 flex-shrink-0 bg-gray-50 rounded-lg overflow-hidden border border-gray-100">
                                    <img src="<?php echo e(optional($item->product->images->first())->image ? asset('storage/' . optional($item->product->images->first())->image) : asset('images/placeholder.jpg')); ?>"
                                         alt="<?php echo e($item->product->name); ?>" class="w-full h-full object-cover" loading="lazy">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-[#2E1A12] truncate leading-tight"><?php echo e($item->product->name); ?></h4>
                                    <p class="text-xs text-gray-500 mt-1">Qty: <?php echo e($item->quantity); ?></p>
                                </div>
                                <span class="text-sm font-medium text-[#2E1A12] whitespace-nowrap">
                                    &#8377;<?php echo e(number_format($item->product->price * $item->quantity, 2)); ?>

                                </span>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>

                        
                        <div class="border-t border-gray-100 pt-4 space-y-2.5">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span x-text="formatINR(subtotal)">&#8377;<?php echo e(number_format($subtotal ?? 0, 2)); ?></span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Shipping</span>
                                <span :class="shipping == 0 ? 'text-green-600 font-medium' : 'text-gray-600'"
                                      x-text="shipping == 0 ? 'FREE' : formatINR(shipping)">
                                    <?php echo e(($shipping ?? 0) == 0 ? 'FREE' : '&#8377;' . number_format($shipping ?? 0, 2)); ?>

                                </span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Taxes</span>
                                <span x-text="formatINR(tax)">&#8377;<?php echo e(number_format($tax ?? 0, 2)); ?></span>
                            </div>
                            
                            <div x-show="discount > 0" x-cloak
                                 class="flex justify-between text-sm text-green-600 font-medium bg-green-50 -mx-1 px-1 py-1.5 rounded">
                                <span>Coupon Discount</span>
                                <span x-text="'-' + formatINR(discount)"></span>
                            </div>
                            <div class="border-t border-gray-100 pt-3 flex justify-between items-center">
                                <span class="text-base font-medium text-[#2E1A12]">Total</span>
                                <span class="text-xl font-medium text-[#6E0F12]" x-text="formatINR(total)">
                                    &#8377;<?php echo e(number_format($total ?? ($subtotal ?? 0), 2)); ?>

                                </span>
                            </div>
                        </div>

                        <button type="submit" :disabled="isProcessing"
                                class="w-full mt-6 bg-[#C8A35D] text-white py-4 rounded-full font-medium text-base hover:bg-[#b8934d] transition-colors flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed shadow-md">
                            <svg x-show="!isProcessing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <svg x-show="isProcessing" class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="isProcessing ? 'Processing...' : 'Place Order'"></span>
                        </button>
                        <p class="flex items-center justify-center gap-2 mt-4 text-xs text-gray-500">Secure 256-bit SSL Encrypted</p>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
    // -------------------------------------------------------
    // checkoutPage() — handles COD form submit + Razorpay flow
    // -------------------------------------------------------
    function checkoutPage() {
        return {
            isProcessing: false,
            alert: { type: null, message: '' },
            form: {
                customer_name:    '<?php echo e(old(\'customer_name\', auth()->user()->name ?? \'\')); ?>',
                customer_email:   '<?php echo e(old(\'customer_email\', auth()->user()->email ?? \'\')); ?>',
                customer_phone:   '<?php echo e(old(\'customer_phone\', \'\')); ?>',
                address_id:       <?php echo e(old(\'address_id\', $selectedAddressId ?? \'null\')); ?>,
                shipping_address: '<?php echo e(old(\'shipping_address\', \'\')); ?>',
                city:             '<?php echo e(old(\'city\', \'\')); ?>',
                state:            '<?php echo e(old(\'state\', \'\')); ?>',
                pincode:          '<?php echo e(old(\'pincode\', \'\')); ?>',
                country:          '<?php echo e(old(\'country\', \'India\')); ?>',
                payment_method:   '<?php echo e(old(\'payment_method\', \'cod\')); ?>',
                notes:            '<?php echo e(old(\'notes\', \'\')); ?>',
            },

            init() {
                this.form.address_id = <?php echo e($selectedAddressId ?? 'null'); ?>;
            },

            handleSubmit(event) {
                if (this.form.payment_method === 'cod') {
                    event.target.submit();
                    return;
                }
                this.processCardPayment(event);
            },

            async processCardPayment(event) {
                this.isProcessing = true;
                this.alert = { type: 'processing', message: 'Creating your order...' };
                try {
                    const formData = new FormData(event.target);
                    const checkoutRes = await fetch(event.target.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' },
                    });
                    const checkoutData = await checkoutRes.json();
                    if (!checkoutRes.ok || !checkoutData.success) {
                        throw new Error(checkoutData.message || 'Unable to create order.');
                    }
                    this.alert = { type: 'processing', message: 'Initializing payment...' };
                    const paymentRes = await fetch(checkoutData.payment_create_url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': formData.get('_token'),
                        },
                        body: JSON.stringify({ order_id: checkoutData.order_id }),
                    });
                    const paymentData = await paymentRes.json();
                    if (!paymentRes.ok) {
                        throw new Error(paymentData.message || 'Unable to start payment.');
                    }
                    this.alert = { type: 'processing', message: 'Opening payment gateway...' };
                    this.openRazorpay(paymentData, formData.get('_token'));
                } catch (err) {
                    this.isProcessing = false;
                    this.alert = { type: 'error', message: err.message };
                }
            },

            openRazorpay(payload, csrfToken) {
                const checkout = new Razorpay({
                    ...payload,
                    handler: async (response) => {
                        this.alert = { type: 'processing', message: 'Confirming your payment...' };
                        try {
                            const verifyRes = await fetch('<?php echo e(route(\'payment.verify\')); ?>', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                body: JSON.stringify(response),
                            });
                            const verifyData = await verifyRes.json();
                            if (!verifyRes.ok || !verifyData.success) {
                                throw new Error(verifyData.message || 'Payment verification failed.');
                            }
                            window.location.href = verifyData.redirect_url;
                        } catch (err) {
                            this.isProcessing = false;
                            this.alert = { type: 'error', message: err.message };
                        }
                    },
                    modal: {
                        ondismiss: () => {
                            this.isProcessing = false;
                            this.alert = { type: 'error', message: 'Payment closed. Your cart is still safe.' };
                        },
                    },
                });
                checkout.on('payment.failed', (res) => {
                    this.isProcessing = false;
                    this.alert = { type: 'error', message: res.error?.description || 'Payment failed.' };
                });
                checkout.open();
            },
        };
    }

    // -------------------------------------------------------
    // couponBox() — AJAX coupon apply/remove, syncs orderSummary
    // -------------------------------------------------------
    function couponBox() {
        return {
            couponCode:         '',
            couponLoading:      false,
            couponError:        '',
            applied:            false,
            appliedCode:        '',
            appliedDiscountFmt: '',

            init() {
                // Restore from server-side session (rendered on page load)
                const serverCode     = '<?php echo e($coupon_code ?? ''); ?>';
                const serverDiscount = <?php echo e($coupon_discount ?? 0); ?>;
                if (serverCode && serverDiscount > 0) {
                    this.applied            = true;
                    this.appliedCode        = serverCode;
                    this.appliedDiscountFmt = '\u20b9' + serverDiscount.toFixed(2);
                    // Update order summary with session values
                    window.dispatchEvent(new CustomEvent('coupon-applied', {
                        detail: {
                            discount: serverDiscount,
                            subtotal: <?php echo e($subtotal ?? 0); ?>,
                            shipping: <?php echo e($shipping ?? 0); ?>,
                            total:    <?php echo e($total ?? 0); ?>,
                        }
                    }));
                }
            },

            async applyCoupon() {
                if (!this.couponCode.trim() || this.couponLoading) return;
                this.couponLoading = true;
                this.couponError   = '';
                try {
                    const res = await fetch('<?php echo e(route(\'checkout.coupon.apply\')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ code: this.couponCode.trim() }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        this.couponError = data.message || 'Could not apply coupon.';
                        return;
                    }
                    this.applied            = true;
                    this.appliedCode        = data.code;
                    this.appliedDiscountFmt = data.discount_fmt || ('\u20b9' + parseFloat(data.discount).toFixed(2));
                    this.couponCode         = '';
                    if (data.summary) {
                        window.dispatchEvent(new CustomEvent('coupon-applied', { detail: data.summary }));
                    }
                } catch (e) {
                    this.couponError = 'Network error. Please try again.';
                } finally {
                    this.couponLoading = false;
                }
            },

            async removeCoupon() {
                this.couponLoading = true;
                try {
                    const res = await fetch('<?php echo e(route(\'checkout.coupon.remove\')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.applied            = false;
                        this.appliedCode        = '';
                        this.appliedDiscountFmt = '';
                        this.couponError        = '';
                        if (data.summary) {
                            window.dispatchEvent(new CustomEvent('coupon-applied', { detail: data.summary }));
                        }
                    }
                } catch (e) {
                    this.couponError = 'Network error. Please try again.';
                } finally {
                    this.couponLoading = false;
                }
            },
        };
    }

    // -------------------------------------------------------
    // orderSummary() — reactive totals panel
    // -------------------------------------------------------
    function orderSummary() {
        return {
            subtotal: <?php echo e($subtotal ?? 0); ?>,
            shipping: <?php echo e($shipping ?? 0); ?>,
            tax:      <?php echo e($tax ?? 0); ?>,
            discount: <?php echo e($coupon_discount ?? 0); ?>,
            total:    <?php echo e($total ?? 0); ?>,

            init() {
                window.addEventListener('coupon-applied', (e) => {
                    const s = e.detail;
                    if (s.subtotal !== undefined) this.subtotal = s.subtotal;
                    if (s.shipping !== undefined) this.shipping = s.shipping;
                    if (s.discount !== undefined) this.discount = s.discount;
                    if (s.total    !== undefined) this.total    = s.total;
                });
            },

            formatINR(val) {
                return '\u20b9' + parseFloat(val || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
        };
    }
    </script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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
<?php /**PATH /var/www/html/resources/views/pages/checkout/index.blade.php ENDPATH**/ ?>