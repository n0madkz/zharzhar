<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('music', function (Blueprint $table) {
            $table->json('categories')->nullable()->after('category');
        });

        $available = ['wedding', 'anniversary', 'birthday'];
        DB::table('music')->select(['id', 'category'])->orderBy('id')->each(function ($track) use ($available): void {
            $category = mb_strtolower((string) $track->category);
            $categories = [];
            if (str_contains($category, 'wedding') || str_contains($category, 'свад') || str_contains($category, 'үйлен')) {
                $categories[] = 'wedding';
            }
            if (str_contains($category, 'anniversary') || str_contains($category, 'юбил') || str_contains($category, 'мерейтой')) {
                $categories[] = 'anniversary';
            }
            if (str_contains($category, 'birthday') || str_contains($category, 'рожд') || str_contains($category, 'туған')) {
                $categories[] = 'birthday';
            }
            DB::table('music')->where('id', $track->id)->update([
                'categories' => json_encode($categories ?: $available, JSON_UNESCAPED_UNICODE),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('music', function (Blueprint $table) {
            $table->dropColumn('categories');
        });
    }
};
