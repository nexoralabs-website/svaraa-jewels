<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bulk_upload_previews', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_uuid');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('stock')->default(1);
            
            $table->string('preview_image_path')->nullable();
            $table->json('gallery_images')->nullable();
            
            $table->tinyInteger('status')->default(1); // App\Enums\PreviewStatus (default 1 = NEEDS_REVIEW)
            $table->string('sku')->nullable();
            $table->string('slug')->nullable();
            
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            $table->string('source', 32); // pdf, image
            $table->string('source_pdf')->nullable();
            $table->integer('pdf_page')->nullable();
            
            $table->integer('occurrences_count')->default(1);
            $table->json('duplicate_pages')->nullable();
            $table->boolean('is_placeholder')->default(false);
            
            $table->json('ai_extracted_data');
            $table->json('edited_fields')->nullable();
            $table->integer('edited_count')->default(0);
            $table->json('validation_result')->nullable(); // Warnings, blocking, and readiness score
            $table->json('processing_metadata')->nullable();
            
            $table->string('content_hash', 64)->nullable();
            $table->unsignedBigInteger('duplicate_of_id')->nullable();
            $table->unsignedBigInteger('published_product_id')->nullable();
            
            $table->integer('version')->default(1); // Optimistic locking version
            
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('batch_uuid')->references('id')->on('upload_batches')->onDelete('cascade');
            
            // Composite Indexes
            $table->index(['batch_uuid', 'status'], 'previews_batch_status_idx');
            $table->index(['status', 'created_at'], 'previews_status_created_idx');
            $table->index(['published_product_id', 'status'], 'previews_published_status_idx');
            $table->index(['duplicate_of_id', 'status'], 'previews_duplicate_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_upload_previews');
    }
};
