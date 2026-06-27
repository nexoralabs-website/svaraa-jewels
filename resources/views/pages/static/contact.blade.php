<x-layouts.app>
    <x-slot:title>Contact Us</x-slot:title>

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
                    <a href="{{ route('products.index') }}" class="rounded-full bg-[#6E0F12] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#4f0b0e]">Shop Earrings</a>
                    <a href="{{ route('about') }}" class="rounded-full border border-[#C8A35D] bg-white/80 px-5 py-3 text-sm font-semibold text-[#6E0F12] transition hover:bg-[#F5EBDD]">Explore Designs</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
