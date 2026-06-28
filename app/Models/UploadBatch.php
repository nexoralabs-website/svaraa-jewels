<?php

namespace App\Models;

use App\Enums\UploadBatchStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class UploadBatch extends Model
{
    use HasUuids;

    // ── Primary key ───────────────────────────────────────────────────────

    /** @var bool UUID primary — not auto-incrementing */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    // ── Mass assignment ───────────────────────────────────────────────────

    protected $fillable = [
        'user_id',
        'status',
        'progress_message',
        'total_pages',
        'processed_pages',
        'metadata',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'status'   => UploadBatchStatus::class,
            'metadata' => 'array',
        ];
    }

    // ── Status regression guard ───────────────────────────────────────────

    /**
     * Statuses that are considered terminal — once reached, the batch
     * cannot be moved back to an earlier stage.
     *
     * Allowed forward transitions only; COMPLETED and FAILED are terminal.
     */
    private const STATUS_ORDER = [
        UploadBatchStatus::QUEUED->value       => 0,
        UploadBatchStatus::EXTRACTING->value   => 1,
        UploadBatchStatus::PROCESSING->value   => 2,
        UploadBatchStatus::REVIEW_READY->value => 3,
        UploadBatchStatus::COMPLETED->value    => 4,
        UploadBatchStatus::FAILED->value       => 5,
    ];

    /**
     * Terminal statuses from which no further transitions are allowed.
     */
    private const TERMINAL_STATUSES = [
        UploadBatchStatus::COMPLETED,
        UploadBatchStatus::FAILED,
    ];

    protected static function booted(): void
    {
        static::updating(function (self $batch) {
            // Guard against status regression
            if (! $batch->isDirty('status')) {
                return;
            }

            // getOriginal('status') may return a raw int OR already-cast enum instance
            $rawOriginal = $batch->getOriginal('status');
            /** @var UploadBatchStatus $oldStatus */
            $oldStatus = $rawOriginal instanceof UploadBatchStatus
                ? $rawOriginal
                : UploadBatchStatus::from((int) $rawOriginal);

            /** @var UploadBatchStatus $newStatus */
            $newStatus = $batch->status instanceof UploadBatchStatus
                ? $batch->status
                : UploadBatchStatus::from((int) $batch->status);

            if (in_array($oldStatus, self::TERMINAL_STATUSES, true)) {
                throw new LogicException(
                    "UploadBatch [{$batch->id}]: cannot transition from terminal status "
                    . "{$oldStatus->name} to {$newStatus->name}."
                );
            }

            $oldOrder = self::STATUS_ORDER[$oldStatus->value] ?? 0;
            $newOrder = self::STATUS_ORDER[$newStatus->value] ?? 0;

            if ($newOrder < $oldOrder) {
                throw new LogicException(
                    "UploadBatch [{$batch->id}]: status regression from "
                    . "{$oldStatus->name} → {$newStatus->name} is not allowed."
                );
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<BulkUploadPreview> */
    public function previews(): HasMany
    {
        return $this->hasMany(BulkUploadPreview::class, 'batch_uuid', 'id');
    }

    /** @return HasMany<BulkUploadJobLog> */
    public function jobLogs(): HasMany
    {
        return $this->hasMany(BulkUploadJobLog::class, 'batch_uuid', 'id');
    }

    // ── Domain helpers ────────────────────────────────────────────────────

    /**
     * Returns processing progress as an integer 0–100.
     * Returns 100 immediately when the batch is COMPLETED.
     */
    public function progressPercent(): int
    {
        if ($this->status === UploadBatchStatus::COMPLETED) {
            return 100;
        }

        if ($this->total_pages <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->processed_pages / $this->total_pages) * 100));
    }

    /**
     * Whether the batch has reached the COMPLETED terminal state.
     */
    public function isCompleted(): bool
    {
        return $this->status === UploadBatchStatus::COMPLETED;
    }
}
