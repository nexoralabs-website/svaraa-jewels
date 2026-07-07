<x-layouts.app>
    <x-slot:title>{{ $query ? 'Search: ' . $query : 'Search' }} | Svaraa Jewels</x-slot:title>

    <div class="bg-[#FDFBF7] py-10 border-b border-[#E8DCCB]/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-4xl font-serif text-[#2E1A12]">
                {{ $query ? 'Results for "' . $query . '"' : 'Search' }}
            </h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Search bar --}}
        <div class="max-w-2xl mx-auto mb-10" x-data="searchPage()">
            <form action="{{ route('search.index') }}" method="GET"
                  class="relative" @submit.prevent="doSubmit">
                <input type="text"
                       name="q"
                       x-model="term"
                       @input.debounce.300ms="fetchSuggestions()"
                       @keydown.escape="suggestions = []"
                       placeholder="Search jewellery..."
                       autocomplete="off"
                       class="w-full rounded-xl border border-[#E8DCCB] bg-white px-5 py-3.5 pr-14
                              text-base shadow-sm focus:border-[#C8A35D] focus:outline-none
                              focus:ring-2 focus:ring-[#C8A35D]/30 transition-colors">
                <button type="submit"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg
                               bg-[#C8A35D] px-4 py-2 text-white hover:bg-[#b8934d] transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
                    </svg>
                </button>

                {{-- Suggestions dropdown --}}
                <div x-show="suggestions.length > 0 && !submitting" x-cloak
                     class="absolute z-50 mt-1 w-full rounded-xl border border-[#E8DCCB] bg-white shadow-xl overflow-hidden">
                    <template x-for="s in suggestions" :key="s.id">
                        <a :href="s.url"
                           class="flex items-center gap-3 px-4 py-3 hover:bg-[#F5EBDD] transition-colors
                                  border-b border-[#E8DCCB] last:border-0">
                            <img :src="s.image" :alt="s.name"
                                 class="h-10 w-10 rounded-lg object-cover flex-shrink-0 bg-gray-100"
                                 onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-[#2E1A12] truncate" x-text="s.name"></p>
                                <p class="text-xs text-gray-500 truncate" x-text="s.category"></p>
                            </div>
                            <span class="text-sm font-medium text-[#6E0F12] whitespace-nowrap"
                                  x-text="s.price"></span>
                        </a>
                    </template>
                </div>
            </form>
        </div>

        @if($query)
        <p class="mb-8 text-sm text-gray-500 text-center">
            @if($products->total() > 0)
                {{ number_format($products->total()) }} result{{ $products->total() !== 1 ? 's' : '' }}
                for <strong class="text-[#2E1A12]">"{{ $query }}"</strong>
            @else
                No results for <strong class="text-[#2E1A12]">"{{ $query }}"</strong>.
                Try a different search term.
            @endif
        </p>
        @endif

        @if($products->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
            <a href="{{ route('products.show', $product->slug) }}"
               class="group bg-white rounded-xl border border-[#E8DCCB] overflow-hidden
                      shadow-sm hover:shadow-md hover:border-[#C8A35D]/50 transition-all">
                <div class="aspect-square overflow-hidden bg-gray-50">
                    <img src="{{ $product->thumbnail_url }}"
                         alt="{{ $product->name }}"
                         class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                         loading="lazy">
                </div>
                <div class="p-4">
                    <p class="text-xs text-[#C8A35D] font-medium mb-1 uppercase tracking-wider">
                        {{ $product->category?->name }}
                    </p>
                    <p class="text-sm font-serif font-medium text-[#2E1A12] line-clamp-2 leading-tight">
                        {{ $product->name }}
                    </p>
                    <p class="mt-2 text-sm font-semibold text-[#6E0F12]">
                        &#8377;{{ number_format($product->price, 2) }}
                    </p>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-12 border-t border-gray-100 pt-8">
            {{ $products->appends(['q' => $query])->links() }}
        </div>

        @elseif($query)
        {{-- Empty state --}}
        <div class="text-center py-24">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="text-xl font-serif text-[#2E1A12] mb-2">No results found</h3>
            <p class="text-gray-500 mb-6">Try searching with different keywords or browse our collections.</p>
            <a href="{{ route('products.index') }}"
               class="inline-block bg-[#C8A35D] text-white px-8 py-3 rounded-full font-medium
                      hover:bg-[#b8934d] transition-colors">
                Browse All Earrings
            </a>
        </div>
        @endif
    </div>

    <script>
    function searchPage() {
        return {
            term: '{{ addslashes($query ?? '') }}',
            suggestions: [],
            submitting: false,
            async fetchSuggestions() {
                if (this.term.length < 2) { this.suggestions = []; return; }
                try {
                    const res = await fetch('{{ route('search.suggest') }}?q=' + encodeURIComponent(this.term));
                    this.suggestions = await res.json();
                } catch(e) { this.suggestions = []; }
            },
            doSubmit(e) {
                this.submitting = true;
                this.suggestions = [];
                e.target.submit();
            },
        };
    }
    </script>
</x-layouts.app>
