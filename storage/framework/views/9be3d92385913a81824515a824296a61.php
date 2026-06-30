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

     <?php $__env->slot('title', null, []); ?> Refund Policy <?php $__env->endSlot(); ?>

    <section class="px-4 py-20">
        <div class="container mx-auto max-w-4xl">
            <div class="section-shell p-8 md:p-12">
                <h1 class="text-4xl font-semibold text-[#2E1A12] md:text-5xl" style="font-family: 'Playfair Display', serif;">Refund Policy</h1>
                <p class="mt-6 text-lg leading-8 text-[#7B6755]">We want you to feel confident with every purchase, and support is available if a piece needs attention.</p>
                <div class="mt-8 space-y-4 text-sm leading-7 text-[#6d5646]">
                    <p>Returns are accepted for unused products within the stated return window.</p>
                    <p>Custom or personalized orders may be non-returnable unless damaged or incorrect.</p>
                    <p>Please contact us promptly if your order arrives damaged or incomplete.</p>
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
<?php /**PATH /var/www/html/resources/views/pages/static/refund.blade.php ENDPATH**/ ?>