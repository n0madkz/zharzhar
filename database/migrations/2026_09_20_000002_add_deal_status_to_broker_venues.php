<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('broker_venues', fn (Blueprint $table) => $table->string('deal_status', 20)->default('open')->index()->after('completed_at'));
        DB::table('broker_venues')->whereNotNull('completed_at')->update(['deal_status' => 'closed']);
    }

    public function down(): void
    {
        Schema::table('broker_venues', fn (Blueprint $table) => $table->dropColumn('deal_status'));
    }
};
