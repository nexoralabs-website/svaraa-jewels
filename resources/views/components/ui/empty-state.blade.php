@props([
    'icon'        => 'shopping-bag',
    'title'       => 'Nothing here yet',
    'description' => '',
    'action'      => '',
    'actionLabel' => 'Browse',
    'actionClass' => 'bg-[#C8A35D] text-white hover:bg-[#b8934d]',
])

<div {{ $attributes->merge(['class' => 'text-center py-20 px-6']) }}>
    <div class="mx-auto w-20 h-20 bg-[#F5EBDD] rounded-full flex items-center justify-center mb-6">
        @if($icon === 'shopping-bag')
        <svg class="w-9 h-9 text-[#C8A35D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119
                     1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576
                     0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375
                     0 11-.75 0 .375.375 0 01.75 0z"/>
        </svg>
        @elseif($icon === 'search')
        <svg class="w-9 h-9 text-[#C8A35D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        @elseif($icon === 'orders')
        <svg class="w-9 h-9 text-[#C8A35D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0
                     002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        @elseif($icon === 'heart')
        <svg class="w-9 h-9 text-[#C8A35D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12
                     7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
        @else
        <svg class="w-9 h-9 text-[#C8A35D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16
                     0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293
                     l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        @endif
    </div>

    <h3 class="text-xl font-serif text-[#2E1A12] mb-2">{{ $title }}</h3>

    @if($description)
    <p class="text-gray-500 text-sm max-w-md mx-auto mb-6">{{ $description }}</p>
    @endif

    @if($action)
    <a href="{{ $action }}"
       class="inline-block px-8 py-3 rounded-full text-sm font-medium transition-colors {{ $actionClass }}">
        {{ $actionLabel }}
    </a>
    @endif

    {{ $slot ?? '' }}
</div>
