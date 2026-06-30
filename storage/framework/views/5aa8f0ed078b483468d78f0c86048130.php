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

     <?php $__env->slot('title', null, []); ?> My Account | Svaraa Jewels <?php $__env->endSlot(); ?>

    <div class="bg-[#FFFFF0] py-12 border-b border-[#C8A35D]/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-serif text-[#6E0F12]">My Account</h1>
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
            <div class="w-full lg:w-3/4 space-y-8">
                <!-- Profile Details -->
                <div class="bg-white p-8 border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-serif text-[#6E0F12]">Profile Details</h2>
                        <a href="<?php echo e(route('profile.edit')); ?>" class="text-sm text-[#C8A35D] hover:text-[#6E0F12] font-medium transition-colors">Edit Profile</a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-widest mb-1">Full Name</p>
                            <p class="font-medium text-gray-900"><?php echo e($user->name); ?></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-widest mb-1">Email Address</p>
                            <p class="font-medium text-gray-900"><?php echo e($user->email); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white p-8 border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-serif text-[#6E0F12]">Recent Orders</h2>
                        <a href="<?php echo e(route('account.orders')); ?>" class="text-sm text-[#C8A35D] hover:text-[#6E0F12] font-medium transition-colors">View All</a>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentOrders->count() > 0): ?>
                        <div class="space-y-4">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $statusValue = $order->order_status?->value ?? $order->order_status;
                                    $statusClass = in_array($statusValue, ['completed', 'delivered', 'shipped']) ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
                                ?>
                                <div class="flex items-center justify-between p-4 border border-gray-100 rounded hover:border-[#C8A35D]/50 transition-colors">
                                    <div>
                                        <p class="font-medium text-gray-900 mb-1"><?php echo e($order->order_number); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo e($order->created_at->format('M d, Y')); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-[#C8A35D] mb-1">₹<?php echo e(number_format($order->total, 2)); ?></p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($statusClass); ?>">
                                            <?php echo e(ucfirst((string) $statusValue)); ?>

                                        </span>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500">You haven't placed any orders yet.</p>
                            <a href="<?php echo e(route('products.index')); ?>" class="inline-block mt-4 text-[#C8A35D] hover:text-[#6E0F12] font-medium">Start Shopping</a>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Saved Addresses -->
                <div class="bg-white p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-xl font-serif text-[#6E0F12] mb-6">Saved Addresses</h2>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($addresses->count() > 0): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $addresses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $address): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="p-4 border border-gray-100 rounded">
                                    <h4 class="font-medium text-gray-900 mb-2"><?php echo e($address->full_name); ?></h4>
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        <?php echo e($address->address_line); ?><br>
                                        <?php echo e($address->city); ?>, <?php echo e($address->state); ?> <?php echo e($address->pincode); ?><br>
                                        <?php echo e($address->country); ?>

                                    </p>
                                    <p class="text-sm text-gray-500 mt-2">Phone: <?php echo e($address->phone); ?></p>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">You haven't saved any addresses yet. Add one during your next checkout.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH /var/www/html/resources/views/pages/account/index.blade.php ENDPATH**/ ?>