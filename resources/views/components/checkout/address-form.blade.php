@props(['addresses'])

<div x-data="{ useExistingAddress: {{ $addresses->count() > 0 ? 'true' : 'false' }} }">
    <div class="mb-6 flex items-center justify-between border-b border-gray-100 pb-4">
        <h2 class="text-xl font-serif text-gray-900">Shipping Address</h2>
        @if($addresses->count() > 0)
            <button type="button" @click="useExistingAddress = !useExistingAddress" class="text-sm font-medium text-[#6E0F12] hover:text-[#520b0d]">
                <span x-text="useExistingAddress ? 'Add New Address' : 'Use Existing Address'"></span>
            </button>
        @endif
    </div>

    {{-- Existing Addresses List --}}
    @if($addresses->count() > 0)
        <div x-show="useExistingAddress" x-transition.opacity class="space-y-4">
            @foreach($addresses as $address)
                <label class="flex p-4 border cursor-pointer hover:border-[#C8A35D] transition-colors relative" :class="$refs.addressRadio{{ $address->id }}.checked ? 'border-[#6E0F12] bg-[#FFFFF0]/30' : 'border-gray-200'">
                    <div class="flex items-center h-5">
                        <input type="radio" x-ref="addressRadio{{ $address->id }}" name="address_id" value="{{ $address->id }}" class="w-4 h-4 text-[#6E0F12] bg-gray-100 border-gray-300 focus:ring-[#6E0F12]" {{ $loop->first ? 'checked' : '' }}>
                    </div>
                    <div class="ml-4 text-sm">
                        <span class="font-medium text-gray-900">{{ $address->full_name }}</span>
                        <p id="helper-radio-text" class="text-xs font-normal text-gray-500 mt-1">
                            {{ $address->address_line }}, {{ $address->city }}, {{ $address->state }} {{ $address->pincode }}, {{ $address->country }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Phone: {{ $address->phone }}</p>
                    </div>
                </label>
            @endforeach
        </div>
    @endif

    {{-- New Address Form --}}
    <div x-show="!useExistingAddress" x-transition.opacity class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Full Name</label>
            <input type="text" name="full_name" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="John Doe">
        </div>
        
        <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Phone Number</label>
            <input type="text" name="phone" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="+1 (555) 000-0000">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Address Line</label>
            <input type="text" name="address_line" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="123 Luxury Lane, Apt 4B">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">City</label>
            <input type="text" name="city" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="New York">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">State / Province</label>
            <input type="text" name="state" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="NY">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Postal Code</label>
            <input type="text" name="pincode" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="10001">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Country</label>
            <input type="text" name="country" :required="!useExistingAddress" class="w-full border-gray-200 py-2.5 px-3 text-sm focus:border-[#C8A35D] focus:ring-0 outline-none border" placeholder="United States">
        </div>
    </div>
</div>
