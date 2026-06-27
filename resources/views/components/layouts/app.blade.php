<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Svaraa Jewels' }} | Timeless Elegance</title>

    <!-- SEO Meta Tags -->
    @isset($seo)
    <meta name="description" content="{{ $seo['description'] ?? '' }}">
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">
    <meta property="og:type"        content="{{ $seo['og_type']        ?? 'website' }}">
    <meta property="og:title"       content="{{ $seo['og_title']       ?? ($title ?? 'Svaraa Jewels') }}">
    <meta property="og:description" content="{{ $seo['og_description'] ?? '' }}">
    <meta property="og:image"       content="{{ $seo['og_image']       ?? asset('images/og-default.jpg') }}">
    <meta property="og:url"         content="{{ $seo['og_url']         ?? url()->current() }}">
    <meta property="og:site_name"   content="{{ config('app.name') }}">
    <meta name="twitter:card"       content="summary_large_image">
    <meta name="twitter:title"      content="{{ $seo['og_title']       ?? ($title ?? 'Svaraa Jewels') }}">
    <meta name="twitter:description" content="{{ $seo['og_description'] ?? '' }}">
    <meta name="twitter:image"      content="{{ $seo['og_image']       ?? asset('images/og-default.jpg') }}">
    @if(!empty($seo['json_ld']))
    <script type="application/ld+json">{!! json_encode($seo['json_ld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endif
    @else
    <meta name="description" content="Discover handcrafted luxury earrings at Svaraa Jewels. Everyday elegance and special moments, crafted for you.">
    @endisset

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|inter:400,500,600" rel="stylesheet" />

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        window.cartCount = {{ $cartCount ?? 0 }};
        window.cartItems = @json($alpineCartItems ?? []);
        window.routes = {
            cartAdd: '{{ route('cart.add') }}',
            cartUpdate: '{{ route('cart.update', ['id' => '**ID**']) }}',
            cartRemove: '{{ route('cart.remove', ['id' => '**ID**']) }}',
            cartIndex: '{{ route('cart.index') }}',
            cartClear: '{{ route('cart.clear') }}',
            checkoutIndex: '{{ route('checkout.index') }}',
            productsIndex: '{{ route('products.index') }}'
        };
        @isset($extraHead)
            {!! $extraHead !!}
        @endisset
    </script>
</head>
<body class="min-h-screen bg-[#F5EBDD]">
    <!-- Navbar -->
    <x-navbar />

    <!-- Main Content -->
    <main class="pt-24">
        {{ $slot }}
    </main>

    <!-- Sidebars -->
    <x-cart.sidebar />
    <x-wishlist.sidebar />

    @stack('scripts')

    <!-- Footer -->
    <x-footer />
</body>
</html>
