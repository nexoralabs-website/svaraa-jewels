<?php

namespace App\Models;

use App\Enums\UploadBatchStepStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadBatchStep extends Model
{
    // ── Mass assignment ───────────────────────────────────────────────────

    protected $fillable = [
        'batch_uuid',
        'step',
        'status',
        'started_at',
        'finished_at',
        'duration_ms',
        'metadata',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'status'      => UploadBatchStepStatus::class,
            'metadata'    => 'array',
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────

    /** @return BelongsTo<UploadBatch, UploadBatchStep> */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(UploadBatch::class, 'batch_uuid', 'id');
    }

    // ── Domain helpers ────────────────────────────────────────────────────

    /**
     * Transition to RUNNING and record the start timestamp.
     * Idempotent — safe to call on an already-running step.
     */
    public function markRunning(): self
    {
        $this->status     = UploadBatchStepStatus::RUNNING;
        $this->started_at = $this->started_at ?? now();

        return $this;
    }

    /**
     * Transition to COMPLETED, record end time and duration.
     */
    public function markCompleted(): self
    {
        $finishedAt = now();

        $this->status      = UploadBatchStepStatus::COMPLETED;
        $this->finished_at = $finishedAt;
        $this->duration_ms = $this->started_at
            ? (int) ($this->started_at->diffInMilliseconds($finishedAt))
            : null;

        return $this;
    }

    /**
     * Transition to FAILED, record end time, duration, and optional error detail.
     */
    public function markFailed(?string $error = null): self
    {
        $finishedAt = now();

        $this->status      = UploadBatchStepStatus::FAILED;
        $this->finished_at = $finishedAt;
        $this->duration_ms = $this->started_at
            ? (int) ($this->started_at->diffInMilliseconds($finishedAt))
            : null;

        if ($error !== null) {
            $current          = $this->metadata ?? [];
            $current['error'] = $error;
            $this->metadata   = $current;
        }

        return $this;
    }
}
