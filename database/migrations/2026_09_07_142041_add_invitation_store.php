<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(7990);
        });
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('type');
            $table->unsignedInteger('value');
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('uses')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('invitation_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained();
            $table->foreignId('promo_code_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invitation_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('token', 64)->unique();
            $table->string('responses_token', 64)->unique();
            $table->uuid('request_key')->unique();
            $table->string('customer_name');
            $table->string('customer_phone', 30);
            $table->json('details');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('discount')->default(0);
            $table->unsignedInteger('total');
            $table->string('promo_code', 40)->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('payment_reference')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitation_orders');
        Schema::dropIfExists('promo_codes');
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
