<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-2">Queue Depth</h3>
            <p class="text-3xl font-bold">{{ app(\App\Services\BulkUploadAnalyticsService::class)->collectQueueMetrics()['queue_depth'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-2">Failed Jobs (24h)</h3>
            <p class="text-3xl font-bold text-red-600">{{ app(\App\Services\BulkUploadAnalyticsService::class)->collectQueueMetrics()['failed_jobs'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-2">Publish Success Rate</h3>
            <p class="text-3xl font-bold text-green-600">{{ app(\App\Services\BulkUploadAnalyticsService::class)->collectBatchMetrics()['publish_success_percent'] }}%</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-2">Storage Used</h3>
            <p class="text-3xl font-bold">{{ app(\App\Services\BulkUploadAnalyticsService::class)->collectStorageMetrics()['storage_used_mb'] }} MB</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
            <ul class="space-y-3">
                @foreach(\App\Models\UploadBatch::latest()->take(5)->get() as $batch)
                    <li class="flex items-center justify-between border-b pb-2">
                        <span class="text-sm">{{ $batch->id }} - {{ $batch->status }}</span>
                        <span class="text-xs text-gray-500">{{ $batch->created_at->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border">
            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('filament.admin.pages.bulk-upload-page') }}" class="block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">New Upload</a>
                <a href="{{ route('filament.admin.pages.bulk-upload-review') }}" class="block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Review Queue</a>
            </div>
        </div>
    </div>
</x-filament-panels::page>
