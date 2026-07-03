<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{--
        SEO Meta Tags.

        Pages push full SEO into the 'seo' stack via:
            @push('seo')
                <x-seo-meta :title="..." :description="..." ... />
            @endpush
        x-seo-meta renders <title>, description, canonical, OG, Twitter, JSON-LD.

        Pages that do NOT push custom SEO still get a <title> from the
        <x-slot:title> prop (rendered below the stack), plus a generic description.

        IMPORTANT: when a page DOES push x-seo-meta, that component already
        renders a <title>. The <title> below becomes a duplicate. Browsers
        use the FIRST <title> encountered in <head>, so the stack output wins.
        The duplicate generic title is suppressed by x-seo-meta setting the
        Blade section '__seo_pushed__', which we check with @hasSection.
    --}}
    @stack('seo')

    @hasSection('__seo_pushed__')
    {{-- x-seo-meta already rendered title + description via the stack above --}}
    @else
    <title>{{ $title ?? 'Svaraa Jewels' }} | Timeless Elegance</title>
    <meta name="description" content="Discover handcrafted luxury earrings at Svaraa Jewels. Everyday elegance and special moments, crafted for you.">
    @endif

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
