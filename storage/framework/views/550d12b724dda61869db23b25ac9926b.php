
<?php
    $seo   = $seo   ?? [];
    $title = $seo['title']          ?? (isset($pageTitle) ? $pageTitle . ' | ' . config('app.name') : config('app.name'));
    $desc  = $seo['description']    ?? 'Discover handcrafted luxury jewellery at ' . config('app.name') . '.';
    $canon = $seo['canonical']      ?? url()->current();
    $ogImg = $seo['og_image']       ?? asset('images/og-default.jpg');
    $ogT   = $seo['og_title']       ?? $title;
    $ogD   = $seo['og_description'] ?? $desc;
    $ogU   = $seo['og_url']         ?? $canon;
    $ogTy  = $seo['og_type']        ?? 'website';
    $jsonLd = $seo['json_ld']       ?? null;
?>

<title><?php echo e($title); ?></title>
<meta name="description" content="<?php echo e($desc); ?>">
<link rel="canonical" href="<?php echo e($canon); ?>">


<meta property="og:type"        content="<?php echo e($ogTy); ?>">
<meta property="og:title"       content="<?php echo e($ogT); ?>">
<meta property="og:description" content="<?php echo e($ogD); ?>">
<meta property="og:image"       content="<?php echo e($ogImg); ?>">
<meta property="og:url"         content="<?php echo e($ogU); ?>">
<meta property="og:site_name"   content="<?php echo e(config('app.name')); ?>">


<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?php echo e($ogT); ?>">
<meta name="twitter:description" content="<?php echo e($ogD); ?>">
<meta name="twitter:image"       content="<?php echo e($ogImg); ?>">


<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($jsonLd): ?>
<script type="application/ld+json">
<?php echo json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>

</script>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /var/www/html/resources/views/partials/seo-meta.blade.php ENDPATH**/ ?>