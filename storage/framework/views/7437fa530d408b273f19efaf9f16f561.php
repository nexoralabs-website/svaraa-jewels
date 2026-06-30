<div class="space-y-4">

    
    <div class="flex flex-wrap items-center gap-3 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($activeBatches)): ?>
        <div class="flex items-center gap-2">
            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Batch</label>
            <select wire:model.live="filterBatch"
                    class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-primary-500">
                <option value="">All batches</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activeBatches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <option value="<?php echo e($b['id']); ?>"><?php echo e(substr($b['id'], 0, 8)); ?>… (<?php echo e($b['processed_pages']); ?>/<?php echo e($b['total_pages']); ?>p)</option>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
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

        
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer">
            <input type="checkbox" wire:model.live="filterDuplicates"
                   class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
            Duplicates only
        </label>

        
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 cursor-pointer">
            <input type="checkbox" wire:model.live="filterEdited"
                   class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500">
            Edited only
        </label>

        
        <div class="ml-auto flex items-center gap-2 flex-wrap">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [0=>'gray',1=>'yellow',2=>'blue',3=>'green',4=>'red']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($statusCounts[$val]) && $statusCounts[$val] > 0): ?>
                    <?php $labels = [0=>'Draft',1=>'Review',2=>'Ready',3=>'Published',4=>'Failed']; ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                        <?php if($col==='gray'): ?> bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                        <?php elseif($col==='yellow'): ?> bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300
                        <?php elseif($col==='blue'): ?> bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                        <?php elseif($col==='green'): ?> bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300
                        <?php else: ?> bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300
                        <?php endif; ?>
                    ">
                        <?php echo e($labels[$val]); ?>: <?php echo e($statusCounts[$val]); ?>

                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedIds) > 0): ?>
    <div class="flex flex-wrap items-center gap-2 p-3 bg-primary-50 dark:bg-primary-900/20 rounded-xl border border-primary-200 dark:border-primary-700">
        <span class="text-sm font-medium text-primary-700 dark:text-primary-300">
            <?php echo e(count($selectedIds)); ?> selected
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
                wire:confirm="Delete <?php echo e(count($selectedIds)); ?> selected rows? This cannot be undone.">
            &#x1F5D1; Delete
        </button>
        <button wire:click="$set('selectedIds', []); $set('selectAll', false)"
                class="ml-auto text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
            Clear selection
        </button>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($previews->isEmpty()): ?>
    <div class="flex flex-col items-center justify-center py-16 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="text-5xl text-gray-300 dark:text-gray-600 mb-3">&#x1F4E5;</div>
        <h3 class="text-base font-medium text-gray-600 dark:text-gray-300">No previews found</h3>
        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Upload a PDF catalogue above to begin extraction.</p>
    </div>
    <?php else: ?>

    
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $previews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $preview): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
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
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors <?php if($isEditing): ?> ring-2 ring-inset ring-primary-400 <?php endif; ?>">

                    
                    <td class="px-3 py-2 text-center">
                        <input type="checkbox"
                               wire:model.live="selectedIds"
                               value="<?php echo e($preview->id); ?>"
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-600">
                    </td>

                    
                    <td class="px-3 py-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($preview->preview_image_path): ?>
                            <img src="<?php echo e(asset('storage/' . $preview->preview_image_path)); ?>"
                                 alt="<?php echo e($preview->name); ?>"
                                 class="w-14 h-14 object-cover rounded-lg border border-gray-200 dark:border-gray-600"
                                 onerror="this.src='<?php echo e(asset('images/placeholder.svg')); ?>'">
                        <?php else: ?>
                            <div class="w-14 h-14 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-2xl text-gray-300">
                                &#x1F5BC;
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>

                    
                    <td class="px-3 py-2 min-w-0">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEditing): ?>
                            <input type="text"
                                   wire:model.defer="editValues.<?php echo e($preview->id); ?>.name"
                                   class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-primary-500"
                                   placeholder="Product name">
                        <?php else: ?>
                            <div class="flex items-center gap-1.5">
                                <span class="font-medium text-gray-800 dark:text-gray-100 truncate max-w-xs"
                                      title="<?php echo e($preview->name); ?>"><?php echo e($preview->name ?: '—'); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($preview->edited_count > 0): ?>
                                    <span title="<?php echo e($preview->edited_count); ?> edit(s)"
                                          class="flex-shrink-0 inline-flex items-center justify-center w-4 h-4 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300 text-[9px] font-bold"
                                          aria-label="edited">
                                        &#x270E;
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($preview->occurrences_count > 1): ?>
                                <span class="text-xs text-orange-500 dark:text-orange-400">
                                    <?php echo e($preview->occurrences_count); ?>&times; duplicate
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>

                    
                    <td class="px-3 py-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEditing): ?>
                            <select wire:model.defer="editValues.<?php echo e($preview->id); ?>.category_id"
                                    class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-primary-500">
                                <option value="">— select —</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categoryOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catId => $catName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($catId); ?>"><?php echo e($catName); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        <?php else: ?>
                            <span class="text-sm text-gray-700 dark:text-gray-300"><?php echo e($preview->category?->name ?? '—'); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>

                    
                    <td class="px-3 py-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEditing): ?>
                            <input type="number" step="0.01" min="0"
                                   wire:model.defer="editValues.<?php echo e($preview->id); ?>.price"
                                   class="w-24 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-primary-500"
                                   placeholder="0.00">
                        <?php else: ?>
                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                <?php echo e($preview->price ? '&#8377;' . number_format($preview->price, 2) : '—'); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>

                    
                    <td class="px-3 py-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEditing): ?>
                            <input type="number" min="0"
                                   wire:model.defer="editValues.<?php echo e($preview->id); ?>.stock"
                                   class="w-16 text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-primary-500"
                                   placeholder="1">
                        <?php else: ?>
                            <span class="text-sm text-gray-700 dark:text-gray-300"><?php echo e($preview->stock ?? 1); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>

                    
                    <td class="px-3 py-2 text-center">
                        <span class="text-sm font-semibold <?php echo e($scoreColor); ?>"><?php echo e($score); ?></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($preview->validation_result) && count($preview->validation_result['blocking'] ?? []) > 0): ?>
                            <div class="text-[10px] text-red-500 dark:text-red-400 leading-tight mt-0.5">
                                <?php echo e(count($preview->validation_result['blocking'])); ?> error(s)
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>

                    
                    <td class="px-3 py-2 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($badge['cls']); ?>">
                            <?php echo e($badge['label']); ?>

                        </span>
                    </td>

                    
                    <td class="px-3 py-2">
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEditing): ?>
                                <button wire:click="saveRow(<?php echo e($preview->id); ?>)"
                                        title="Save"
                                        class="p-1.5 rounded-lg bg-green-600 hover:bg-green-700 text-white transition-colors text-xs font-bold">
                                    &#x2713;
                                </button>
                                <button wire:click="$set('editValues.<?php echo e($preview->id); ?>', null)"
                                        title="Cancel"
                                        class="p-1.5 rounded-lg bg-gray-400 hover:bg-gray-500 text-white transition-colors text-xs font-bold">
                                    &#x2715;
                                </button>
                            <?php else: ?>
                                <button wire:click="startEdit(<?php echo e($preview->id); ?>)"
                                        title="Edit"
                                        class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors text-sm">
                                    &#x270F;
                                </button>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusVal !== 2 && $statusVal !== 3): ?>
                                <button wire:click="markReady(<?php echo e($preview->id); ?>)"
                                        title="Mark Ready"
                                        class="p-1.5 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-500 dark:text-blue-400 transition-colors text-sm">
                                    &#x2714;
                                </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($preview->edited_count > 0): ?>
                                <button wire:click="openCompareModal(<?php echo e($preview->id); ?>)"
                                        title="Compare changes"
                                        class="p-1.5 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/30 text-amber-500 dark:text-amber-400 transition-colors text-sm">
                                    &#x21C4;
                                </button>
                                <button wire:click="resetToAi(<?php echo e($preview->id); ?>)"
                                        title="Reset to AI values"
                                        wire:confirm="Reset to AI extracted values? All manual edits will be lost."
                                        class="p-1.5 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 text-purple-500 dark:text-purple-400 transition-colors text-sm">
                                    &#x21BA;
                                </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <button wire:click="duplicateRow(<?php echo e($preview->id); ?>)"
                                        title="Duplicate row"
                                        class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors text-sm">
                                    &#x2398;
                                </button>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusVal !== 3): ?>
                                <button wire:click="deleteRow(<?php echo e($preview->id); ?>)"
                                        title="Delete"
                                        wire:confirm="Delete this preview? It will be soft-deleted."
                                        class="p-1.5 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-400 dark:text-red-400 transition-colors text-sm">
                                    &#x1F5D1;
                                </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="mt-4">
        <?php echo e($previews->links()); ?>

    </div>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> 

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCompareModal): ?>
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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $compareAi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $changed = ($compareEdited[$field] ?? null) != $value; ?>
                            <div class="rounded-lg p-2.5 <?php echo e($changed ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/50' : 'bg-gray-50 dark:bg-gray-700/50'); ?>">
                                <div class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500"><?php echo e($field); ?></div>
                                <div class="text-sm text-gray-800 dark:text-gray-200 mt-0.5 break-words"><?php echo e($value ?: '—'); ?></div>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Edited Values</h4>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $compareEdited; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $changed = ($compareAi[$field] ?? null) != $value; ?>
                            <div class="rounded-lg p-2.5 <?php echo e($changed ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700/50' : 'bg-gray-50 dark:bg-gray-700/50'); ?>">
                                <div class="text-[10px] uppercase tracking-wider text-gray-400 dark:text-gray-500"><?php echo e($field); ?></div>
                                <div class="text-sm text-gray-800 dark:text-gray-200 mt-0.5 break-words"><?php echo e($value ?: '—'); ?></div>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>

    <?php
        $__scriptKey = '880668993-0';
        ob_start();
    ?>
<script>
    $wire.on('conflict-warning', ({ id, message }) => {
        console.warn('[BulkUploadPreviewGrid] conflict on row', id, message);
    });
    $wire.on('row-saved', ({ id }) => {
        console.debug('[BulkUploadPreviewGrid] row saved:', id);
    });
</script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php /**PATH /var/www/html/resources/views/livewire/bulk-upload-preview-grid.blade.php ENDPATH**/ ?>