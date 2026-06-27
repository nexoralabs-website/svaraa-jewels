<x-layouts.app>
    <x-slot:title>Order History | Svaraa Jewels</x-slot:title>

    <div class="bg-[#FDFBF7] py-10 border-b border-[#C8A35D]/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-serif text-[#2E1A12]">Order History</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
            <div class="w-full lg:w-1/4">
                <x-account.sidebar />
            </div>

            <div class="w-full lg:w-3/4">
                @if($orders->count() > 0)
                <div class="space-y-4">
                    @foreach($orders as $order)
                    @php
                        $statusColors = [
                            'completed'  => 'bg-green-100 text-green-800',
                            'paid'       => 'bg-blue-100 text-blue-800',
                            'processing' => 'bg-blue-100 text-blue-800',
                            'shipped'    => 'bg-purple-100 text-purple-800',
                            'cancelled'  => 'bg-red-100 text-red-800',
                            'failed'     => 'bg-red-100 text-red-800',
                            'pending'    => 'bg-yellow-100 text-yellow-800',
                        ];
                        $statusColor = $statusColors[$order->order_status->value] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <div class="bg-white rounded-xl border border-[#E8DCCB] overflow-hidden hover:border-[#C8A35D]/50 transition-colors">
                        <div class="bg-[#FDFBF7] px-5 py-3 border-b border-[#E8DCCB] flex flex-wrap justify-between items-center gap-3">
                            <div class="flex gap-6 text-sm">
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-0.5">Order</p>
                                    <p class="font-medium text-[#2E1A12] font-mono text-xs">{{ $order->order_number }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-0.5">Date</p>
                                    <p class="font-medium text-[#2E1A12]">{{ $order->created_at->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-0.5">Total</p>
                                    <p class="font-semibold text-[#6E0F12]">&#8377;{{ number_format($order->total, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-0.5">Status</p>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                        {{ ucfirst($order->order_status->value) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('account.orders.show', $order) }}"
                                   class="text-sm text-[#C8A35D] hover:text-[#b8934d] font-medium transition-colors">
                                    View Details &rarr;
                                </a>
                            </div>
                        </div>

                        <div class="px-5 py-4 flex items-center justify-between gap-4">
                            <div class="flex gap-2">
                                @foreach($order->items->take(4) as $item)
                                <div class="w-12 h-12 rounded-lg border border-[#E8DCCB] overflow-hidden bg-gray-50 flex-shrink-0">
                                    <img src="{{ $item->product?->thumbnail ? asset('storage/' . $item->product->thumbnail) : asset('images/placeholder.jpg') }}"
                                         alt="{{ $item->product_name }}"
                                         class="w-full h-full object-cover">
                                </div>
                                @endforeach
                                @if($order->items->count() > 4)
                                <div class="w-12 h-12 rounded-lg border border-[#E8DCCB] bg-[#FDFBF7] flex items-center justify-center text-xs text-gray-500 font-medium">
                                    +{{ $order->items->count() - 4 }}
                                </div>
                                @endif
                            </div>

                            @if(in_array($order->payment_status, ['captured', 'refunded']))
                            <a href="{{ route('orders.invoice', $order) }}"
                               class="text-xs text-gray-500 hover:text-[#2E1A12] flex items-center gap-1 transition-colors"
                               title="Download Invoice">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Invoice
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    @if($orders->hasPages())
                    <div class="mt-6">{{ $orders->links() }}</div>
                    @endif
                </div>
                @else
                <x-ui.empty-state
                    icon="orders"
                    title="No orders yet"
                    description="You haven't placed any orders yet. Explore our collection."
                    :action="route('products.index')"
                    action-label="Start Shopping" />
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
