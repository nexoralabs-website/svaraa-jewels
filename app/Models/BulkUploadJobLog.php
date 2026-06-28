<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkUploadJobLog extends Model
{
    // ── Mass assignment ───────────────────────────────────────────────────

    protected $fillable = [
        'batch_uuid',
        'job_name',
        'status',
        'attempt',
        'worker',
        'started_at',
        'ended_at',
        'duration_ms',
        'memory_mb',
        'error',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────

    /** @return BelongsTo<UploadBatch, BulkUploadJobLog> */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(UploadBatch::class, 'batch_uuid', 'id');
    }

    // ── Domain helpers ────────────────────────────────────────────────────

    /**
     * Duration in seconds (float), derived from duration_ms.
     * Returns null when duration is not yet recorded.
     */
    public function durationSeconds(): ?float
    {
        if ($this->duration_ms === null) {
            return null;
        }

        return round($this->duration_ms / 1000, 3);
    }

    /**
     * Whether this log entry represents a failed job run.
     * Status 3 = failed (matches migration comment).
     */
    public function isFailed(): bool
    {
        return (int) $this->status === 3;
    }
}
