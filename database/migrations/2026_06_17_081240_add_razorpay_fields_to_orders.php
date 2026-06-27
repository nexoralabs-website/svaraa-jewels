<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('razorpay_order_id')->nullable();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('payment_signature')->nullable();
            $table->timestamp('paid_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = array_filter([
                'razorpay_order_id',
                'razorpay_payment_id',
                'payment_signature',
                'paid_at',
            ], fn ($column) => Schema::hasColumn('orders', $column));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
