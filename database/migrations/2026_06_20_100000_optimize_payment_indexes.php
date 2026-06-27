<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['payment_status', 'created_at']);
            $table->index(['order_status', 'created_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });

        Schema::table('payment_events', function (Blueprint $table) {
            $table->index('event_id');
            $table->index(['provider', 'event_id', 'processed_at']);
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            $table->index('order_id');
            $table->index(['action', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['orders_payment_status_created_at_index']);
            $table->dropIndex(['orders_order_status_created_at_index']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['payments_status_created_at_index']);
        });

        Schema::table('payment_events', function (Blueprint $table) {
            $table->dropIndex(['payment_events_event_id_index']);
            $table->dropIndex(['payment_events_provider_event_id_processed_at_index']);
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropIndex(['payment_logs_order_id_index']);
            $table->dropIndex(['payment_logs_action_status_created_at_index']);
        });
    }
};