<x-layouts.app>
    <x-slot:title>Order #{{ $order->order_number }} | Svaraa Jewels</x-slot:title>

    {{-- Header --}}
    <div class="bg-[#FDFBF7] py-10 border-b border-[#C8A35D]/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Order</p>
                    <h1 class="text-2xl md:text-3xl font-serif text-[#2E1A12]">#{{ $order->order_number }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    @if(in_array($order->payment_status, ['captured', 'refunded']))
                    <a href="{{ route('orders.invoice', $order) }}"
                       class="text-sm bg-[#2E1A12] text-white px-4 py-2 rounded-full font-medium hover:bg-[#1a0e09] transition-colors flex items-center gap-1.5"
                       title="Download Invoice PDF">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Invoice
                    </a>
                    @endif
                    <a href="{{ route('orders.index') }}" class="text-sm text-[#6E0F12] hover:text-[#520b0d] font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @php
            $statusConfig = [
                'pending'   => ['label' => 'Pending',   'color' => 'bg-yellow-100 text-yellow-800',  'step' => 1],
                'processing'=> ['label' => 'Processing', 'color' => 'bg-blue-100 text-blue-800',      'step' => 2],
                'paid'      => ['label' => 'Paid',       'color' => 'bg-blue-100 text-blue-800',      'step' => 2],
                'shipped'   => ['label' => 'Shipped',    'color' => 'bg-purple-100 text-purple-800',  'step' => 3],
                'completed' => ['label' => 'Delivered',  'color' => 'bg-green-100 text-green-800',    'step' => 4],
                'cancelled' => ['label' => 'Cancelled',  'color' => 'bg-red-100 text-red-800',        'step' => 0],
                'failed'    => ['label' => 'Failed',     'color' => 'bg-red-100 text-red-800',        'step' => 0],
            ];
            $currentStatus = $statusConfig[$order->order_status->value] ?? ['label' => ucfirst($order->order_status->value), 'color' => 'bg-gray-100 text-gray-800', 'step' => 1];
            $currentStep = $currentStatus['step'];
            $paymentStatusConfig = [
                'pending'   => ['color' => 'text-yellow-600', 'icon' => '○'],
                'authorized'=> ['color' => 'text-blue-600',   'icon' => '◐'],
                'captured'  => ['color' => 'text-green-600',  'icon' => '●'],
                'failed'    => ['color' => 'text-red-600',    'icon' => '✕'],
                'refunded'  => ['color' => 'text-purple-600', 'icon' => '↺'],
            ];
            $paymentStatus = $paymentStatusConfig[$order->payment_status] ?? ['color' => 'text-gray-500', 'icon' => '?'];
        @endphp

        @if(in_array($order->order_status->value, ['pending', 'processing', 'paid', 'shipped', 'completed']))
        {{-- Order Tracking Timeline --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB] p-6 md:p-8 mb-8">
            <h3 class="text-lg font-serif text-[#2E1A12] mb-8 text-center">Order Tracking</h3>
            <div class="relative">
                <div class="hidden sm:flex items-center justify-between">
                    {{-- Step 1: Ordered --}}
                    <div class="flex flex-col items-center relative z-10">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center
                            @if($currentStep >= 1) bg-[#C8A35D] text-white @else bg-gray-100 text-gray-400 @endif">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                        </div>
                        <p class="mt-2 text-xs font-medium text-gray-700">Ordered</p>
                        <p class="text-[10px] text-gray-400">{{ $order->created_at->format('M d') }}</p>
                    </div>

                    {{-- Connector --}}
                    <div class="hidden sm:flex-1 h-0.5 mx-4 mt-[-20px]
                        @if($currentStep >= 2) bg-[#C8A35D] @else bg-gray-200 @endif"></div>

                    {{-- Step 2: Paid --}}
                    <div class="flex flex-col items-center relative z-10">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center
                            @if($currentStep >= 2) bg-[#C8A35D] text-white @else bg-gray-100 text-gray-400 @endif">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                        </div>
                        <p class="mt-2 text-xs font-medium text-gray-700">Payment {{ ucfirst($order->payment_status) }}</p>
                        <p class="text-[10px] text-gray-400">{{ $order->paid_at?->format('M d') ?? '--' }}</p>
                    </div>

                    <div class="hidden sm:flex-1 h-0.5 mx-4 mt-[-20px]
                        @if($currentStep >= 3) bg-[#C8A35D] @else bg-gray-200 @endif"></div>

                    {{-- Step 3: Shipped --}}
                    <div class="flex flex-col items-center relative z-10">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center
                            @if($currentStep >= 3) bg-[#C8A35D] text-white @else bg-gray-100 text-gray-400 @endif">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.793 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                        </div>
                        <p class="mt-2 text-xs font-medium text-gray-700">Shipped</p>
                        <p class="text-[10px] text-gray-400">--</p>
                    </div>

                    <div class="hidden sm:flex-1 h-0.5 mx-4 mt-[-20px]
                        @if($currentStep >= 4) bg-[#C8A35D] @else bg-gray-200 @endif"></div>

                    {{-- Step 4: Delivered --}}
                    <div class="flex flex-col items-center relative z-10">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center
                            @if($currentStep >= 4) bg-green-500 text-white @else bg-gray-100 text-gray-400 @endif">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="mt-2 text-xs font-medium text-gray-700">Delivered</p>
                        <p class="text-[10px] text-gray-400">--</p>
                    </div>
                </div>

                {{-- Mobile Timeline --}}
                <div class="sm:hidden space-y-0">
                    @php $steps = [['label' => 'Ordered', 'date' => $order->created_at->format('M d, Y'), 'icon' => '📋'], ['label' => 'Payment', 'date' => $order->paid_at?->format('M d, Y') ?? 'Pending', 'icon' => '💳'], ['label' => 'Shipped', 'date' => 'Pending', 'icon' => '🚚'], ['label' => 'Delivered', 'date' => 'Pending', 'icon' => '✅']]; @endphp
                    @foreach($steps as $idx => $step)
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg
                                @if($idx + 1 <= $currentStep) bg-[#C8A35D] text-white @else bg-gray-100 text-gray-400 @endif">
                                {{ $step['icon'] }}
                            </div>
                            @if($idx < 3)
                            <div class="w-0.5 flex-1 my-1
                                @if($idx + 1 < $currentStep) bg-[#C8A35D] @else bg-gray-200 @endif"></div>
                            @endif
                        </div>
                        <div class="pb-6">
                            <p class="text-sm font-medium @if($idx + 1 <= $currentStep) text-[#2E1A12] @else text-gray-400 @endif">{{ $step['label'] }}</p>
                            <p class="text-xs text-gray-400">{{ $step['date'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left: Items --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB] p-6">
                    <h3 class="text-lg font-serif text-[#2E1A12] mb-5">Items Ordered</h3>
                    <div class="divide-y divide-[#E8DCCB]">
                        @foreach($order->items as $item)
                        <div class="flex items-center py-4 gap-4">
                            <div class="w-20 h-20 bg-gray-50 rounded-lg overflow-hidden border border-gray-100 flex-shrink-0">
                                @php
                                    $img = asset('images/placeholder.jpg');
                                    if ($item->product && $item->product->thumbnail) {
                                        $img = asset('storage/' . $item->product->thumbnail);
                                    }
                                @endphp
                                <img src="{{ $img }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover" onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-[#2E1A12] truncate">{{ $item->product_name }}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Qty: {{ $item->quantity }} × ₹{{ number_format($item->price, 2) }}</p>
                            </div>
                            <span class="text-sm font-medium text-[#2E1A12] whitespace-nowrap">₹{{ number_format($item->subtotal, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-6 pt-4 border-t border-[#E8DCCB] space-y-2">
                        <div class="flex justify-between text-sm text-gray-600"><span>Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></div>
                        <div class="flex justify-between text-sm text-gray-600"><span>Shipping</span><span>{{ $order->shipping > 0 ? '₹'.number_format($order->shipping, 2) : 'FREE' }}</span></div>
                        @if($order->coupon_code)
                        <div class="flex justify-between text-sm text-green-600">
                            <span class="flex items-center gap-1">
                                Coupon
                                <span class="text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded font-mono">{{ $order->coupon_code }}</span>
                            </span>
                            <span>-₹{{ number_format($order->coupon_discount ?? $order->discount, 2) }}</span>
                        </div>
                        @elseif($order->discount > 0)
                        <div class="flex justify-between text-sm text-green-600"><span>Discount</span><span>-₹{{ number_format($order->discount, 2) }}</span></div>
                        @endif
                        <div class="flex justify-between text-lg font-medium text-[#2E1A12] pt-2 border-t border-gray-100">
                            <span>Total</span><span class="text-[#C8A35D]">₹{{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Details --}}
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB] p-6">
                    <h3 class="text-lg font-serif text-[#2E1A12] mb-4">Order Details</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd><span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full {{ $currentStatus['color'] }}">{{ $currentStatus['label'] }}</span></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Payment</dt><dd class="{{ $paymentStatus['color'] }} font-medium">{{ $paymentStatus['icon'] }} {{ ucfirst($order->payment_status) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Method</dt><dd class="text-[#2E1A12]">{{ strtoupper($order->payment_method ?? 'N/A') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Date</dt><dd class="text-[#2E1A12]">{{ $order->created_at->format('M d, Y') }}</dd></div>
                        @if($order->paid_at)
                        <div class="flex justify-between"><dt class="text-gray-500">Paid At</dt><dd class="text-[#2E1A12]">{{ $order->paid_at->format('M d, Y h:i A') }}</dd></div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB] p-6">
                    <h3 class="text-lg font-serif text-[#2E1A12] mb-4">Shipping Address</h3>
                    <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $order->shipping_address }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $order->customer_phone }}</p>
                </div>

                @if(in_array($order->payment_status, ['failed']) && !in_array($order->order_status->value, ['cancelled']))
                <div class="bg-red-50 border border-red-200 rounded-xl p-5">
                    <p class="text-sm text-red-700 font-medium mb-3">Payment failed. Retry to complete your order.</p>
                    <a href="{{ route('payment.retry', $order) }}" class="inline-block bg-[#6E0F12] text-white px-5 py-2.5 rounded-full text-xs font-medium hover:bg-[#520b0d] transition-colors">
                        Retry Payment
                    </a>
                </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB] p-6">
                    <h3 class="text-lg font-serif text-[#2E1A12] mb-4">Need Help?</h3>
                    <p class="text-sm text-gray-500 mb-3">Questions about your order? Our team is here to help.</p>
                    <a href="mailto:{{ config('mail.from.address') }}" class="text-sm text-[#C8A35D] font-medium hover:underline">Contact Support →</a>
                </div>

                {{-- Refund Request --}}
                @if(isset($canRefund) && $canRefund)
                <div class="bg-white rounded-xl shadow-sm border border-[#E8DCCB] p-6">
                    <h3 class="text-lg font-serif text-[#2E1A12] mb-2">Request a Refund</h3>
                    <p class="text-sm text-gray-500 mb-4">Not satisfied? We'll process your refund within 3–5 business days.</p>
                    <a href="{{ route('refunds.create', $order) }}"
                       class="inline-block bg-red-50 border border-red-200 text-red-700 px-5 py-2.5
                              rounded-full text-sm font-medium hover:bg-red-100 transition-colors">
                        Request Refund
                    </a>
                </div>
                @endif

                @if($order->refund)
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-5">
                    <p class="text-sm font-medium text-purple-800 mb-1">Refund {{ ucfirst($order->refund->status) }}</p>
                    <p class="text-xs text-purple-600">
                        Submitted {{ $order->refund->created_at->diffForHumans() }}
                        @if($order->refund->status === 'approved')
                        — &#8377;{{ number_format($order->total, 2) }} will be credited to your account.
                        @endif
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>


