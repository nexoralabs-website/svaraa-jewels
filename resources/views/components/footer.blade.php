<footer class="bg-[#2E1A12] py-12 text-[#E8DCCB]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-10 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-md">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#C8A35D]">Svaraa Jewels</p>
                <h3 class="mt-3 text-3xl font-semibold text-white" style="font-family: 'Playfair Display', serif;">Modern earrings for everyday shine.</h3>
                <p class="mt-4 text-sm leading-7 text-[#f4e2c2]">Find earrings designed to feel polished, simple, and lasting.</p>

                <div class="mt-6 space-y-2 text-sm text-[#f4e2c2]">
                    <a href="tel:+917339559072" class="footer-link block transition hover:text-white">Phone: 7339559072</a>
                    <a href="mailto:svaraajewelry@gmail.com" class="footer-link block transition hover:text-white">Email: svaraajewelry@gmail.com</a>
                    <a href="https://instagram.com/svaraa.jewels_" target="_blank" rel="noopener noreferrer" class="footer-link block transition hover:text-white">Instagram: @svaraa.jewels_</a>
                </div>
            </div>

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-[0.25em] text-[#C8A35D]">Explore</h4>
                    <ul class="mt-4 space-y-3 text-sm text-[#f4e2c2]">
                        <li><a href="{{ route('home') }}" class="footer-link transition hover:text-white">Home</a></li>
                        <li><a href="{{ route('products.index') }}" class="footer-link transition hover:text-white">Shop</a></li>
                        <li><a href="{{ route('about') }}" class="footer-link transition hover:text-white">About</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-[0.25em] text-[#C8A35D]">Support</h4>
                    <ul class="mt-4 space-y-3 text-sm text-[#f4e2c2]">
                        <li><a href="{{ route('contact') }}" class="footer-link transition hover:text-white">Contact</a></li>
                        <li><a href="{{ route('shipping-policy') }}" class="footer-link transition hover:text-white">Shipping</a></li>
                        <li><a href="{{ route('refund-policy') }}" class="footer-link transition hover:text-white">Returns</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-[0.25em] text-[#C8A35D]">Policies</h4>
                    <ul class="mt-4 space-y-3 text-sm text-[#f4e2c2]">
                        <li><a href="{{ route('terms') }}" class="footer-link transition hover:text-white">Terms & Conditions</a></li>
                        <li><a href="{{ route('privacy-policy') }}" class="footer-link transition hover:text-white">Privacy Policy</a></li>
                        <li><a href="{{ route('shipping-policy') }}" class="footer-link transition hover:text-white">Shipping Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="mt-10 border-t border-[#C8A35D]/30 pt-6 text-center text-sm text-[#f4e2c2]">
            © {{ date('Y') }} Svaraa Jewels. All rights reserved.
        </div>
    </div>
</footer>
