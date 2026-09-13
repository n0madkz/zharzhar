<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('restaurant_services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('restaurant_tariffs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_service_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->decimal('price_per_guest', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->foreignId('restaurant_tariff_id')->nullable()->after('restaurant_slot_id')->constrained()->nullOnDelete();
        });

        $now = now();
        DB::table('restaurants')->orderBy('id')->each(function ($restaurant) use ($now): void {
            $serviceId = DB::table('restaurant_services')->insertGetId([
                'restaurant_id' => $restaurant->id,
                'name' => 'Банкет',
                'description' => 'Основная услуга ресторана',
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $tariffId = DB::table('restaurant_tariffs')->insertGetId([
                'restaurant_service_id' => $serviceId,
                'name' => 'Стандарт',
                'description' => 'Базовый тариф',
                'price_per_guest' => $restaurant->default_price_per_guest ?? 0,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('bookings')->where('restaurant_id', $restaurant->id)->update(['restaurant_tariff_id' => $tariffId]);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('restaurant_tariff_id');
        });
        Schema::dropIfExists('restaurant_tariffs');
        Schema::dropIfExists('restaurant_services');
    }
};
