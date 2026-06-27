<nav x-data="navbar()"
      @scroll.window="isScrolled = (window.pageYOffset > 20)"
      class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
      :class="isScrolled ? 'bg-[#F5EBDD]/95 backdrop-blur-md shadow-md py-3' : 'bg-transparent py-5'">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="text-2xl font-bold text-[#6E0F12]" style="font-family: 'Playfair Display', serif;">
                Svaraa Jewels
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="nav-link text-[#2E1A12] hover:text-[#6E0F12] font-medium">Home</a>
                <a href="{{ route('products.index') }}" class="nav-link text-[#2E1A12] hover:text-[#6E0F12] font-medium">Earrings</a>
                <a href="{{ route('about') }}" class="nav-link text-[#2E1A12] hover:text-[#6E0F12] font-medium">About</a>
                <a href="{{ route('contact') }}" class="nav-link text-[#2E1A12] hover:text-[#6E0F12] font-medium">Contact</a>
            </div>

            <!-- Icons -->
            <div class="hidden md:flex items-center space-x-6">
                <!-- Search with Autocomplete -->
                <div class="relative" @click.outside="closeSearch">
                    <form action="{{ route('search.index') }}" method="GET" @submit="handleSubmit" class="relative">
                        <input type="text" 
                               name="q"
                               x-model="searchQuery"
                               @input.debounce.300ms="fetchSuggestions"
                               @keydown.escape="closeSearch"
                               @keydown.enter.prevent="submitSearch"
                               placeholder="Search jewelry..."
                               autocomplete="off"
                               class="w-48 lg:w-64 pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-full bg-white/90 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#C8A35D] transition-all"
                               :class="{'w-64': isSearchFocused || searchQuery.length > 0}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </form>
                    
                    <!-- Search Suggestions Dropdown -->
                    <div x-show="suggestions.length > 0 && !isSubmitting" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute right-0 mt-2 w-full bg-white rounded-xl shadow-lg border border-[#E8DCCB] max-h-96 overflow-y-auto z-50"
                         style="min-width: 20rem;">
                        <template x-for="suggestion in suggestions" :key="suggestion.id">
                            <a :href="suggestion.url" 
                               class="flex items-center gap-3 px-4 py-3 hover:bg-[#F5EBDD] transition-colors first:rounded-t-xl last:rounded-b-xl border-b border-[#E8DCCB] last:border-0">
                                <img :src="suggestion.image" :alt="suggestion.name" class="w-12 h-12 rounded-lg object-cover flex-shrink-0 bg-gray-100" onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-[#2E1A12] truncate" x-text="suggestion.name"></p>
                                    <p class="text-xs text-gray-500 truncate" x-text="suggestion.category"></p>
                                </div>
                                <span class="text-sm font-medium text-[#6E0F12]" x-text="suggestion.price"></span>
                            </a>
                        </template>
                    </div>
                </div>

                {{-- Wishlist icon - triggers sidebar --}}
                <button @click="$dispatch('open-wishlist')" class="icon-action text-[#2E1A12] hover:text-[#6E0F12] relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    @if(($wishlistCount ?? 0) > 0)
                        <span class="absolute -top-2 -right-2 bg-[#C8A35D] text-[#2E1A12] text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">{{ $wishlistCount }}</span>
                    @endif
                </button>
                {{-- Cart icon - triggers sidebar --}}
                <button @click="$store.cart.open = !$store.cart.open" class="icon-action text-[#2E1A12] hover:text-[#6E0F12] relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span x-show="$store.cart.count > 0" x-transition class="absolute -top-2 -right-2 bg-[#6E0F12] text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center" x-text="$store.cart.count"></span>
                </button>
            </div>

            <!-- Mobile Menu Button -->
            <button @click="isOpen = !isOpen" class="md:hidden text-[#2E1A12]">
                <svg x-show="!isOpen" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="isOpen" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <div x-show="isOpen" 
             x-transition:enter="transition-transform ease-out duration-300"
             x-transition:enter-start="transform -translate-y-full"
             x-transition:enter-end="transform translate-y-0"
             x-transition:leave="transition-transform ease-in duration-200"
             x-transition:leave-start="transform translate-y-0"
             x-transition:leave-end="transform -translate-y-full"
             class="md:hidden mt-4 py-6 bg-white rounded-lg shadow-lg">
            <div class="flex flex-col space-y-4 px-4">
                <a href="{{ route('home') }}" @click="isOpen = false" class="text-[#2E1A12] hover:text-[#6E0F12] transition-colors font-medium text-lg">Home</a>
                <a href="{{ route('products.index') }}" @click="isOpen = false" class="text-[#2E1A12] hover:text-[#6E0F12] transition-colors font-medium text-lg">Earrings</a>
                <a href="{{ route('about') }}" @click="isOpen = false" class="text-[#2E1A12] hover:text-[#6E0F12] transition-colors font-medium text-lg">About</a>
                <a href="{{ route('contact') }}" @click="isOpen = false" class="text-[#2E1A12] hover:text-[#6E0F12] transition-colors font-medium text-lg">Contact</a>
                
                <div class="flex items-center space-x-6 pt-4 border-t border-[#E8DCCB]">
                    <!-- Mobile Search -->
                    <div class="relative flex-1" @click.outside="closeSearch">
                        <form action="{{ route('search.index') }}" method="GET" @submit="handleSubmit" class="relative">
                            <input type="text" 
                                   name="q"
                                   x-model="searchQuery"
                                   @input.debounce.300ms="fetchSuggestions"
                                   @keydown.escape="closeSearch"
                                   placeholder="Search earrings..."
                                   autocomplete="off"
                                   class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-full bg-white focus:outline-none focus:ring-2 focus:ring-[#C8A35D]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </form>
                        
                        <!-- Mobile Search Suggestions -->
                        <div x-show="suggestions.length > 0 && !isSubmitting" 
                             x-cloak
                             class="absolute left-0 mt-2 w-full bg-white rounded-xl shadow-lg border border-[#E8DCCB] max-h-64 overflow-y-auto z-50">
                            <template x-for="suggestion in suggestions" :key="suggestion.id">
                                <a :href="suggestion.url" 
                                   class="flex items-center gap-3 px-4 py-3 hover:bg-[#F5EBDD] transition-colors border-b border-[#E8DCCB] last:border-0">
                                    <img :src="suggestion.image" :alt="suggestion.name" class="w-10 h-10 rounded-lg object-cover flex-shrink-0 bg-gray-100">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-[#2E1A12] truncate" x-text="suggestion.name"></p>
                                        <p class="text-xs text-gray-500 truncate" x-text="suggestion.category"></p>
                                    </div>
                                    <span class="text-sm font-medium text-[#6E0F12]" x-text="suggestion.price"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                    
                    <button @click="$dispatch('open-wishlist'); isOpen = false" class="text-[#2E1A12] hover:text-[#6E0F12] transition-colors relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        @if(($wishlistCount ?? 0) > 0)
                            <span class="absolute -top-2 -right-2 bg-[#C8A35D] text-[#2E1A12] text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">{{ $wishlistCount }}</span>
                        @endif
                    </button>
                    <button @click="$store.cart.open = !$store.cart.open; isOpen = false" class="text-[#2E1A12] hover:text-[#6E0F12] transition-colors relative">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <template x-if="$store.cart.count > 0">
                            <span class="absolute -top-2 -right-2 bg-[#6E0F12] text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center" x-text="$store.cart.count"></span>
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
function navbar() {
    return {
        isOpen: false,
        isScrolled: false,
        searchQuery: '',
        suggestions: [],
        isSubmitting: false,
        isSearchFocused: false,
        
        init() {
            this.checkScroll();
        },
        
        checkScroll() {
            this.isScrolled = window.pageYOffset > 20;
        },
        
        async fetchSuggestions() {
            if (this.searchQuery.length < 2) {
                this.suggestions = [];
                return;
            }
            try {
                const response = await fetch(`{{ route('search.suggest') }}?q=${encodeURIComponent(this.searchQuery)}`);
                this.suggestions = await response.json();
            } catch (e) {
                this.suggestions = [];
            }
        },
        
        closeSearch() {
            this.suggestions = [];
            this.isSearchFocused = false;
        },
        
        submitSearch() {
            this.isSubmitting = true;
            this.closeSearch();
        },
        
        handleSubmit() {
            if (this.searchQuery.trim().length >= 2) {
                this.submitSearch();
            }
        }
    }
}
</script>
