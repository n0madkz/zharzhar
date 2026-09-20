<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->string('broker_city', 100)->nullable());
        Schema::create('broker_venues', function (Blueprint $table) {
            $table->id();
            $table->string('source_id', 100)->unique();
            $table->string('name');
            $table->string('city', 100)->index();
            $table->string('district')->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('two_gis_url', 500);
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_venues');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('broker_city'));
    }
};
