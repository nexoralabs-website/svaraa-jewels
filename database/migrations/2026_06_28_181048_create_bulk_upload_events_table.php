<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bulk_upload_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_uuid');
            $table->unsignedBigInteger('preview_id')->nullable();
            $table->string('event_type');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('old_state')->nullable();
            $table->json('new_state')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('batch_uuid');
            $table->index('preview_id');
            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_upload_events');
    }
};
