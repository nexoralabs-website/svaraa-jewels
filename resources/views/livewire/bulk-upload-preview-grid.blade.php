<div class="space-y-4">

    {{-- ── Filter bar ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">

        {{-- Batch filter --}}
        @if(count($activeBatches))
        <div class="flex items-center gap-2">
            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Batch</label>
            <select wire:model.live="filterBatch"
                    class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-primary-500">
                <option value="">All batches</option>
                @foreach($activeBatches as $b)
                    <option value="{{ $b['id'] }}">{{ substr($b['id'], 0, 8) }}… ({{ $b['processed_pages'] }}/{{ $b['total_pages'] }}p)</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Status filter --}}
        <div class="flex items-center gap-2">
            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
            <select wire:model.live="filterStatus"
                    class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-primary-500">
                <option value="">All statuses</option>
                <option value="0">Draft</option>
                <option value="1">Needs Review</option>
                <option value="2">Ready</option>
                <option value="3">Published</option>
                <option value="4">Failed</option>
            </select>
        </div>

        {{-- Duplicate filter --}}
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer">
            <input type="checkbox" wire:model.live="filterDuplicates"
                   class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
            Duplicates only
        </label>

        {{-- Edited filter --}}
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer">
            <input type="checkbox" wire:model.live="filterEdited"
                   class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
            Edited only
        </label>

        {{-- Status badges --}}
        <div class="ml-auto flex items-center gap-2 flex-wrap">
            @foreach([0=>'gray',1=>'yellow',2=>'blue',3=>'green',4=>'red'] as $val => $col)
                @if(isset($statusCounts[$val]) && $statusCounts[$val] > 0)
                    @php $labels = [0=>'Draft',1=>'Review',2=>'Ready',3=>'Published',4=>'Failed']; @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        @if($col==='gray') bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                        @elseif($col==='yellow') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300
                        @elseif($col==='blue') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                        @elseif($col==='green') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300
                        @else bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300
                        @endif
                    ">
                        {{ $labels[$val] }}: {{ $statusCounts[$val] }}
                    </span>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ── Bulk actions ─────────────────────────────────────────────────── --}}
    @if(count($selectedIds) > 0)
    <div class="flex flex-wrap items-center gap-2 p-3 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-700">
        <span class="text-sm font-medium text-primary-700 dark:text-primary-300">
            {{ count($selectedIds) }} selected
        </span>
        <button wire:click="publishSelected"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg bg-green-600 hover:bg-green-700 text-white transition-colors">
            &#x1F680; Publish READY
        </button>
        <button wire:click="saveDraftSelected"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-600 hover:bg-gray-700 text-white transition-colors">
            &#x1F4C4; Save as Draft
        </button>
        <button wire:click="autoFillPrices"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-500 hover:bg-amber-600 text-white transition-colors">
            &#x20B9; Auto-fill Prices
        </button>
        <button wire:click="regenerateDescriptions"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg bg-purple-600 hover:bg-purple-700 text-white transition-colors">
            &#x2728; Regenerate Descriptions
        </button>
        <button wire:click="deleteSelected"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors"
                wire:confirm="Delete {{ count($selectedIds) }} selected rows? This cannot be undone.">
            &#x1F5D1; Delete
        </button>
        <button wire:click="$set('selectedIds', []); $set('selectAll', false)"
                class="ml-auto text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
            Clear selection
        </button>
    </div>
    @endif

    {{-- ── Empty state ──────────────────────────────────────────────────── --}}
    @if($previews->isEmpty())
    <div class="flex flex-col items-center justify-center py-16 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="text-5xl text-gray-300 dark:text-gray-600 mb-3">&#x1F4E5;</div>
        <h3 class="text-base font-medium text-gray-600 dark:text-gray-300">No previews found</h3>
        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Upload a PDF catalogue above to begin extraction.</p>
    </div>
    @else

    {{-- ── Review Table ─────────────────────────────────────────────────── --}}
    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm bg-white dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="w-10 px-3 py-3">
                        <input type="checkbox" wire:model.live="selectAll"
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-600">
                    </th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">Image</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product Name</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-36">Category</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Price (&#8377;)</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">Stock</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">Score</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-28">Status</th>
                    <th class="px-3 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-36">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                @foreach($previews as $preview)
                @php
                    $statusVal = $preview->status->value;
                    $badge = match($statusVal) {
                        0 => ['label'=>'Draft',        'cls'=>'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
                        1 => ['label'=>'Needs Review', 'cls'=>'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300'],
                        2 => ['label'=>'Ready',        'cls'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'],
                        3 => ['label'=>'Published',    'cls'=>'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'],
                        4 => ['label'=>'Failed',       'cls'=>'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'],
                        default => ['label'=>'?', 'cls'=>'bg-gray-100 text-gray-700'],
                    };
                    $score      = $preview->validationScore();
                    $scoreColor = $score >= 80 ? 'text-green-600 dark:text-green-400'
                                : ($score >= 50 ? 'text-amber-500 dark:text-amber-400'
                                : 'text-red-500 dark:text-red-400');
                    $isEditing  = isset($editValues[$preview->id]);
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors @if($isEditing) ring-2 ring-inset ring-primary-400 @endif">

                    {{-- Checkbox --}}
                    <td class="px-3 py-2 text-center">
                        <input type="checkbox"
                               wire:model.live="selectedIds"
                               value="{{ $preview->id }}"
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-600">
                    </td>

                    {{-- Preview image --}}
                    <td class="px-3 py-2">
@if($preview->preview_image_path)
                             <img src="{{ Storage::url($preview->preview_image_path) }}"
                                  alt="{{ $preview->name }}"
                                  class="w-14 h-14 object-cover rounded-lg border border-gray-200 dark:border-gray-600"
                                  onerror="this.src='{{ asset('images/placeholder.svg') }}'">
                         @else
                            <div class="w-14 h-14 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-2xl text-gray-300">
                                &#x1F5BC;
                            </div>
                        @endif
                    </td>

                    {{-- Product name (inline-editable) --}}
                    <td class="px-3 py-2 min-w-0">
                        @if($isEditing)
                            <input type="text"
                                   wire:model.defer="editValues.{{ $preview->id }}.name"
                                   class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-primary-500"
                                   placeholder="Product name">
                        @else
                            <div class="flex items-center gap-1.5">
                                <span class="font-medium text-gray-800 dark:text-gray-100 truncate max-w-xs"
                                      title="{{ $preview->name }}">{{ $preview->name ?: '—' }}</span>
                                @if($preview->edited_count > 0)
                                    <span title="{{ $preview->edited_count }} edit(s)"
                                          class="flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300 text-[9px] font-bold"
                                          aria-label="edited">
                                        &#x270E;
                                    </span>
                                @endif
                            </div>
                            @if($preview->occurrences_count > 1)
                                <span class="text-xs text-orange-500 dark:text-orange-400">
                                    {{ $preview->occurrences_count }}&times; duplicate
                                </span>
                            @endif
                        @endif
                    </td>

                    {{-- Category --}}
                    <td class="px-3 py-2">
                        @if($isEditing)
                            <select wire:model.defer="editValues.{{ $preview->id }}.category_id"
                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-primary-500">
                                <option value="">— select —</option>
                                @foreach($categoryOptions as $catId => $catName)
                                    <option value="{{ $catId }}">{{ $catName }}</option>
                                @endforeach
                            </select>
                        @else
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $preview->category?->name ?? '—' }}</span>
                        @endif
                    </td>

                    {{-- Price --}}
                    <td class="px-3 py-2">
                        @if($isEditing)
                            <input type="number" step="0.01" min="0"
                                   wire:model.defer="editValues.{{ $preview->id }}.price"
                                   class="w-24 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-primary-500"
                                   placeholder="0.00">
                        @else
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $preview->price ? '&#8377;' . number_format($preview->price, 2) : '—' }}
                            </span>
                        @endif
                    </td>

                    {{-- Stock --}}
                    <td class="px-3 py-2">
                        @if($isEditing)
                            <input type="number" min="0"
                                   wire:model.defer="editValues.{{ $preview->id }}.stock"
                                   class="w-16 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-primary-500"
                                   placeholder="1">
                        @else
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $preview->stock ?? 1 }}</span>
                        @endif
                    </td>

                    {{-- Validation score --}}
                    <td class="px-3 py-2 text-center">
                        <span class="text-sm font-semibold {{ $scoreColor }}">{{ $score }}</span>
                        @if(is_array($preview->validation_result) && count($preview->validation_result['blocking'] ?? []) > 0)
                            <div class="text-[10px] text-red-500 dark:text-red-400 leading-tight mt-0.5">
                                {{ count($preview->validation_result['blocking']) }} error(s)
                            </div>
                        @endif
                    </td>

                    {{-- Status badge --}}
                    <td class="px-3 py-2 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge['cls'] }}">
                            {{ $badge['label'] }}
                        </span>
                    </td>

                    {{-- Row actions --}}
                    <td class="px-3 py-2">
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            @if($isEditing)
                                <button wire:click="saveRow({{ $preview->id }})"
                                        title="Save"
                                        class="p-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white transition-colors text-xs font-bold">
                                    &#x2713;
                                </button>
                                <button wire:click="$set('editValues.{{ $preview->id }}', null)"
                                        title="Cancel"
                                        class="p-1.5 rounded-lg bg-gray-400 hover:bg-gray-500 text-white transition-colors text-xs font-bold">
                                    &#x2715;
                                </button>
                            @else
                                <button wire:click="startEdit({{ $preview->id }})"
                                        title="Edit"
                                        class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors text-sm">
                                    &#x270F;
                                </button>

                                @if($statusVal !== 2 && $statusVal !== 3)
                                <button wire:click="markReady({{ $preview->id }})"
                                        title="Mark Ready"
                                        class="p-1.5 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-500 dark:text-blue-400 transition-colors text-sm">
                                    &#x2714;
                                </button>
                                @endif

                                @if($preview->edited_count > 0)
                                <button wire:click="openCompareModal({{ $preview->id }})"
                                        title="Compare changes"
                                        class="p-1.5 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/30 text-amber-500 dark:text-amber-400 transition-colors text-sm">
                                    &#x21C4;
                                </button>
                                <button wire:click="resetToAi({{ $preview->id }})"
                                        title="Reset to AI values"
                                        wire:confirm="Reset to AI extracted values? All manual edits will be lost."
                                        class="p-1.5 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 text-purple-500 dark:text-purple-400 transition-colors text-sm">
                                    &#x21BA;
                                </button>
                                @endif

                                <button wire:click="duplicateRow({{ $preview->id }})"
                                        title="Duplicate row"
                                        class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors text-sm">
                                    &#x2398;
                                </button>

                                @if($statusVal !== 3)
                                <button wire:click="deleteRow({{ $preview->id }})"
                                        title="Delete"
                                        wire:confirm="Delete this preview? It will be soft-deleted."
                                        class="p-1.5 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-400 dark:text-red-400 transition-colors text-sm">
                                    &#x1F5D1;
                                </button>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Pagination ───────────────────────────────────────────────────── --}}
    <div class="mt-4">
        {{ $previews->links() }}
    </div>

    @endif {{-- end not-empty --}}

    {{-- ── Compare Changes Modal ───────────────────────────────────────── --}}
    @if($showCompareModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Compare Changes</h3>
                <button wire:click="closeCompareModal"
                        class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 text-lg leading-none">
                    &#x2715;
                </button>
            </div>
            <div class="overflow-y-auto px-6 py-4 flex-1">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">AI Extracted</h4>
                        <div class="space-y-2">
                            @foreach($compareAi as $field => $value)
                            @php $changed = ($compareEdited[$field] ?? null) != $value; @endphp
                            <div class="rounded-lg p-2.5 {{ $changed ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/50' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                                <div class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $field }}</div>
                                <div class="text-sm text-gray-800 dark:text-gray-200 mt-0.5 break-words">{{ $value ?: '—' }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Edited Values</h4>
                        <div class="space-y-2">
                            @foreach($compareEdited as $field => $value)
                            @php $changed = ($compareAi[$field] ?? null) != $value; @endphp
                            <div class="rounded-lg p-2.5 {{ $changed ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700/50' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                                <div class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $field }}</div>
                                <div class="text-sm text-gray-800 dark:text-gray-200 mt-0.5 break-words">{{ $value ?: '—' }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                <button wire:click="closeCompareModal"
                        class="px-4 py-2 text-sm font-medium rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

@script
<script>
    $wire.on('conflict-warning', ({ id, message }) => {
        console.warn('[BulkUploadPreviewGrid] conflict on row', id, message);
    });
    $wire.on('row-saved', ({ id }) => {
        console.debug('[BulkUploadPreviewGrid] row saved:', id);
    });
</script>
@endscript
