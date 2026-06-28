<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('bulk_upload_preview_id')->nullable()->index()->after('id');
            $table->foreignId('published_by')->nullable()->index()->after('bulk_upload_preview_id')->constrained('users')->nullOnDelete();
            $table->uuid('published_batch_uuid')->nullable()->index()->after('published_by');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['published_by']);
            $table->dropColumn(['bulk_upload_preview_id', 'published_by', 'published_batch_uuid']);
        });
    }
};
