<?php

namespace App\Services;

use App\Enums\PreviewStatus;
use App\Enums\UploadBatchStatus;
use App\Models\BulkUploadPreview;
use App\Models\UploadBatch;
use App\Models\User;
use Illuminate\Support\Str;

class BulkUploadService
{
    // ── Batch ─────────────────────────────────────────────────────────────

    /**
     * Create a new upload batch for the given user.
     *
     * UUID is generated automatically by the HasUuids trait on the model.
     * Status starts as QUEUED; page counters start at 0.
     */
    public function createBatch(?User $user, array $metadata = []): UploadBatch
    {
        return UploadBatch::create([
            'user_id'          => $user?->id,
            'status'           => UploadBatchStatus::QUEUED,
            'total_pages'      => 0,
            'processed_pages'  => 0,
            'metadata'         => $metadata ?: null,
        ]);
    }

    // ── Preview ───────────────────────────────────────────────────────────

    /**
     * Create a new preview row inside a batch.
     *
     * Rules:
     *  - status is always set to NEEDS_REVIEW regardless of $data
     *  - ai_extracted_data is snapshotted from the editable fields in $data
     *  - content_hash is generated from a stable fingerprint of the AI data
     *  - version starts at 1
     */
    public function createPreview(UploadBatch $batch, array $data): BulkUploadPreview
    {
        // Editable fields that AI extracted — snapshot before any human edits
        $aiFields = [
            'name'               => $data['name']               ?? null,
            'category_id'        => $data['category_id']        ?? null,
            'description'        => $data['description']        ?? null,
            'price'              => $data['price']              ?? null,
            'sku'                => $data['sku']                ?? null,
            'slug'               => $data['slug']               ?? null,
            'preview_image_path' => $data['preview_image_path'] ?? null,
        ];

        // Stable hash of the extracted content for duplicate detection
        $contentHash = $this->buildContentHash($aiFields);

        return BulkUploadPreview::create(array_merge($data, [
            'batch_uuid'        => $batch->id,
            'status'            => PreviewStatus::NEEDS_REVIEW,
            'ai_extracted_data' => $aiFields,
            'content_hash'      => $contentHash,
            'edited_fields'     => [],
            'edited_count'      => 0,
            'version'           => 1,
        ]));
    }

    /**
     * Apply human edits to an existing preview.
     *
     * Rules:
     *  - Only fields present in $changes are updated
     *  - edited_fields accumulates the names of changed AI-extractable fields
     *  - edited_count increments for each call that modifies at least one field
     *  - version increments on every successful update
     */
    public function updatePreview(BulkUploadPreview $preview, array $changes): BulkUploadPreview
    {
        // Editable AI-originated fields — track which ones changed
        $trackable = ['name', 'category_id', 'description', 'price', 'sku', 'slug', 'preview_image_path'];

        $currentEdited = is_array($preview->edited_fields) ? $preview->edited_fields : [];
        $newlyEdited   = [];
        $ai            = is_array($preview->ai_extracted_data) ? $preview->ai_extracted_data : [];

        foreach ($trackable as $field) {
            if (! array_key_exists($field, $changes)) {
                continue;
            }

            // Consider a field "edited" when it differs from the AI snapshot
            // (cast both sides to string for a safe loose comparison)
            $aiValue      = (string) ($ai[$field] ?? '');
            $incomingValue = (string) ($changes[$field] ?? '');

            if ($incomingValue !== $aiValue && ! in_array($field, $currentEdited, true)) {
                $newlyEdited[] = $field;
            }
        }

        $editedFields = array_values(array_unique(array_merge($currentEdited, $newlyEdited)));
        $editedCount  = $preview->edited_count + (count($newlyEdited) > 0 ? 1 : 0);

        $preview->fill($changes);
        $preview->edited_fields = $editedFields;
        $preview->edited_count  = $editedCount;
        $preview->incrementVersion();
        $preview->save();

        return $preview;
    }

    /**
     * Clone a preview row into a new draft within the same batch.
     *
     * The duplicate is independent: publication tracking and review state
     * are cleared so it goes back to NEEDS_REVIEW.
     */
    public function duplicatePreview(BulkUploadPreview $preview): BulkUploadPreview
    {
        $clone = $preview->replicate([
            'published_product_id',
            'reviewed_at',
            'published_at',
            'deleted_at',
        ]);

        $clone->status               = PreviewStatus::NEEDS_REVIEW;
        $clone->published_product_id = null;
        $clone->reviewed_at          = null;
        $clone->published_at         = null;
        $clone->version              = 1;
        $clone->save();

        return $clone;
    }

    /**
     * Reset a preview's editable fields back to the AI-extracted snapshot.
     * Delegates to the model's resetToAi() + incrementVersion() helpers,
     * then persists.
     */
    public function resetPreview(BulkUploadPreview $preview): BulkUploadPreview
    {
        $preview->resetToAi()->incrementVersion()->save();

        return $preview;
    }

    /**
     * Soft-delete a preview.
     * Hard deletes are never performed from this service.
     */
    public function deletePreview(BulkUploadPreview $preview): void
    {
        $preview->delete();
    }

    // ── Internals ─────────────────────────────────────────────────────────

    /**
     * Build a deterministic SHA-256 content hash from AI-extracted fields.
     * Used for duplicate image/content detection.
     */
    private function buildContentHash(array $aiFields): string
    {
        // Normalise: lowercase trimmed name + preview_image_path
        $fingerprint = implode('|', [
            strtolower(trim((string) ($aiFields['name'] ?? ''))),
            strtolower(trim((string) ($aiFields['preview_image_path'] ?? ''))),
            (string) ($aiFields['price'] ?? ''),
        ]);

        return hash('sha256', $fingerprint);
    }
}
