<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('restaurant_slots')->where('slot_key', 'morning')->update(['color' => '#6fa982']);
        DB::table('restaurant_slots')->where('slot_key', 'day')->update(['color' => '#d8b36a']);
        DB::table('restaurant_slots')->where('slot_key', 'evening')->update(['color' => '#c46b58']);
    }

    public function down(): void
    {
        DB::table('restaurant_slots')->where('slot_key', 'morning')->update(['color' => '#d8b36a']);
        DB::table('restaurant_slots')->where('slot_key', 'day')->update(['color' => '#7ba184']);
    }
};
