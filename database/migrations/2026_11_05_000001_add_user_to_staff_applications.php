<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_applications', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('staff_request_id')->constrained()->nullOnDelete();
            $table->unique(['staff_request_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('staff_applications', function (Blueprint $table) {
            $table->dropUnique(['staff_request_id', 'user_id']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
