<x-layouts.app>
    <x-slot:title>Order Details | Svaraa Jewels</x-slot:title>

    <div class="bg-[#FFFFF0] py-12 border-b border-[#C8A35D]/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-serif text-[#6E0F12]">Order Details</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
            <!-- Sidebar -->
            <div class="w-full lg:w-1/4">
                <x-account.sidebar />
            </div>

            <!-- Content -->
            <div class="w-full lg:w-3/4">
                <div class="mb-6">
                    <a href="{{ route('account.orders') }}" class="text-sm text-gray-500 hover:text-[#6E0F12] inline-flex items-center gap-1 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Back to Orders
                    </a>
                </div>

                <div class="bg-white p-8 border border-gray-100 shadow-sm mb-8">
                    <div class="flex flex-wrap justify-between items-start gap-4 mb-8 pb-6 border-b border-gray-100">
                        <div>
                            <h2 class="text-xl font-serif text-[#6E0F12] mb-1">Order {{ $order->order_number }}</h2>
                            <p class="text-sm text-gray-500">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 mb-2">
                                Status:
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ in_array($order->order_status?->value ?? $order->order_status, ['completed', 'delivered', 'shipped']) ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst((string) ($order->order_status?->value ?? $order->order_status)) }}
                                </span>
                            </p>
                            <p class="text-sm font-medium text-gray-900">
                                Payment:
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ in_array($order->payment_status, ['captured', 'paid', 'refunded']) ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst((string) $order->payment_status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-6 mb-8">
                        <h3 class="text-lg font-serif text-gray-900 mb-4">Items Summary</h3>
                        <div class="divide-y divide-gray-100 border-t border-gray-100">
                            @foreach($order->items as $item)
                                <div class="py-4 flex gap-6">
                                    <div class="w-20 h-20 bg-gray-50 border border-gray-100 flex-shrink-0">
                                        <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1 flex flex-col justify-between">
                                        <div class="flex justify-between">
                                            <div>
                                                <h4 class="font-medium text-gray-900">
                                                    <a href="{{ route('products.show', $item->product) }}" class="hover:text-[#6E0F12] transition-colors">{{ $item->product->name }}</a>
                                                </h4>
                                                <p class="text-sm text-gray-500 mt-1">Qty: {{ $item->quantity }}</p>
                                            </div>
                                            <p class="font-medium text-[#C8A35D]">₹{{ number_format($item->price, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t border-gray-100 pt-8">
                        <!-- Shipping Details -->
                        <div>
                            <h3 class="text-lg font-serif text-gray-900 mb-4">Shipping Address</h3>
                            @if($order->address)
                                <div class="text-sm text-gray-600 leading-relaxed bg-gray-50 p-4 rounded border border-gray-100">
                                    <p class="font-medium text-gray-900 mb-1">{{ $order->address->full_name }}</p>
                                    <p>{{ $order->address->address_line }}</p>
                                    <p>{{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->pincode }}</p>
                                    <p>{{ $order->address->country }}</p>
                                    <p class="mt-2 text-gray-500">Phone: {{ $order->address->phone }}</p>
                                </div>
                            @else
                                <p class="text-sm text-gray-500">Address details not available.</p>
                            @endif
                        </div>

                        <!-- Order Total -->
                        <div>
                            <h3 class="text-lg font-serif text-gray-900 mb-4">Order Total</h3>
                            <div class="bg-gray-50 p-6 rounded border border-gray-100 space-y-3">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Subtotal</span>
                                    <span>₹{{ number_format($order->subtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-600 pb-3 border-b border-gray-200">
                                    <span>Shipping</span>
                                    <span class="text-green-600">{{ $order->shipping > 0 ? '₹'.number_format($order->shipping, 2) : 'Free' }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-1">
                                    <span class="font-medium text-gray-900">Total</span>
                                    <span class="text-xl font-medium text-[#6E0F12]">₹{{ number_format($order->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
