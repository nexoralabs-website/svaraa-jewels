<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete()->after('payment_meta');
            }
            if (! Schema::hasColumn('orders', 'coupon_code')) {
                $table->string('coupon_code')->nullable()->after('coupon_id');
            }
            if (! Schema::hasColumn('orders', 'coupon_discount')) {
                $table->decimal('coupon_discount', 10, 2)->default(0)->after('coupon_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'coupon_code', 'coupon_discount']);
        });
    }
};
