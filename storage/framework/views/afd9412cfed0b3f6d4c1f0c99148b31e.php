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

     <?php $__env->slot('title', null, []); ?> About Us <?php $__env->endSlot(); ?>

    <section class="px-4 py-20">
        <div class="container mx-auto max-w-5xl">
            <div class="section-shell p-8 md:p-12">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#C8A35D]">About Svaraa</p>
                <h1 class="mt-4 text-4xl font-semibold text-[#2E1A12] md:text-5xl" style="font-family: 'Playfair Display', serif;">Jewelry that feels like a treasured story.</h1>
                <p class="mt-6 text-lg leading-8 text-[#7B6755]">Svaraa Jewels creates modern heirlooms with a deeply personal sensibility. Every design is thoughtfully composed to feel luminous, comfortable, and meaningful enough to be worn for years to come.</p>

                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    <div class="rounded-[1.25rem] border border-[#e8dccb] bg-[#fffdf9] p-6">
                        <h2 class="text-xl font-semibold text-[#2E1A12]" style="font-family: 'Playfair Display', serif;">Craftsmanship</h2>
                        <p class="mt-3 text-sm leading-7 text-[#7B6755]">We focus on refined detailing, premium finishes, and pieces that balance modern simplicity with rich character.</p>
                    </div>
                    <div class="rounded-[1.25rem] border border-[#e8dccb] bg-[#fffdf9] p-6">
                        <h2 class="text-xl font-semibold text-[#2E1A12]" style="font-family: 'Playfair Display', serif;">Meaning</h2>
                        <p class="mt-3 text-sm leading-7 text-[#7B6755]">Our jewelry is designed for life’s milestones, from engagements and anniversaries to everyday quiet luxury.</p>
                    </div>
                    <div class="rounded-[1.25rem] border border-[#e8dccb] bg-[#fffdf9] p-6">
                        <h2 class="text-xl font-semibold text-[#2E1A12]" style="font-family: 'Playfair Display', serif;">Service</h2>
                        <p class="mt-3 text-sm leading-7 text-[#7B6755]">An attentive concierge experience helps you find pieces that suit your style, gifting needs, and celebration plans.</p>
                    </div>
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
<?php /**PATH /var/www/html/resources/views/pages/static/about.blade.php ENDPATH**/ ?>