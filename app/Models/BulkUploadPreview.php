<?php

namespace App\Models;

use App\Enums\PreviewStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

class BulkUploadPreview extends Model
{
    use SoftDeletes;

    // ── Mass assignment ───────────────────────────────────────────────────

    protected $fillable = [
        'batch_uuid',
        'user_id',
        'name',
        'category_id',
        'description',
        'price',
        'stock',
        'preview_image_path',
        'gallery_images',
        'status',
        'sku',
        'slug',
        'meta_title',
        'meta_description',
        'source',
        'source_pdf',
        'pdf_page',
        'occurrences_count',
        'duplicate_pages',
        'is_placeholder',
        'ai_extracted_data',
        'edited_fields',
        'edited_count',
        'validation_result',
        'processing_metadata',
        'content_hash',
        'duplicate_of_id',
        'published_product_id',
        'version',
        'reviewed_at',
        'published_at',
    ];

    // ── Casts ─────────────────────────────────────────────────────────────

    protected function casts(): array
    {
        return [
            'status'              => PreviewStatus::class,
            'gallery_images'      => 'array',
            'duplicate_pages'     => 'array',
            'ai_extracted_data'   => 'array',
            'edited_fields'       => 'array',
            'validation_result'   => 'array',
            'processing_metadata' => 'array',
            'reviewed_at'         => 'datetime',
            'published_at'        => 'datetime',
            'is_placeholder'      => 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────

    /** @return BelongsTo<UploadBatch, BulkUploadPreview> */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(UploadBatch::class, 'batch_uuid', 'id');
    }

    /** @return BelongsTo<Category, BulkUploadPreview> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Product, BulkUploadPreview> */
    public function publishedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'published_product_id');
    }

    /** @return BelongsTo<User, BulkUploadPreview> */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Query scopes ──────────────────────────────────────────────────────

    /** @param Builder<BulkUploadPreview> $query */
    public function scopeReady(Builder $query): Builder
    {
        return $query->where('status', PreviewStatus::READY->value);
    }

    /** @param Builder<BulkUploadPreview> $query */
    public function scopeNeedsReview(Builder $query): Builder
    {
        return $query->where('status', PreviewStatus::NEEDS_REVIEW->value);
    }

    /** @param Builder<BulkUploadPreview> $query */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PreviewStatus::PUBLISHED->value);
    }

    // ── Domain helpers ────────────────────────────────────────────────────

    /**
     * Whether any field has been manually edited (differs from AI extraction).
     */
    public function isEdited(): bool
    {
        return $this->edited_count > 0
            || (is_array($this->edited_fields) && count($this->edited_fields) > 0);
    }

    /**
     * Whether the validation result contains any blocking errors.
     */
    public function hasBlockingErrors(): bool
    {
        $result = $this->validation_result;

        if (! is_array($result)) {
            return false;
        }

        $blocking = $result['blocking'] ?? $result['errors'] ?? [];

        return is_array($blocking) && count($blocking) > 0;
    }

    /**
     * Numeric readiness score (0–100) from validation_result.
     * Returns 0 when no score is recorded.
     */
    public function validationScore(): int
    {
        $result = $this->validation_result;

        if (! is_array($result)) {
            return 0;
        }

        return (int) ($result['score'] ?? $result['readiness_score'] ?? 0);
    }

    /**
     * Transition this preview to READY status.
     *
     * @throws LogicException when blocking validation errors are present.
     */
    public function markReady(): self
    {
        if ($this->hasBlockingErrors()) {
            throw new LogicException(
                "BulkUploadPreview [{$this->id}]: cannot mark as READY — "
                . 'blocking validation errors are present.'
            );
        }

        $this->status      = PreviewStatus::READY;
        $this->reviewed_at = now();

        return $this;
    }

    /**
     * Transition to PUBLISHED and record publication timestamp.
     *
     * @throws LogicException when a published product already exists for this preview.
     */
    public function markPublished(int $productId): self
    {
        if ($this->published_product_id !== null) {
            throw new LogicException(
                "BulkUploadPreview [{$this->id}]: already linked to product "
                . "[{$this->published_product_id}] — cannot publish again."
            );
        }

        $this->status               = PreviewStatus::PUBLISHED;
        $this->published_at         = now();
        $this->published_product_id = $productId;

        return $this;
    }

    /**
     * Reset editable fields back to whatever the AI originally extracted.
     * Restores: name, category_id, description, price, sku, slug, preview_image_path.
     */
    public function resetToAi(): self
    {
        $ai = $this->ai_extracted_data ?? [];

        $this->name                = $ai['name'] ?? $this->name;
        $this->category_id         = $ai['category_id'] ?? $this->category_id;
        $this->description         = $ai['description'] ?? $this->description;
        $this->price               = $ai['price'] ?? $this->price;
        $this->sku                 = $ai['sku'] ?? $this->sku;
        $this->slug                = $ai['slug'] ?? $this->slug;
        $this->preview_image_path  = $ai['preview_image_path'] ?? $this->preview_image_path;

        // Clear edit tracking
        $this->edited_fields = [];
        $this->edited_count  = 0;

        return $this;
    }

    /**
     * Increment the optimistic locking version counter.
     * Should be called whenever editable fields are changed.
     */
    public function incrementVersion(): self
    {
        $this->version = ($this->version ?? 1) + 1;

        return $this;
    }
}
