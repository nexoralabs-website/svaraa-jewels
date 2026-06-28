<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bulk_upload_job_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_uuid')->index();
            $table->string('job_name', 128);
            $table->tinyInteger('status')->default(0); // 0 = pending, 1 = running, 2 = completed, 3 = failed
            $table->integer('attempt')->default(1);
            $table->string('worker')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->decimal('memory_mb', 8, 2)->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_upload_job_logs');
    }
};
