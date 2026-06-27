<x-filament-panels::page>
    <form wire:submit.prevent="$refresh" class="grid gap-4 md:grid-cols-5">
        <input type="date" wire:model="from" class="rounded border-gray-300" />
        <input type="date" wire:model="to" class="rounded border-gray-300" />
        <select wire:model="paymentStatus" class="rounded border-gray-300">
            <option value="">All payments</option>
            <option value="pending">Pending</option>
            <option value="authorized">Authorized</option>
            <option value="captured">Captured</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
        </select>
        <select wire:model="orderStatus" class="rounded border-gray-300">
            <option value="">All orders</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="processing">Processing</option>
            <option value="failed">Failed</option>
            <option value="cancelled">Cancelled</option>
            <option value="completed">Completed</option>
        </select>
        <a href="{{ route('admin.operations.export', array_filter($filters)) }}" class="rounded bg-primary-600 px-4 py-2 text-center text-sm font-medium text-white">Export CSV</a>
    </form>

    <div class="grid gap-4 md:grid-cols-3">
        @foreach([
            'Orders today' => $metrics['orders_today'],
            'Revenue today' => 'INR ' . number_format($metrics['revenue_today'], 2),
            'Pending payments' => $metrics['pending_payments'],
            'Failed payments' => $metrics['failed_payments'],
            'Refund count' => $metrics['refund_count'],
            'Stock conflicts' => $metrics['stock_conflicts'],
        ] as $label => $value)
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="text-sm text-gray-500">{{ $label }}</div>
                <div class="mt-2 text-2xl font-semibold">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold">Recent Webhook Events</h2>
        <div class="divide-y divide-gray-100">
            @forelse($metrics['recent_webhook_events'] as $event)
                <div class="py-2 text-sm">
                    <span class="font-medium">{{ $event->created_at->format('M d, H:i:s') }}</span>
                    <span>{{ $event->action }}</span>
                    <span class="text-gray-500">{{ $event->status }}</span>
                    <span class="text-gray-400">{{ $event->correlation_id }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No webhook events found.</p>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
