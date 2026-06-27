<x-filament-panels::page>
    {{ $this->form }}

    @if(count($this->previews))
        <div class="space-y-6 mt-8">
            @foreach($this->previews as $index => $product)
                <div class="border rounded-lg p-4 mb-4">
                    <label class="flex items-center gap-2 mb-3">
                        <input
                            type="checkbox"
                            wire:model="selectedRows"
                            value="{{ $index }}"
                        >
                        <span>Select this product</span>
                    </label>

                    @if(!empty($product['image']))
                        <img
                            src="{{ $product['image'] }}"
                            alt="{{ $product['name'] ?? 'Product Image' }}"
                            class="w-32 h-32 object-cover rounded mb-3"
                        >
                    @endif

                    <h3 class="font-bold">{{ $product['name'] }}</h3>
                    <p>{{ $product['description'] }}</p>
                    <p>Category: {{ $product['category'] ?? 'N/A' }}</p>

                    @if(!empty($product['price']))
                        <p class="mt-2 font-semibold">
                            ₹{{ $product['price'] }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="flex gap-4 mt-6">
            <x-filament::button
                wire:click="saveDraft"
                color="gray"
            >
                Save Draft
            </x-filament::button>

            <x-filament::button
                wire:click="publishSelected"
                color="success"
            >
                Publish Products
            </x-filament::button>
        </div>
    @endif
</x-filament-panels::page>
