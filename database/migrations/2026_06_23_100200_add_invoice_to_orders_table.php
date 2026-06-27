<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->unique()->after('order_number');
            }
            if (! Schema::hasColumn('orders', 'invoice_generated_at')) {
                $table->timestamp('invoice_generated_at')->nullable()->after('invoice_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'invoice_generated_at']);
        });
    }
};
