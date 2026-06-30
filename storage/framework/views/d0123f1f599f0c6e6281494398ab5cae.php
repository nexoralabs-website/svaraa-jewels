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

     <?php $__env->slot('title', null, []); ?> Terms & Conditions <?php $__env->endSlot(); ?>

    <section class="px-4 py-20">
        <div class="container mx-auto max-w-4xl">
            <div class="section-shell p-8 md:p-12">
                <h1 class="text-4xl font-semibold text-[#2E1A12] md:text-5xl" style="font-family: 'Playfair Display', serif;">Terms & Conditions</h1>
                <p class="mt-6 text-lg leading-8 text-[#7B6755]">By shopping with Svaraa Jewels, you agree to our store policies, product availability, and purchase terms.</p>
                <div class="mt-8 space-y-4 text-sm leading-7 text-[#6d5646]">
                    <p>1. All orders are subject to availability and confirmation.</p>
                    <p>2. Prices may change without prior notice, though confirmed orders will be honored.</p>
                    <p>3. Delivery timelines are estimates and may vary by location.</p>
                    <p>4. Customers are encouraged to review product details before purchase.</p>
                </div>
            </div>
        </div>
    </section>
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
<?php /**PATH /var/www/html/resources/views/pages/static/terms.blade.php ENDPATH**/ ?>