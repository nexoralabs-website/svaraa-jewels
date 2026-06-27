<x-filament-panels::page>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold">Queue Metrics</h2>
        <div class="grid gap-4 md:grid-cols-4">
            @foreach([
                'Pending jobs' => $snapshot['pending_jobs'],
                'Email jobs' => $snapshot['email_jobs'],
                'Default jobs' => $snapshot['default_jobs'],
                'Failed jobs' => $snapshot['failed_jobs'],
            ] as $label => $value)
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="text-sm text-gray-500">{{ $label }}</div>
                    <div class="mt-2 text-2xl font-semibold">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold">Failed Jobs</h2>
        <div class="divide-y divide-gray-100">
            @forelse($failedJobs as $job)
                <div class="flex items-center justify-between py-2 text-sm">
                    <div>
                        <span class="font-medium">{{ $job->uuid }}</span>
                        <span class="text-gray-500">{{ $job->queue }}</span>
                        <span class="text-gray-400">{{ $job->failed_at }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No failed jobs.</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold">Dead-Letter Candidates</h2>
        <p class="text-sm text-gray-500">Jobs failed for more than 24 hours should be inspected, fixed, and retried or archived.</p>
        <div class="mt-3 divide-y divide-gray-100">
            @forelse($deadLetters as $job)
                <div class="py-2 text-sm">{{ $job->uuid }} · {{ $job->queue }} · {{ $job->failed_at }}</div>
            @empty
                <p class="text-sm text-gray-500">No dead-letter candidates.</p>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
