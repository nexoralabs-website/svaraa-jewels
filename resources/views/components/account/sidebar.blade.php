<div class="bg-white p-6 border border-[#C8A35D]/20 shadow-sm sticky top-8">
    <div class="mb-8 text-center pb-6 border-b border-gray-100">
        <div class="w-16 h-16 bg-[#F5EBDD] rounded-full mx-auto flex items-center justify-center text-2xl font-serif text-[#6E0F12] mb-3">
            {{ substr(auth()->user()->name, 0, 1) }}
        </div>
        <h3 class="font-serif text-lg text-gray-900">{{ auth()->user()->name }}</h3>
        <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
    </div>

    <nav class="space-y-2">
        <a href="{{ route('account.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded text-sm transition-colors {{ request()->routeIs('account.index') ? 'bg-[#FFFFF0] text-[#6E0F12] font-medium border-l-2 border-[#C8A35D]' : 'text-gray-600 hover:bg-gray-50 hover:text-[#6E0F12]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            <span>My Profile</span>
        </a>

        <a href="{{ route('account.orders') }}" class="flex items-center space-x-3 px-4 py-3 rounded text-sm transition-colors {{ request()->routeIs('account.orders*') ? 'bg-[#FFFFF0] text-[#6E0F12] font-medium border-l-2 border-[#C8A35D]' : 'text-gray-600 hover:bg-gray-50 hover:text-[#6E0F12]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            <span>Order History</span>
        </a>

        <a href="{{ route('wishlist.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded text-sm transition-colors {{ request()->routeIs('wishlist.*') ? 'bg-[#FFFFF0] text-[#6E0F12] font-medium border-l-2 border-[#C8A35D]' : 'text-gray-600 hover:bg-gray-50 hover:text-[#6E0F12]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
            <span>Wishlist</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="pt-4 mt-4 border-t border-gray-100">
            @csrf
            <button type="submit" class="flex items-center space-x-3 px-4 py-3 w-full text-left text-sm text-gray-600 hover:bg-red-50 hover:text-red-700 rounded transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
                <span>Logout</span>
            </button>
        </form>
    </nav>
</div>
