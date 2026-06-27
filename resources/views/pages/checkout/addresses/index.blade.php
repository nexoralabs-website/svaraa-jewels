{{-- Checkout Address Partial --}}
{{-- Displays saved addresses + add new form --}}
<div x-data="{
    addingNew: false,
    editingId: null,
    form: {
        full_name: '',
        phone: '',
        address_line: '',
        city: '',
        state: '',
        pincode: '',
        country: 'India',
    }
}">
    {{-- Addresses List --}}
    @if($addresses->count() > 0)
    <div class="space-y-4 mb-6">
        @foreach($addresses as $address)
        <div x-data="{ selected: {{ ($selectedAddressId ?? 0) == $address->id ? 'true' : 'false' }}, editing: {{ old('full_name') && old('full_name') === $address->full_name ? 'true' : 'false' } } }"
             class="relative border rounded-xl p-4 cursor-pointer transition-all duration-200"
             :class="selected ? 'border-[#C8A35D] bg-[#C8A35D]/5 shadow-sm' : 'border-gray-200 hover:border-[#C8A35D]/50'"
             @click="selectAddress({{ $address->id }})">

            {{-- Radio Indicator --}}
            <div class="flex items-start gap-4">
                <div class="pt-1">
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                         :class="selected ? 'border-[#C8A35D]' : 'border-gray-300'">
                        <div x-show="selected" x-transition class="w-3 h-3 rounded-full bg-[#C8A35D]"></div>
                    </div>
                </div>

                <div class="flex-1 min-w-0">
                    {{-- Default Badge --}}
                    @if($address->is_default)
                    <span class="inline-flex items-center bg-[#C8A35D]/10 text-[#6E0F12] px-2 py-0.5 rounded text-xs font-medium uppercase tracking-widest mb-2">
                        Default Address
                    </span>
                    @endif

                    <p class="font-medium text-[#2E1A12]">{{ $address->full_name }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $address->phone }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $address->address_line }}</p>
                    <p class="text-sm text-gray-600">{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</p>
                    <p class="text-sm text-gray-500">{{ $address->country }}</p>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col gap-2" @click.stop>
                    <button type="button"
                            @click="startEdit({{ $address->id }}, '{{ str_replace("'", "\\'", $address->full_name) }}', '{{ str_replace("'", "\\'", $address->phone) }}', '{{ str_replace("'", "\\'", $address->address_line) }}', '{{ str_replace("'", "\\'", $address->city) }}', '{{ str_replace("'", "\\'", $address->state) }}', '{{ str_replace("'", "\\'", $address->pincode) }}')"
                            class="text-xs text-[#C8A35D] hover:text-[#6E0F12] font-medium transition-colors">
                        Edit
                    </button>
                    <form action="{{ route('addresses.destroy', $address) }}" method="POST" class="inline"
                          @submit="if(!confirm('Delete this address?')) $event.preventDefault();">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-gray-400 hover:text-red-600 font-medium transition-colors">
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            <input type="radio" name="address_id" value="{{ $address->id }}" x-ref="radio_{{ $address->id }}"
                   class="sr-only" {{ ($selectedAddressId ?? 0) == $address->id ? 'checked' : '' }}>
        </div>
        @endforeach
    </div>
    @endif

    {{-- New Address Form (toggle) --}}
    <div x-show="addingNew" x-cloak x-transition class="mt-4">
        <div class="border border-[#C8A35D]/30 rounded-xl p-6 bg-[#FFFFF0]/30">
            <h3 class="text-lg font-serif text-[#2E1A12] mb-4">New Address</h3>
            <form action="{{ route('addresses.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Full Name *</label>
                    <input type="text" name="full_name" x-model="form.full_name" required
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                    @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Phone *</label>
                    <input type="tel" name="phone" x-model="form.phone" required
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Country *</label>
                    <input type="text" name="country" x-model="form.country" value="India" required
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                    @error('country') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Address Line *</label>
                    <textarea name="address_line" x-model="form.address_line" rows="2" required
                              class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none"></textarea>
                    @error('address_line') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">City *</label>
                    <input type="text" name="city" x-model="form.city" required
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                    @error('city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">State *</label>
                    <input type="text" name="state" x-model="form.state" required
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                    @error('state') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">PIN Code *</label>
                    <input type="text" name="pincode" x-model="form.pincode" required
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                    @error('pincode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_default" value="1" class="w-4 h-4 text-[#C8A35D] border-gray-300 rounded focus:ring-[#C8A35D]">
                        <span class="text-sm text-gray-700">Set as default address</span>
                    </label>
                </div>
                <div class="md:col-span-2 flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-[#6E0F12] text-white py-2.5 rounded-lg font-medium text-sm hover:bg-[#520b0d] transition-colors">
                        Save Address
                    </button>
                    <button type="button" @click="addingNew = false" class="px-6 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add New Address Toggle --}}
    @if(!Auth::check() || ($addresses->count() > 0 && !$addingNew))
    <button type="button" @click="addingNew = !addingNew"
            class="mt-4 w-full border-2 border-dashed border-gray-200 rounded-xl py-4 text-sm font-medium text-gray-500 hover:border-[#C8A35D] hover:text-[#C8A35D] transition-colors flex items-center justify-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span x-text="addingNew ? 'Hide New Address Form' : 'Add New Address'"></span>
    </button>
    @endif

    {{-- Edit Address Modal --}}
    <div x-show="editingId" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="editingId = null"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <h3 class="text-lg font-serif text-[#2E1A12] mb-4">Edit Address</h3>
                <form :action="'/addresses/' + editingId" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Full Name *</label>
                        <input type="text" name="full_name" x-model="form.full_name" required
                               class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Phone *</label>
                            <input type="tel" name="phone" x-model="form.phone" required
                                   class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Country *</label>
                            <input type="text" name="country" x-model="form.country" required
                                   class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">Address Line *</label>
                        <textarea name="address_line" x-model="form.address_line" rows="2" required
                                  class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none"></textarea>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">City *</label>
                            <input type="text" name="city" x-model="form.city" required
                                   class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">State *</label>
                            <input type="text" name="state" x-model="form.state" required
                                   class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 uppercase tracking-widest mb-1">PIN *</label>
                            <input type="text" name="pincode" x-model="form.pincode" required
                                   class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:border-[#C8A35D] focus:ring-1 focus:ring-[#C8A35D] outline-none">
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 bg-[#6E0F12] text-white py-2.5 rounded-lg font-medium text-sm hover:bg-[#520b0d] transition-colors">
                            Update Address
                        </button>
                        <button type="button" @click="editingId = null" class="px-6 py-2.5 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
