<x-filament-panels::page>
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold">Immutable Payment History</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b text-gray-500">
                        <th class="py-2">Time</th>
                        <th>Order</th>
                        <th>Action</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Correlation</th>
                        <th>Actor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentLogs as $log)
                        <tr class="border-b">
                            <td class="py-2">{{ $log->created_at->format('M d, H:i:s') }}</td>
                            <td>{{ $log->order?->order_number ?? 'N/A' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->status }}</td>
                            <td>{{ $log->payment_id ?? 'N/A' }}</td>
                            <td>{{ $log->correlation_id ?? 'N/A' }}</td>
                            <td>{{ $log->actor?->email ?? 'system' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold">Admin Activity</h2>
        <div class="divide-y divide-gray-100">
            @forelse($activityLogs as $activity)
                <div class="py-2 text-sm">
                    <span class="font-medium">{{ $activity->acted_at->format('M d, H:i:s') }}</span>
                    <span>{{ $activity->action }}</span>
                    <span class="text-gray-500">{{ $activity->actor?->email ?? 'system' }}</span>
                    <span class="text-gray-400">{{ $activity->order?->order_number }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No admin activity recorded.</p>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
