<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($title ?? 'Svaraa Jewels'); ?> | Timeless Elegance</title>

    <!-- SEO Meta Tags -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($seo)): ?>
    <meta name="description" content="<?php echo e($seo['description'] ?? ''); ?>">
    <link rel="canonical" href="<?php echo e($seo['canonical'] ?? url()->current()); ?>">
    <meta property="og:type"        content="<?php echo e($seo['og_type']        ?? 'website'); ?>">
    <meta property="og:title"       content="<?php echo e($seo['og_title']       ?? ($title ?? 'Svaraa Jewels')); ?>">
    <meta property="og:description" content="<?php echo e($seo['og_description'] ?? ''); ?>">
    <meta property="og:image"       content="<?php echo e($seo['og_image']       ?? asset('images/og-default.jpg')); ?>">
    <meta property="og:url"         content="<?php echo e($seo['og_url']         ?? url()->current()); ?>">
    <meta property="og:site_name"   content="<?php echo e(config('app.name')); ?>">
    <meta name="twitter:card"       content="summary_large_image">
    <meta name="twitter:title"      content="<?php echo e($seo['og_title']       ?? ($title ?? 'Svaraa Jewels')); ?>">
    <meta name="twitter:description" content="<?php echo e($seo['og_description'] ?? ''); ?>">
    <meta name="twitter:image"      content="<?php echo e($seo['og_image']       ?? asset('images/og-default.jpg')); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($seo['json_ld'])): ?>
    <script type="application/ld+json"><?php echo json_encode($seo['json_ld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
    <meta name="description" content="Discover handcrafted luxury earrings at Svaraa Jewels. Everyday elegance and special moments, crafted for you.">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|inter:400,500,600" rel="stylesheet" />

    <!-- Styles & Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <script>
        window.cartCount = <?php echo e($cartCount ?? 0); ?>;
        window.cartItems = <?php echo json_encode($alpineCartItems ?? [], 15, 512) ?>;
        window.routes = {
            cartAdd: '<?php echo e(route('cart.add')); ?>',
            cartUpdate: '<?php echo e(route('cart.update', ['id' => '**ID**'])); ?>',
            cartRemove: '<?php echo e(route('cart.remove', ['id' => '**ID**'])); ?>',
            cartIndex: '<?php echo e(route('cart.index')); ?>',
            cartClear: '<?php echo e(route('cart.clear')); ?>',
            checkoutIndex: '<?php echo e(route('checkout.index')); ?>',
            productsIndex: '<?php echo e(route('products.index')); ?>'
        };
        <?php if(isset($extraHead)): ?>
            <?php echo $extraHead; ?>

        <?php endif; ?>
    </script>
</head>
<body class="min-h-screen bg-[#F5EBDD]">
    <!-- Navbar -->
    <?php if (isset($component)) { $__componentOriginala591787d01fe92c5706972626cdf7231 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala591787d01fe92c5706972626cdf7231 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.navbar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('navbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala591787d01fe92c5706972626cdf7231)): ?>
<?php $attributes = $__attributesOriginala591787d01fe92c5706972626cdf7231; ?>
<?php unset($__attributesOriginala591787d01fe92c5706972626cdf7231); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala591787d01fe92c5706972626cdf7231)): ?>
<?php $component = $__componentOriginala591787d01fe92c5706972626cdf7231; ?>
<?php unset($__componentOriginala591787d01fe92c5706972626cdf7231); ?>
<?php endif; ?>

    <!-- Main Content -->
    <main class="pt-24">
        <?php echo e($slot); ?>

    </main>

    <!-- Sidebars -->
    <?php if (isset($component)) { $__componentOriginal3ba6d5857760cef0e67a54ddc4423557 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3ba6d5857760cef0e67a54ddc4423557 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.cart.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('cart.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3ba6d5857760cef0e67a54ddc4423557)): ?>
<?php $attributes = $__attributesOriginal3ba6d5857760cef0e67a54ddc4423557; ?>
<?php unset($__attributesOriginal3ba6d5857760cef0e67a54ddc4423557); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3ba6d5857760cef0e67a54ddc4423557)): ?>
<?php $component = $__componentOriginal3ba6d5857760cef0e67a54ddc4423557; ?>
<?php unset($__componentOriginal3ba6d5857760cef0e67a54ddc4423557); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal14b3e30bc84fe47d1a59cb1b1ce0749c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal14b3e30bc84fe47d1a59cb1b1ce0749c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.wishlist.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('wishlist.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal14b3e30bc84fe47d1a59cb1b1ce0749c)): ?>
<?php $attributes = $__attributesOriginal14b3e30bc84fe47d1a59cb1b1ce0749c; ?>
<?php unset($__attributesOriginal14b3e30bc84fe47d1a59cb1b1ce0749c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal14b3e30bc84fe47d1a59cb1b1ce0749c)): ?>
<?php $component = $__componentOriginal14b3e30bc84fe47d1a59cb1b1ce0749c; ?>
<?php unset($__componentOriginal14b3e30bc84fe47d1a59cb1b1ce0749c); ?>
<?php endif; ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>

    <!-- Footer -->
    <?php if (isset($component)) { $__componentOriginal8a8716efb3c62a45938aca52e78e0322 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a8716efb3c62a45938aca52e78e0322 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a8716efb3c62a45938aca52e78e0322)): ?>
<?php $attributes = $__attributesOriginal8a8716efb3c62a45938aca52e78e0322; ?>
<?php unset($__attributesOriginal8a8716efb3c62a45938aca52e78e0322); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a8716efb3c62a45938aca52e78e0322)): ?>
<?php $component = $__componentOriginal8a8716efb3c62a45938aca52e78e0322; ?>
<?php unset($__componentOriginal8a8716efb3c62a45938aca52e78e0322); ?>
<?php endif; ?>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/components/layouts/app.blade.php ENDPATH**/ ?>