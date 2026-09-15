<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->index('created_at', 'bookings_created_at_index');
        });

        Schema::table('payout_requests', function (Blueprint $table): void {
            $table->index(['restaurant_id', 'status', 'created_at'], 'payout_restaurant_status_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('payout_requests', function (Blueprint $table): void {
            $table->dropIndex('payout_restaurant_status_created_index');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex('bookings_created_at_index');
        });
    }
};
