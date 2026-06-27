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
            $table->foreignId('user_id')->nullable()->change();
            $table->decimal('subtotal', 10, 2)->after('order_number');
            $table->decimal('discount', 10, 2)->default(0)->after('subtotal');
            $table->decimal('shipping', 10, 2)->default(0)->after('discount');
            $table->decimal('tax', 10, 2)->default(0)->after('shipping');
            $table->decimal('total', 10, 2)->after('tax');
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->string('customer_name')->after('payment_method');
            $table->string('customer_email')->after('customer_name');
            $table->string('customer_phone')->after('customer_email');
            $table->text('shipping_address')->after('customer_phone');
            $table->text('notes')->nullable()->after('shipping_address');
            
            $table->renameColumn('status', 'order_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('order_status', 'status');
            $table->dropColumn([
                'subtotal',
                'discount',
                'shipping',
                'tax',
                'total',
                'payment_method',
                'customer_name',
                'customer_email',
                'customer_phone',
                'shipping_address',
                'notes'
            ]);
        });
    }
};
