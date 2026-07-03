<?php

namespace App\Services;

use App\Enums\PreviewStatus;
use App\Models\BulkUploadPreview;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\UploadBatch;
use Illuminate\Database\QueryException;
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
        return DB::transaction(function () use ($preview): Product {
            $lockedPreview = BulkUploadPreview::whereKey($preview->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPreview->published_product_id !== null) {
                $product = Product::find($lockedPreview->published_product_id);

                if ($product === null) {
                    throw new LogicException(
                        "BulkUploadPreview [{$lockedPreview->id}]: already published but linked Product "
                        . "[{$lockedPreview->published_product_id}] does not exist."
                    );
                }

                return $product;
            }

            if ($lockedPreview->status !== PreviewStatus::READY) {
                throw new LogicException(
                    "BulkUploadPreview [{$lockedPreview->id}]: must be in READY status to publish "
                    . "(current: {$lockedPreview->status->name})."
                );
            }

            try {
                $product = Product::firstOrCreate(
                    ['bulk_upload_preview_id' => $lockedPreview->id],
                    [
                        'category_id'          => $lockedPreview->category_id,
                        'name'                 => $lockedPreview->name,
                        'slug'                 => $lockedPreview->slug,
                        'price'                => $lockedPreview->price,
                        'description'          => $lockedPreview->description,
                        'thumbnail'            => $lockedPreview->preview_image_path,
                        'status'               => true,
                        'stock'                => $lockedPreview->stock ?? 1,
                        'meta_title'           => $lockedPreview->meta_title,
                        'meta_description'     => $lockedPreview->meta_description,
                        'published_by'         => $lockedPreview->user_id,
                        'published_batch_uuid' => $lockedPreview->batch_uuid,
                    ]
                );
            } catch (QueryException $e) {
                if ($this->isBulkUploadPreviewKeyViolation($e)) {
                    $product = Product::where('bulk_upload_preview_id', $lockedPreview->id)->first();

                    if ($product === null) {
                        throw $e;
                    }
                } else {
                    throw $e;
                }
            }

            if ($lockedPreview->published_product_id === null) {
                $lockedPreview->markPublished($product->id)->save();
            }

            if ($product->wasRecentlyCreated) {
                $this->createProductImages($product, $lockedPreview);
            }

            return $product;
        });
    }

    private function isBulkUploadPreviewKeyViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;
        $message  = $exception->getMessage();

        return in_array($sqlState, ['23000', '23505'], true)
            && str_contains($message, 'bulk_upload_preview_id');
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
