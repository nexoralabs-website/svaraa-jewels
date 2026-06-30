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

     <?php $__env->slot('title', null, []); ?> Contact Us <?php $__env->endSlot(); ?>

    <section class="px-4 py-20">
        <div class="container mx-auto max-w-5xl">
            <div class="section-shell p-8 md:p-12">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#C8A35D]">Contact Svaraa</p>
                <h1 class="mt-4 text-4xl font-semibold text-[#2E1A12] md:text-5xl" style="font-family: 'Playfair Display', serif;">Reach out for new arrivals, styling ideas, or help finding a piece.</h1>
                <p class="mt-6 text-lg leading-8 text-[#7B6755]">Whether you are shopping for everyday wear or a special occasion, our team is happy to help you find something that feels right.</p>

                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    <a href="tel:+917339559072" class="rounded-[1.25rem] border border-[#e8dccb] bg-[#fffdf9] p-6 transition hover:-translate-y-1 hover:shadow-md">
                        <h2 class="text-xl font-semibold text-[#2E1A12]" style="font-family: 'Playfair Display', serif;">Call</h2>
                        <p class="mt-3 text-sm leading-7 text-[#7B6755]">7339559072</p>
                    </a>
                    <a href="mailto:svaraajewelry@gmail.com" class="rounded-[1.25rem] border border-[#e8dccb] bg-[#fffdf9] p-6 transition hover:-translate-y-1 hover:shadow-md">
                        <h2 class="text-xl font-semibold text-[#2E1A12]" style="font-family: 'Playfair Display', serif;">Email</h2>
                        <p class="mt-3 text-sm leading-7 text-[#7B6755]">svaraajewelry@gmail.com</p>
                    </a>
                    <a href="https://instagram.com/svaraa.jewels_" target="_blank" rel="noopener noreferrer" class="rounded-[1.25rem] border border-[#e8dccb] bg-[#fffdf9] p-6 transition hover:-translate-y-1 hover:shadow-md">
                        <h2 class="text-xl font-semibold text-[#2E1A12]" style="font-family: 'Playfair Display', serif;">Instagram</h2>
                        <p class="mt-3 text-sm leading-7 text-[#7B6755]">@svaraa.jewels_</p>
                    </a>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="<?php echo e(route('products.index')); ?>" class="rounded-full bg-[#6E0F12] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#4f0b0e]">Shop Earrings</a>
                    <a href="<?php echo e(route('about')); ?>" class="rounded-full border border-[#C8A35D] bg-white/80 px-5 py-3 text-sm font-semibold text-[#6E0F12] transition hover:bg-[#F5EBDD]">Explore Designs</a>
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
<?php /**PATH /var/www/html/resources/views/pages/static/contact.blade.php ENDPATH**/ ?>