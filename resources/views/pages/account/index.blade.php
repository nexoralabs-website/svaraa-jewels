<x-layouts.app>
    <x-slot:title>My Account | Svaraa Jewels</x-slot:title>

    <div class="bg-[#FFFFF0] py-12 border-b border-[#C8A35D]/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-serif text-[#6E0F12]">My Account</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
            <!-- Sidebar -->
            <div class="w-full lg:w-1/4">
                <x-account.sidebar />
            </div>

            <!-- Content -->
            <div class="w-full lg:w-3/4 space-y-8">
                <!-- Profile Details -->
                <div class="bg-white p-8 border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-serif text-[#6E0F12]">Profile Details</h2>
                        <a href="{{ route('profile.edit') }}" class="text-sm text-[#C8A35D] hover:text-[#6E0F12] font-medium transition-colors">Edit Profile</a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-widest mb-1">Full Name</p>
                            <p class="font-medium text-gray-900">{{ $user->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-widest mb-1">Email Address</p>
                            <p class="font-medium text-gray-900">{{ $user->email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white p-8 border border-gray-100 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-serif text-[#6E0F12]">Recent Orders</h2>
                        <a href="{{ route('account.orders') }}" class="text-sm text-[#C8A35D] hover:text-[#6E0F12] font-medium transition-colors">View All</a>
                    </div>

                    @if($recentOrders->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentOrders as $order)
                                @php
                                    $statusValue = $order->order_status?->value ?? $order->order_status;
                                    $statusClass = in_array($statusValue, ['completed', 'delivered', 'shipped']) ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800';
                                @endphp
                                <div class="flex items-center justify-between p-4 border border-gray-100 rounded hover:border-[#C8A35D]/50 transition-colors">
                                    <div>
                                        <p class="font-medium text-gray-900 mb-1">{{ $order->order_number }}</p>
                                        <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-[#C8A35D] mb-1">₹{{ number_format($order->total, 2) }}</p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                            {{ ucfirst((string) $statusValue) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">You haven't placed any orders yet.</p>
                            <a href="{{ route('products.index') }}" class="inline-block mt-4 text-[#C8A35D] hover:text-[#6E0F12] font-medium">Start Shopping</a>
                        </div>
                    @endif
                </div>

                <!-- Saved Addresses -->
                <div class="bg-white p-8 border border-gray-100 shadow-sm">
                    <h2 class="text-xl font-serif text-[#6E0F12] mb-6">Saved Addresses</h2>
                    
                    @if($addresses->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @foreach($addresses as $address)
                                <div class="p-4 border border-gray-100 rounded">
                                    <h4 class="font-medium text-gray-900 mb-2">{{ $address->full_name }}</h4>
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        {{ $address->address_line }}<br>
                                        {{ $address->city }}, {{ $address->state }} {{ $address->pincode }}<br>
                                        {{ $address->country }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-2">Phone: {{ $address->phone }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">You haven't saved any addresses yet. Add one during your next checkout.</p>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>
