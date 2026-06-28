<?php

namespace App\Services;

use App\Models\BulkUploadPreview;

class PreviewValidationService
{
    // ── Penalty weights ───────────────────────────────────────────────────

    private const PENALTY_BLOCKING = 25;   // per blocking issue (multiple can stack)
    private const PENALTY_WARNING  = 5;    // per warning

    // ── Public API ────────────────────────────────────────────────────────

    /**
     * Validate a preview row and return a structured result array.
     *
     * Return shape:
     * [
     *   'blocking' => string[],   // errors that prevent publishing
     *   'warnings' => string[],   // soft issues that should be reviewed
     *   'score'    => int,        // 0–100 readiness score
     * ]
     */
    public function validate(BulkUploadPreview $preview): array
    {
        $blocking = $this->collectBlocking($preview);
        $warnings = $this->collectWarnings($preview);
        $score    = $this->computeScore($blocking, $warnings);

        return [
            'blocking' => $blocking,
            'warnings' => $warnings,
            'score'    => $score,
        ];
    }

    /**
     * Convenience wrapper — true when the preview has at least one blocking error.
     */
    public function hasBlockingErrors(BulkUploadPreview $preview): bool
    {
        return count($this->collectBlocking($preview)) > 0;
    }

    /**
     * Compute the 0–100 readiness score for a preview.
     * Accepts pre-collected arrays to avoid duplicate DB queries.
     */
    public function computeScore(array $blocking, array $warnings): int
    {
        $penalty = (count($blocking) * self::PENALTY_BLOCKING)
                 + (count($warnings) * self::PENALTY_WARNING);

        return max(0, 100 - $penalty);
    }

    // ── Blocking checks ───────────────────────────────────────────────────

    /**
     * Collect all blocking validation errors.
     * Each rule uses exists() — no count loops.
     */
    private function collectBlocking(BulkUploadPreview $preview): array
    {
        $errors = [];

        if (empty(trim((string) $preview->name))) {
            $errors[] = 'Product name is required.';
        }

        if (empty($preview->category_id)) {
            $errors[] = 'Category is required.';
        }

        if ($preview->price === null || (float) $preview->price <= 0) {
            $errors[] = 'Price must be greater than zero.';
        }

        if (! $this->isValidImagePath($preview->preview_image_path)) {
            $errors[] = 'A valid preview image path is required.';
        }

        return $errors;
    }

    // ── Warning checks ────────────────────────────────────────────────────

    /**
     * Collect soft warnings via exists() only — no COUNT queries.
     */
    private function collectWarnings(BulkUploadPreview $preview): array
    {
        $warnings = [];

        // Duplicate SKU across other non-deleted previews
        if (
            ! empty($preview->sku)
            && BulkUploadPreview::where('sku', $preview->sku)
                ->where('id', '!=', $preview->id ?? 0)
                ->whereNull('deleted_at')
                ->exists()
        ) {
            $warnings[] = 'Duplicate SKU detected in another preview.';
        }

        // Duplicate slug across other non-deleted previews
        if (
            ! empty($preview->slug)
            && BulkUploadPreview::where('slug', $preview->slug)
                ->where('id', '!=', $preview->id ?? 0)
                ->whereNull('deleted_at')
                ->exists()
        ) {
            $warnings[] = 'Duplicate slug detected in another preview.';
        }

        // Duplicate content_hash (same image + name fingerprint)
        if (
            ! empty($preview->content_hash)
            && BulkUploadPreview::where('content_hash', $preview->content_hash)
                ->where('id', '!=', $preview->id ?? 0)
                ->whereNull('deleted_at')
                ->exists()
        ) {
            $warnings[] = 'Duplicate image/content fingerprint detected in another preview.';
        }

        // Duplicate name + category combination
        if (
            ! empty($preview->name)
            && ! empty($preview->category_id)
            && BulkUploadPreview::where('name', $preview->name)
                ->where('category_id', $preview->category_id)
                ->where('id', '!=', $preview->id ?? 0)
                ->whereNull('deleted_at')
                ->exists()
        ) {
            $warnings[] = 'Another preview with the same name and category already exists.';
        }

        return $warnings;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * An image path is "valid" when it is a non-empty string.
     * Actual file-existence checks belong to a storage layer, not here.
     */
    private function isValidImagePath(?string $path): bool
    {
        return ! empty(trim((string) $path));
    }
}
