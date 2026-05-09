<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->index(['status', 'created_at'], 'orders_status_created_at_index');
        });

        Schema::table('order_details', function (Blueprint $table): void {
            $table->index('status', 'order_details_status_index');
        });

        Schema::table('reservations', function (Blueprint $table): void {
            $table->index('reservation_time', 'reservations_reservation_time_index');
        });

        Schema::table('inventory_logs', function (Blueprint $table): void {
            $table->index('created_at', 'inventory_logs_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_status_created_at_index');
        });

        Schema::table('order_details', function (Blueprint $table): void {
            $table->dropIndex('order_details_status_index');
        });

        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropIndex('reservations_reservation_time_index');
        });

        Schema::table('inventory_logs', function (Blueprint $table): void {
            $table->dropIndex('inventory_logs_created_at_index');
        });
    }
};