<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-2">Queue Depth</h3>
            <p class="text-3xl font-bold"><?php echo e(app(\App\Services\BulkUploadAnalyticsService::class)->collectQueueMetrics()['queue_depth']); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-2">Failed Jobs (24h)</h3>
            <p class="text-3xl font-bold text-red-600"><?php echo e(app(\App\Services\BulkUploadAnalyticsService::class)->collectQueueMetrics()['failed_jobs']); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-2">Publish Success Rate</h3>
            <p class="text-3xl font-bold text-green-600"><?php echo e(app(\App\Services\BulkUploadAnalyticsService::class)->collectBatchMetrics()['publish_success_percent']); ?>%</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-2">Storage Used</h3>
            <p class="text-3xl font-bold"><?php echo e(app(\App\Services\BulkUploadAnalyticsService::class)->collectStorageMetrics()['storage_used_mb']); ?> MB</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
            <ul class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\UploadBatch::latest()->take(5)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li class="flex items-center justify-between border-b pb-2">
                        <span class="text-sm"><?php echo e($batch->id); ?> - <?php echo e($batch->status); ?></span>
                        <span class="text-xs text-gray-500"><?php echo e($batch->created_at->diffForHumans()); ?></span>
                    </li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="<?php echo e(route('filament.admin.pages.bulk-upload-page')); ?>" class="block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">New Upload</a>
                <a href="<?php echo e(route('filament.admin.pages.bulk-upload-review')); ?>" class="block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Review Queue</a>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/filament/admin/pages/bulk-upload-operations-dashboard.blade.php ENDPATH**/ ?>