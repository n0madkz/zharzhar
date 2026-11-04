<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->date('event_date');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->unsignedSmallInteger('workers_needed');
            $table->unsignedInteger('pay_per_worker');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamps();
            $table->index(['status', 'event_date']);
        });

        Schema::create('staff_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_request_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 30);
            $table->text('message')->nullable();
            $table->string('status', 20)->default('new');
            $table->timestamps();
            $table->unique(['staff_request_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_applications');
        Schema::dropIfExists('staff_requests');
    }
};
