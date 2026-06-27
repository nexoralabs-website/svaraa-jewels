<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->foreignId('actor_id')->nullable()->after('order_id')->constrained('users')->nullOnDelete();
            $table->string('correlation_id')->nullable()->after('provider')->index();
            $table->string('payment_id')->nullable()->after('correlation_id')->index();
            $table->string('customer_email')->nullable()->after('payment_id')->index();
            $table->string('event_type')->nullable()->after('customer_email')->index();
            $table->unsignedInteger('latency_ms')->nullable()->after('event_type');
            $table->string('environment')->nullable()->after('latency_ms');
        });
    }

    public function down(): void
    {
        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('actor_id');
            $table->dropColumn([
                'correlation_id',
                'payment_id',
                'customer_email',
                'event_type',
                'latency_ms',
                'environment',
            ]);
        });
    }
};
