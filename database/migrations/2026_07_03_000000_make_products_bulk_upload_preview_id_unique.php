<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicate = DB::table('products')
            ->select('bulk_upload_preview_id', DB::raw('COUNT(*) as duplicate_count'))
            ->whereNotNull('bulk_upload_preview_id')
            ->groupBy('bulk_upload_preview_id')
            ->having('duplicate_count', '>', 1)
            ->limit(1)
            ->first();

        if ($duplicate !== null) {
            throw new \RuntimeException(sprintf(
                'Cannot add UNIQUE constraint to products.bulk_upload_preview_id: duplicate preview id %s exists.',
                $duplicate->bulk_upload_preview_id,
            ));
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['bulk_upload_preview_id']);
            $table->unique('bulk_upload_preview_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['bulk_upload_preview_id']);
            $table->index('bulk_upload_preview_id');
        });
    }
};
