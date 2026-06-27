@props(['currentSort' => 'newest'])

<div x-data="{ open: false }" class="relative z-20">
    <button 
        @click="open = !open" 
        @click.away="open = false"
        class="flex items-center gap-2 border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white hover:border-[#C8A35D] transition-colors"
    >
        Sort by: 
        <span class="text-[#6E0F12]">
            @if($currentSort === 'price-low') Price: Low to High
            @elseif($currentSort === 'price-high') Price: High to Low
            @else Latest
            @endif
        </span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 ml-1 transition-transform" :class="{'rotate-180': open}">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 shadow-xl"
        style="display: none;"
    >
        <div class="py-1">
            <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#6E0F12] hover:text-white transition-colors {{ $currentSort === 'newest' ? 'bg-gray-50 font-medium' : '' }}">
                Latest
            </a>
            <a href="{{ request()->fullUrlWithQuery(['sort' => 'price-low']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#6E0F12] hover:text-white transition-colors {{ $currentSort === 'price-low' ? 'bg-gray-50 font-medium' : '' }}">
                Price: Low to High
            </a>
            <a href="{{ request()->fullUrlWithQuery(['sort' => 'price-high']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-[#6E0F12] hover:text-white transition-colors {{ $currentSort === 'price-high' ? 'bg-gray-50 font-medium' : '' }}">
                Price: High to Low
            </a>
        </div>
    </div>
</div>
