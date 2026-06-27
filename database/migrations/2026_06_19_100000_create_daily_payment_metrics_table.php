<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_payment_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date')->unique();
            $table->unsignedInteger('capture_attempts')->default(0);
            $table->unsignedInteger('capture_successes')->default(0);
            $table->decimal('capture_success_rate', 6, 2)->default(0);
            $table->unsignedInteger('webhook_events')->default(0);
            $table->unsignedInteger('webhook_failures')->default(0);
            $table->decimal('webhook_failure_rate', 6, 2)->default(0);
            $table->unsignedInteger('reconciliation_recoveries')->default(0);
            $table->unsignedInteger('duplicate_webhooks')->default(0);
            $table->unsignedInteger('refund_count')->default(0);
            $table->decimal('refund_ratio', 6, 2)->default(0);
            $table->unsignedInteger('stock_conflicts')->default(0);
            $table->unsignedInteger('queue_backlog')->default(0);
            $table->unsignedInteger('failed_jobs')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_payment_metrics');
    }
};
