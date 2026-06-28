<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('upload_batch_steps', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_uuid')->index();
            $table->string('step', 64); // extract_pdf, generate_metadata, validate, publish
            $table->tinyInteger('status')->default(0)->index(); // App\Enums\UploadBatchStepStatus
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('batch_uuid')->references('id')->on('upload_batches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_batch_steps');
    }
};
