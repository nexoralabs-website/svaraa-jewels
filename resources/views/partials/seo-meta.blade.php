{{--
    Usage in layouts/app.blade.php inside <head>:
    @include('partials.seo-meta')

    Pass $seo array from controllers using SeoService, e.g.:
    $seo = app(SeoService::class)->forProduct($product);
    or defaults are used.
--}}
@php
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
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $desc }}">
<link rel="canonical" href="{{ $canon }}">

{{-- Open Graph --}}
<meta property="og:type"        content="{{ $ogTy }}">
<meta property="og:title"       content="{{ $ogT }}">
<meta property="og:description" content="{{ $ogD }}">
<meta property="og:image"       content="{{ $ogImg }}">
<meta property="og:url"         content="{{ $ogU }}">
<meta property="og:site_name"   content="{{ config('app.name') }}">

{{-- Twitter Card --}}
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="{{ $ogT }}">
<meta name="twitter:description" content="{{ $ogD }}">
<meta name="twitter:image"       content="{{ $ogImg }}">

{{-- JSON-LD Structured Data --}}
@if($jsonLd)
<script type="application/ld+json">
{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endif
