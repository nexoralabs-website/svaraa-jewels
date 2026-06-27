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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'razorpay_order_id')) {
                $table->string('razorpay_order_id')->nullable()->after('order_status');
            }

            if (!Schema::hasColumn('orders', 'razorpay_payment_id')) {
                $table->string('razorpay_payment_id')->nullable()->after('razorpay_order_id');
            }

            if (!Schema::hasColumn('orders', 'razorpay_signature')) {
                $table->string('razorpay_signature')->nullable()->after('razorpay_payment_id');
            }

            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('razorpay_signature');
            }

            if (!Schema::hasColumn('orders', 'payment_meta')) {
                $table->json('payment_meta')->nullable()->after('paid_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'razorpay_signature',
                'payment_meta',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
