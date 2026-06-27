<x-layouts.app>
    <x-slot:title>My Orders | Svaraa Jewels</x-slot:title>

    <div class="bg-[#FDFBF7] py-10 border-b border-[#C8A35D]/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl md:text-4xl font-serif text-[#2E1A12] text-center">My Orders</h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        @if($orders->isEmpty())
            <x-ui.empty-state
                icon="orders"
                title="No orders yet"
                description="You haven't placed any orders. Browse our collection and find something beautiful."
                :action="route('products.index')"
                action-label="Start Shopping" />
        @else
            <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#FDFBF7] border-b border-[#E8DCCB]">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-[#2E1A12] uppercase tracking-widest">Order #</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-[#2E1A12] uppercase tracking-widest">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-[#2E1A12] uppercase tracking-widest">Total</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-[#2E1A12] uppercase tracking-widest">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-[#2E1A12] uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E8DCCB]">
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
                                    $color = $statusColors[$order->order_status->value] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <tr class="hover:bg-[#FDFBF7]/60 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-[#2E1A12] font-mono">{{ $order->order_number }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $order->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-[#2E1A12]">
                                        &#8377;{{ number_format($order->total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 inline-flex text-xs font-semibold rounded-full {{ $color }}">
                                            {{ ucfirst($order->order_status->value) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('orders.show', $order) }}"
                                               class="text-sm text-[#C8A35D] hover:text-[#b8934d] font-medium transition-colors">
                                                View
                                            </a>
                                            @if(in_array($order->payment_status, ['captured', 'refunded']))
                                            <a href="{{ route('orders.invoice', $order) }}"
                                               class="text-sm text-[#2E1A12] hover:text-[#1a0e09] font-medium flex items-center gap-1 transition-colors"
                                               title="Download Invoice">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                PDF
                                            </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-[#E8DCCB]">
                    {{ $orders->links() }}
                </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts.app>
