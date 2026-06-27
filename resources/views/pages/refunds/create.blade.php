<x-layouts.app>
    <x-slot:title>Request Refund — Order #{{ $order->order_number }} | Svaraa Jewels</x-slot:title>

    <div class="bg-[#FDFBF7] py-10 border-b border-[#E8DCCB]/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('orders.show', $order) }}"
                   class="text-sm text-[#C8A35D] hover:text-[#b8934d] font-medium flex items-center gap-1.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Order
                </a>
            </div>
            <h1 class="text-2xl md:text-3xl font-serif text-[#2E1A12] mt-4">Request a Refund</h1>
            <p class="text-gray-500 text-sm mt-1">Order #{{ $order->order_number }}</p>
        </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm" role="alert">
            {{ session('error') }}
        </div>
        @endif

        {{-- Order summary card --}}
        <div class="bg-white rounded-xl border border-[#E8DCCB] p-6 mb-8">
            <h2 class="text-base font-serif text-[#2E1A12] mb-4">Order Summary</h2>
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex justify-between">
                    <span>Order</span>
                    <span class="font-medium text-[#2E1A12] font-mono">{{ $order->order_number }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Date</span>
                    <span>{{ $order->created_at->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Total</span>
                    <span class="font-semibold text-[#6E0F12]">&#8377;{{ number_format($order->total, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Items</span>
                    <span>{{ $order->items->count() }} item(s)</span>
                </div>
            </div>
        </div>

        {{-- Refund policy notice --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-8 flex gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-amber-800">
                <p class="font-medium mb-1">Refund Policy</p>
                <p>Refunds are processed within 3–5 business days after admin approval.
                   The amount will be credited to your original payment method.</p>
            </div>
        </div>

        {{-- Refund form --}}
        <form action="{{ route('refunds.store', $order) }}" method="POST"
              class="bg-white rounded-xl border border-[#E8DCCB] p-6 md:p-8 space-y-6">
            @csrf

            <div>
                <label for="reason"
                       class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-2">
                    Reason for Refund *
                </label>
                <textarea id="reason"
                          name="reason"
                          rows="5"
                          required
                          maxlength="1000"
                          class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm
                                 focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D]
                                 outline-none transition-colors resize-none"
                          placeholder="Please describe the reason for your refund request (e.g. item damaged, wrong item received, quality issue)...">{{ old('reason') }}</textarea>
                @error('reason')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400 mt-1 text-right">
                    <span x-data="{ len: {{ strlen(old('reason', '')) }} }"
                          x-text="len + ' / 1000'"></span>
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit"
                        class="flex-1 bg-[#6E0F12] text-white py-3 rounded-full font-medium text-sm
                               hover:bg-[#520b0d] transition-colors text-center">
                    Submit Refund Request
                </button>
                <a href="{{ route('orders.show', $order) }}"
                   class="flex-1 border border-gray-200 text-gray-600 py-3 rounded-full font-medium text-sm
                          hover:border-gray-300 hover:bg-gray-50 transition-colors text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>
