<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use App\Models\Order;
use App\Models\User;

class AdminActivityLogger
{
    public function log(string $action, ?User $actor = null, ?Order $order = null, array $metadata = [], ?string $targetType = null, ?string $targetId = null): AdminActivityLog
    {
        return AdminActivityLog::create([
            'actor_id' => $actor?->id,
            'order_id' => $order?->id,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata,
            'acted_at' => now(),
        ]);
    }
}
