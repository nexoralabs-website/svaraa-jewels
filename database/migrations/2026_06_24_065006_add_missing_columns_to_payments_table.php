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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_gateway')->nullable()->after('payment_method');
            $table->string('gateway_order_id')->nullable()->after('payment_gateway');
            $table->string('currency')->default('INR')->after('amount');
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->json('payment_meta')->nullable()->after('paid_at');
            $table->string('refund_status')->nullable()->after('payment_meta');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('refund_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'gateway_order_id',
                'currency',
                'paid_at',
                'payment_meta',
                'refund_status',
                'refund_amount',
            ]);
        });
    }
};
