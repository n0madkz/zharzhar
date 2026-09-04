<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table): void {
            $table->decimal('default_price_per_guest', 12, 2)->default(0)->after('max_seats');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->decimal('price_per_guest', 12, 2)->default(0)->after('guest_count');
            $table->decimal('prepayment', 12, 2)->default(0)->after('price_per_guest');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['price_per_guest', 'prepayment']);
        });
        Schema::table('restaurants', function (Blueprint $table): void {
            $table->dropColumn('default_price_per_guest');
        });
    }
};
