<?php

namespace App\Services;

use App\Models\BulkUploadEvent;
use App\Models\BulkUploadPreview;
use App\Models\UploadBatch;

class BulkUploadAuditService
{
    public static function record(
        string $batchUuid,
        string $eventType,
        ?string $previewId = null,
        ?string $actorId = null,
        ?array $oldState = null,
        ?array $newState = null,
        ?array $metadata = null,
    ): BulkUploadEvent {
        return BulkUploadEvent::create([
            'batch_uuid' => $batchUuid,
            'preview_id' => $previewId,
            'event_type' => $eventType,
            'actor_id' => $actorId,
            'old_state' => $oldState,
            'new_state' => $newState,
            'metadata' => $metadata,
        ]);
    }

    public static function getTimelineForBatch(string $batchUuid)
    {
        return BulkUploadEvent::where('batch_uuid', $batchUuid)
            ->latest()
            ->paginate(50);
    }

    public static function getTimelineForPreview(string $previewId)
    {
        return BulkUploadEvent::where('preview_id', $previewId)
            ->latest()
            ->paginate(50);
    }
}
