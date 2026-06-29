<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bulk_upload_previews', function (Blueprint $table) {
            $table->dropForeign(['batch_uuid']);
        });
    }

    public function down(): void
    {
        Schema::table('bulk_upload_previews', function (Blueprint $table) {
            $table->foreign('batch_uuid')
                ->references('id')
                ->on('upload_batches')
                ->onDelete('cascade');
        });
    }
};
