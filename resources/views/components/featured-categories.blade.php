<section id="collections" class="py-20">
    <div class="container mx-auto px-4">
        <!-- Signature Collection Section -->
        <div class="mt-0">
            <div class="section-reveal mb-12 text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#C8A35D]">Signature Collection</p>
                <h2 class="mt-4 text-3xl font-semibold text-[#2E1A12] md:text-4xl" style="font-family: 'Playfair Display', serif;">
                    Our Most Cherished Earrings
                </h2>
            </div>

            <div class="signature-grid">
                @php($signaturePieces = [
                    ['name' => 'Ember Drop Earrings', 'tag' => 'Limited', 'description' => 'Lightweight rose gold drops', 'colSpan' => 'col-span-12 md:col-span-4', 'rowSpan' => ''],
                    ['name' => 'Chandbali Classic', 'tag' => 'Best Seller', 'description' => 'Traditional jhumka reimagined', 'colSpan' => 'col-span-12 md:col-span-4', 'rowSpan' => ''],
                    ['name' => 'Hoops Deluxe', 'tag' => 'New', 'description' => 'Polished gold hoops for day to night', 'colSpan' => 'col-span-12 md:col-span-4', 'rowSpan' => ''],
                ])

                @foreach($signaturePieces as $piece)
                    <div class="signature-card {{ $piece['colSpan'] }} {{ $piece['rowSpan'] }} p-6 jewelry-detail" style="animation-delay: {{ $loop->index * 150 }}ms;">
                        <div class="absolute top-4 right-4">
                            <span class="rounded-full bg-[#F5EBDD] px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-[#6E0F12]">{{ $piece['tag'] }}</span>
                        </div>
                        
                        <div class="flex items-center justify-center h-40 mb-4">
                            <div class="relative">
                                <div class="absolute inset-0 rounded-full bg-[#C8A35D]/20 blur-2xl"></div>
                                <div class="relative flex h-24 w-24 items-center justify-center rounded-full border border-[#d8bf8c] bg-white/70 text-[#6E0F12] jewelry-accent">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.077 9.911c-.783-.57-.38-1.81.588-1.81h4.915a1 1 0 00.95-.69l1.519-4.674z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-xl font-semibold text-[#2E1A12] text-center" style="font-family: 'Playfair Display', serif;">{{ $piece['name'] }}</h3>
                        <p class="mt-2 text-sm text-[#7B6755] text-center">{{ $piece['description'] }}</p>
                        
                        <div class="mt-4 flex justify-center">
                            <a href="{{ route('products.index') }}" class="cta-pill rounded-full bg-[#6E0F12] px-5 py-2 text-sm font-semibold text-white hover:bg-[#4f0b0e] luxury-cta">
                                View Details
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Best Sellers & Brand Story -->
        <div class="mt-24 grid gap-8 lg:grid-cols-[1.05fr_0.95fr]">
            <div class="section-shell section-reveal p-8 md:p-10">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#C8A35D]">Best sellers</p>
                <h3 class="mt-3 text-3xl font-semibold text-[#2E1A12]" style="font-family: 'Playfair Display', serif;">Designed for everyday shine and easy gifting.</h3>
                <div class="no-scrollbar mt-8 flex gap-5 overflow-x-auto pb-2">
                    @php($featured = [
                        ['name' => 'Ember Drop Earrings', 'price' => '₹16,200', 'note' => 'Rose gold', 'badge' => 'Best seller'],
                        ['name' => 'Chandbali Classic', 'price' => '₹22,800', 'note' => 'Gold-plated', 'badge' => 'New'],
                        ['name' => 'Hoops Deluxe', 'price' => '₹18,500', 'note' => 'Polished gold', 'badge' => 'Popular'],
                    ])
                    @foreach($featured as $piece)
                        <div class="product-card card-tilt group min-w-[260px] max-w-[260px] rounded-[1.5rem] border border-[#e8dccb] bg-[#fffdf9] p-5 shadow-sm product-premium" style="animation-delay: {{ $loop->index * 120 }}ms;">
                            <div class="flex items-center justify-between">
                                <span class="rounded-full bg-[#F5EBDD] px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-[#6E0F12]">{{ $piece['badge'] }}</span>
                                <span class="text-sm font-semibold text-[#C8A35D]">★ 4.9</span>
                            </div>
                            <div class="product-image-container product-visual mt-5 rounded-[1.25rem] bg-[linear-gradient(135deg,_rgba(240,223,180,0.4),_rgba(255,248,239,1))] p-5">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border border-[#d8bf8c] bg-white/70 text-[#6E0F12]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.077 9.911c-.783-.57-.38-1.81.588-1.81h4.915a1 1 0 00.95-.69l1.519-4.674z" />
                                    </svg>
                                </div>
                            </div>
                            <h4 class="mt-4 text-xl font-semibold text-[#2E1A12]" style="font-family: 'Playfair Display', serif;">{{ $piece['name'] }}</h4>
                            <p class="mt-2 text-sm text-[#7B6755]">{{ $piece['note'] }}</p>
                            <div class="product-cta mt-5 flex items-center justify-between">
                                <span class="text-lg font-semibold text-[#6E0F12]">{{ $piece['price'] }}</span>
                                <div class="flex gap-2">
                                    <a href="{{ route('products.index') }}" class="icon-action rounded-full border border-[#e8dccb] bg-white px-3 py-2 text-sm font-semibold text-[#6E0F12] hover:bg-[#F5EBDD]">Quick view</a>
                                    <a href="{{ route('products.index') }}" class="wishlist-badge rounded-full border border-[#e8dccb] bg-white px-3 py-2 text-sm font-semibold text-[#6E0F12] hover:bg-[#F5EBDD]">♡</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-6">
                <div class="section-shell section-reveal p-8 md:p-10">
                    <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#C8A35D]">Brand story</p>
                    <h3 class="mt-3 text-3xl font-semibold text-[#2E1A12]" style="font-family: 'Playfair Display', serif;">Simple pieces made to feel personal and lasting.</h3>
                    <p class="mt-4 text-lg leading-8 text-[#7B6755]">We design jewelry that balances modern shape with a soft, comfortable finish so it works beautifully from day to evening.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('about') }}" class="cta-pill rounded-full bg-[#6E0F12] px-5 py-3 text-sm font-semibold text-white hover:bg-[#4f0b0e] luxury-cta">Explore Designs</a>
                        <a href="https://instagram.com/svaraa.jewels_" target="_blank" class="cta-pill rounded-full border border-[#C8A35D] bg-white/80 px-5 py-3 text-sm font-semibold text-[#6E0F12] hover:bg-[#F5EBDD] luxury-cta">Follow on Instagram</a>
                    </div>
                </div>

                <div class="section-shell section-reveal p-8 md:p-10">
                    <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#C8A35D]">Customer love</p>
                    <div class="mt-4 rounded-[1.25rem] border border-[#e8dccb] bg-[#fffdf9] p-6 shadow-sm">
                        <p class="text-lg leading-8 text-[#7B6755]">“The finish feels effortless and the styling is so simple. It looks beautiful whether I wear it all day or for dinner.”</p>
                        <div class="mt-5 flex items-center justify-between text-sm text-[#6d5646]">
                            <span>— Asha, Delhi</span>
                            <span class="font-semibold text-[#6E0F12]">Rated 5/5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instagram Section -->
        <div class="mt-24">
            <div class="section-reveal mb-12 text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-[#C8A35D]">@svaraa.jewels_</p>
                <h2 class="mt-4 text-3xl font-semibold text-[#2E1A12] md:text-4xl" style="font-family: 'Playfair Display', serif;">
                    Join Our Community
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-[#7B6755]">
                    Share your Svaraa moments and be part of our story.
                </p>
            </div>

            <div class="instagram-grid">
                @for($i = 1; $i <= 8; $i++)
                    <a href="https://instagram.com/svaraa.jewels_" target="_blank" class="instagram-item" style="animation-delay: {{ ($i - 1) * 80 }}ms;">
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#F5EBDD] to-[#d8bf8c]">
                            <span class="text-[#6E0F12] text-4xl">✦</span>
                        </div>
                        <div class="instagram-overlay">
                            <div class="text-white text-center">
                                <div class="flex items-center justify-center gap-4">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                        </svg>
                                        2.4k
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endfor
            </div>
        </div>
    </div>
</section>
