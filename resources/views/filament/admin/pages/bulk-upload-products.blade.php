<x-filament-panels::page>

    {{-- ── Upload form ────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        {{ $this->form }}

        {{-- Process images button (image upload mode only) --}}
        @if(empty($this->previews) && !$this->showDbGrid)
            <div class="mt-4">
                <x-filament::button
                    wire:click="processUploadedImages"
                    color="primary"
                    icon="heroicon-o-arrow-path"
                >
                    Process Images
                </x-filament::button>
            </div>
        @endif
    </div>

    {{-- ── Extraction progress ─────────────────────────────────────────── --}}
    @if($this->step === 'extracting' || $this->step === 'generating')
        <div class="flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-700">
            <svg class="animate-spin w-5 h-5 text-blue-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">
                {{ $this->progressMessage ?: 'Processing…' }}
            </span>
        </div>
    @endif

    {{-- ── Import summary banner ───────────────────────────────────────── --}}
    @if($this->showSummary && $this->step === 'review')
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Import Summary</h3>
                <button wire:click="$set('showSummary', false)"
                        class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">Dismiss</button>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                @foreach([
                    ['Pages scanned',  $this->summaryPagesScanned,  'blue'],
                    ['Extracted',       $this->summaryExtracted,     'green'],
                    ['Duplicates',      $this->summaryDuplicates,    'yellow'],
                    ['Placeholders',    $this->summaryPlaceholders,  'gray'],
                    ['Failed',          $this->summaryFailed,        'red'],
                ] as [$label, $count, $color])
                <div class="rounded-lg p-3 text-center
                    @if($color==='blue') bg-blue-50 dark:bg-blue-900/20
                    @elseif($color==='green') bg-green-50 dark:bg-green-900/20
                    @elseif($color==='yellow') bg-yellow-50 dark:bg-yellow-900/20
                    @elseif($color==='red') bg-red-50 dark:bg-red-900/20
                    @else bg-gray-50 dark:bg-gray-700/50
                    @endif">
                    <div class="text-xl font-bold
                        @if($color==='blue') text-blue-600 dark:text-blue-400
                        @elseif($color==='green') text-green-600 dark:text-green-400
                        @elseif($color==='yellow') text-yellow-600 dark:text-yellow-400
                        @elseif($color==='red') text-red-600 dark:text-red-400
                        @else text-gray-600 dark:text-gray-400
                        @endif">
                        {{ $count }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $label }}</div>
                </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── DB-backed review grid (Phase 5) ────────────────────────────── --}}
    @if($this->showDbGrid)
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-800 dark:text-gray-100">
                    Preview Review Grid
                    @if($this->activeBatchUuid)
                        <span class="ml-2 text-sm font-normal text-gray-400 dark:text-gray-500">
                            · batch {{ substr($this->activeBatchUuid, 0, 8) }}…
                        </span>
                    @endif
                </h2>
                <button wire:click="closeReviewGrid"
                        class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                    Close grid
                </button>
            </div>

            @livewire('bulk-upload-preview-grid', ['batchUuid' => $this->activeBatchUuid])
        </div>
    @endif

    {{-- ── In-memory review table (legacy / direct image uploads) ────── --}}
    @if(count($this->previews) && !$this->showDbGrid)
        <div class="space-y-4 mt-4">

            {{-- Select / Deselect all --}}
            <div class="flex items-center gap-3">
                <button wire:click="selectAll"
                        class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                    Select all
                </button>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <button wire:click="deselectAll"
                        class="text-xs text-gray-500 dark:text-gray-400 hover:underline">
                    Deselect all
                </button>
                <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                    {{ count($this->previews) }} row(s)
                </span>
            </div>

            @foreach(array_slice($this->previews, 0, $this->visibleRowCount, true) as $index => $product)
                <div class="border rounded-xl p-4 bg-white dark:bg-gray-800 shadow-sm
                    {{ !empty($this->rowErrors[$index]) ? 'border-red-400 dark:border-red-500' : 'border-gray-200 dark:border-gray-700' }}">

                    <div class="flex items-start gap-4">
                        {{-- Checkbox --}}
                        <label class="flex items-center gap-2 mt-1 flex-shrink-0">
                            <input
                                type="checkbox"
                                wire:model.live="selectedRows"
                                value="{{ $index }}"
                                class="rounded border-gray-300 dark:border-gray-600 text-primary-600"
                            >
                        </label>

                        {{-- Image --}}
                        @if(!empty($product['image']) || !empty($product['preview_url']))
                            <img
                                src="{{ $product['image'] ?? $product['preview_url'] }}"
                                alt="{{ $product['name'] ?? 'Product Image' }}"
                                class="w-24 h-24 object-cover rounded-lg border border-gray-200 dark:border-gray-600 flex-shrink-0"
                                onerror="this.src='{{ asset('images/placeholder.svg') }}'"
                            >
                        @endif

                        {{-- Fields --}}
                        <div class="flex-1 min-w-0 space-y-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-gray-800 dark:text-gray-100">
                                    {{ $product['name'] ?? '—' }}
                                </span>
                                @if(!empty($product['page_info']))
                                    <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">
                                        {{ $product['page_info'] }}
                                    </span>
                                @endif
                                @if(!empty($product['is_placeholder']))
                                    <span class="text-xs text-amber-500 bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 rounded">placeholder</span>
                                @endif
                                @if(($product['occurrences_count'] ?? 1) > 1)
                                    <span class="text-xs text-orange-500 bg-orange-50 dark:bg-orange-900/30 px-2 py-0.5 rounded">
                                        {{ $product['occurrences_count'] }}× dup
                                    </span>
                                @endif
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                {{ $product['description'] ?? '' }}
                            </p>

                            <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-300">
                                <span>Category: <strong>{{ $product['category'] ?? 'N/A' }}</strong></span>
                                @if(!empty($product['price']))
                                    <span>Price: <strong>₹{{ $product['price'] }}</strong></span>
                                @endif
                                <span>Stock: <strong>{{ $product['stock'] ?? 1 }}</strong></span>
                            </div>

                            {{-- Row errors --}}
                            @if(!empty($this->rowErrors[$index]))
                                <div class="text-xs text-red-600 dark:text-red-400 space-y-0.5">
                                    @foreach($this->rowErrors[$index] as $err)
                                        <div>⚠ {{ $err }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Remove button --}}
                        <button wire:click="removeRow({{ $index }})"
                                class="flex-shrink-0 p-1.5 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-400 transition-colors"
                                title="Remove">
                            &#x1F5D1;
                        </button>
                    </div>
                </div>
            @endforeach

            {{-- Show more --}}
            @if($this->visibleRowCount < count($this->previews))
                <div class="text-center">
                    <button wire:click="showMoreRows"
                            class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
                        Show more ({{ count($this->previews) - $this->visibleRowCount }} remaining)
                    </button>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap gap-3 mt-6">
            <x-filament::button
                wire:click="saveDraft"
                color="gray"
                icon="heroicon-o-document"
            >
                Save All as Draft
            </x-filament::button>

            <x-filament::button
                wire:click="publishSelected"
                color="success"
                icon="heroicon-o-rocket-launch"
            >
                Publish Selected
            </x-filament::button>

            <x-filament::button
                wire:click="clearAll"
                color="danger"
                icon="heroicon-o-trash"
                size="sm"
            >
                Clear All
            </x-filament::button>
        </div>
    @endif

    {{-- ── Done state ──────────────────────────────────────────────────── --}}
    @if($this->step === 'done' && !$this->showDbGrid)
        <div class="flex flex-col items-center justify-center py-10 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="text-5xl text-green-400 mb-3">&#x2705;</div>
            @if($this->lastAction === 'published')
                <h3 class="text-base font-medium text-gray-700 dark:text-gray-200">
                    {{ $this->lastCount }} product(s) published successfully.
                </h3>
            @elseif($this->lastAction === 'draft_saved')
                <h3 class="text-base font-medium text-gray-700 dark:text-gray-200">
                    {{ $this->lastCount }} product(s) saved as draft.
                </h3>
            @endif
            <button wire:click="openReviewGrid"
                    class="mt-4 text-sm text-primary-600 dark:text-primary-400 hover:underline">
                Open Review Grid →
            </button>
        </div>
    @endif

</x-filament-panels::page>
