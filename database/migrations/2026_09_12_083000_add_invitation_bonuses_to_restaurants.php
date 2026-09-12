<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->decimal('bonus_percent', 5, 2)->default(0)->after('default_price_per_guest');
        });

        Schema::table('bonus_transactions', function (Blueprint $table) {
            $table->foreignId('invitation_order_id')
                ->nullable()
                ->after('invitation_id')
                ->unique()
                ->constrained('invitation_orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bonus_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invitation_order_id');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('bonus_percent');
        });
    }
};
