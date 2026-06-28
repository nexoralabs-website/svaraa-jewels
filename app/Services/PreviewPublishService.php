<?php

namespace App\Services;

use App\Enums\PreviewStatus;
use App\Models\BulkUploadPreview;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\UploadBatch;
use Illuminate\Support\Facades\DB;
use LogicException;
use Throwable;

class PreviewPublishService
{
    // ── Single publish ────────────────────────────────────────────────────

    /**
     * Publish a single READY preview as a live Product.
     *
     * Rules:
     *  - Wrapped in a DB transaction
     *  - Refused if preview status is not READY
     *  - Refused if already published (published_product_id is set)
     *  - Creates the Product row
     *  - Creates ProductImage rows from gallery_images + preview_image_path
     *  - Calls markPublished() on the preview and persists both records
     *
     * @throws LogicException for business-rule violations
     * @throws Throwable      re-thrown on unexpected DB failure
     */
    public function publishSingle(BulkUploadPreview $preview): Product
    {
        if ($preview->status !== PreviewStatus::READY) {
            throw new LogicException(
                "BulkUploadPreview [{$preview->id}]: must be in READY status to publish "
                . "(current: {$preview->status->name})."
            );
        }

        if ($preview->published_product_id !== null) {
            throw new LogicException(
                "BulkUploadPreview [{$preview->id}]: already published as product "
                . "[{$preview->published_product_id}]."
            );
        }

        return DB::transaction(function () use ($preview): Product {
            // ── Create Product ────────────────────────────────────────────
            $product = Product::create([
                'category_id'            => $preview->category_id,
                'name'                   => $preview->name,
                'slug'                   => $preview->slug,     // auto-deduped by model boot
                'price'                  => $preview->price,
                'description'            => $preview->description,
                'thumbnail'              => $preview->preview_image_path,
                'status'                 => true,
                'stock'                  => $preview->stock ?? 1,
                'meta_title'             => $preview->meta_title,
                'meta_description'       => $preview->meta_description,
                'bulk_upload_preview_id' => $preview->id,
                'published_by'           => $preview->user_id,
                'published_batch_uuid'   => $preview->batch_uuid,
            ]);

            // ── Create product images ─────────────────────────────────────
            $this->createProductImages($product, $preview);

            // ── Link preview back to product ──────────────────────────────
            $preview->markPublished($product->id)->save();

            return $product;
        });
    }

    // ── Batch publish ─────────────────────────────────────────────────────

    /**
     * Publish all READY previews in a batch, processing in chunks of 50.
     *
     * Returns a metrics array:
     * [
     *   'published'   => int,
     *   'failed'      => int,
     *   'duration_ms' => int,
     *   'errors'      => array<int, string>,  // preview_id => message
     * ]
     *
     * Each preview is wrapped in its own transaction so one failure does not
     * roll back successfully published products.
     */
    public function publishBatch(UploadBatch $batch): array
    {
        $startMs   = (int) (microtime(true) * 1000);
        $published = 0;
        $failed    = 0;
        $errors    = [];

        $batch->previews()
            ->where('status', PreviewStatus::READY->value)
            ->whereNull('published_product_id')
            ->chunkById(50, function ($previews) use (&$published, &$failed, &$errors): void {
                foreach ($previews as $preview) {
                    try {
                        $this->publishSingle($preview);
                        $published++;
                    } catch (Throwable $e) {
                        $failed++;
                        $errors[$preview->id] = $e->getMessage();
                    }
                }
            });

        $durationMs = (int) (microtime(true) * 1000) - $startMs;

        return [
            'published'   => $published,
            'failed'      => $failed,
            'duration_ms' => $durationMs,
            'errors'      => $errors,
        ];
    }

    // ── Internals ─────────────────────────────────────────────────────────

    /**
     * Build ProductImage rows from the preview's gallery_images array
     * and the preview_image_path (treated as the primary image when present).
     */
    private function createProductImages(Product $product, BulkUploadPreview $preview): void
    {
        $gallery = is_array($preview->gallery_images) ? $preview->gallery_images : [];

        // Primary thumbnail — always inserted first, marked is_primary = true
        if (! empty($preview->preview_image_path)) {
            ProductImage::create([
                'product_id' => $product->id,
                'image'      => $preview->preview_image_path,
                'is_primary' => true,
            ]);
        }

        // Additional gallery images — not primary
        foreach ($gallery as $imagePath) {
            if (empty($imagePath) || $imagePath === $preview->preview_image_path) {
                continue; // skip empty or duplicate of the primary
            }

            ProductImage::create([
                'product_id' => $product->id,
                'image'      => $imagePath,
                'is_primary' => false,
            ]);
        }
    }
}
