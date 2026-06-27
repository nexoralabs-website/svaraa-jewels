<div class="mt-12">
    <h2 class="text-xl font-serif text-gray-900 mb-6 border-b border-gray-100 pb-4">Payment Method</h2>
    
    <div class="space-y-4">
        {{-- Credit Card --}}
        <label class="flex p-4 border border-gray-200 cursor-pointer hover:border-[#C8A35D] transition-colors relative" :class="$refs.ccRadio.checked ? 'border-[#6E0F12] bg-[#FFFFF0]/30' : ''">
            <div class="flex items-center h-5">
                <input type="radio" x-ref="ccRadio" name="payment_method" value="credit_card" class="w-4 h-4 text-[#6E0F12] bg-gray-100 border-gray-300 focus:ring-[#6E0F12]" checked>
            </div>
            <div class="ml-4 text-sm flex-1">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-900">Credit / Debit Card</span>
                    <div class="flex gap-1">
                        <div class="w-8 h-5 bg-gray-200 rounded-sm"></div>
                        <div class="w-8 h-5 bg-gray-200 rounded-sm"></div>
                    </div>
                </div>
                <p class="text-xs font-normal text-gray-500 mt-1">Pay securely with your card.</p>
            </div>
        </label>

        {{-- UPI --}}
        <label class="flex p-4 border border-gray-200 cursor-pointer hover:border-[#C8A35D] transition-colors relative" :class="$refs.upiRadio.checked ? 'border-[#6E0F12] bg-[#FFFFF0]/30' : ''">
            <div class="flex items-center h-5">
                <input type="radio" x-ref="upiRadio" name="payment_method" value="upi" class="w-4 h-4 text-[#6E0F12] bg-gray-100 border-gray-300 focus:ring-[#6E0F12]">
            </div>
            <div class="ml-4 text-sm flex-1">
                <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-900">UPI</span>
                    <span class="text-xs font-semibold text-gray-400 border px-1 rounded">UPI</span>
                </div>
                <p class="text-xs font-normal text-gray-500 mt-1">Google Pay, PhonePe, Paytm, etc.</p>
            </div>
        </label>

        {{-- COD --}}
        <label class="flex p-4 border border-gray-200 cursor-pointer hover:border-[#C8A35D] transition-colors relative" :class="$refs.codRadio.checked ? 'border-[#6E0F12] bg-[#FFFFF0]/30' : ''">
            <div class="flex items-center h-5">
                <input type="radio" x-ref="codRadio" name="payment_method" value="cod" class="w-4 h-4 text-[#6E0F12] bg-gray-100 border-gray-300 focus:ring-[#6E0F12]">
            </div>
            <div class="ml-4 text-sm">
                <span class="font-medium text-gray-900">Cash on Delivery</span>
                <p class="text-xs font-normal text-gray-500 mt-1">Pay in cash when your order arrives.</p>
            </div>
        </label>
    </div>
</div>
