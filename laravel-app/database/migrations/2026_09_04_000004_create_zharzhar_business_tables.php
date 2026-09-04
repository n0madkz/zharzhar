<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('restaurant_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('slot_key');
            $table->string('label');
            $table->string('start_time');
            $table->string('end_time');
            $table->string('color', 20);
            $table->timestamps();
            $table->unique(['restaurant_id', 'slot_key']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('restaurant_slot_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_name');
            $table->string('phone')->nullable();
            $table->date('booking_date');
            $table->string('booking_time')->nullable();
            $table->unsignedInteger('guest_count')->default(1);
            $table->string('status')->default('pending');
            $table->text('note')->nullable();
            $table->string('color', 20)->default('#d8b36a');
            $table->timestamps();
            $table->index(['restaurant_id', 'booking_date']);
        });

        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');
            $table->string('event_type')->nullable();
            $table->string('preview_image')->nullable();
            $table->json('config_json')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('music', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('audio_url');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type');
            $table->string('title');
            $table->date('event_date');
            $table->string('event_time')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('venue_address')->nullable();
            $table->string('language', 8)->default('ru');
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained();
            $table->string('slug')->unique();
            $table->json('content_json')->nullable();
            $table->json('settings_json')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('views_total')->default(0);
            $table->timestamps();
        });

        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invitation_id')->constrained()->cascadeOnDelete();
            $table->string('guest_name');
            $table->string('attendance_status');
            $table->unsignedInteger('guest_count')->default(1);
            $table->text('companions')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::create('bonus_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invitation_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('type')->default('accrual');
            $table->string('status')->default('available');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('kaspi_phone', 30);
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
        Schema::dropIfExists('bonus_transactions');
        Schema::dropIfExists('rsvps');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('events');
        Schema::dropIfExists('music');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('restaurant_slots');
        Schema::dropIfExists('restaurants');
    }
};
