<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class InvitationCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['Нежный шалфей', 'sage-wedding', 'wedding', 'sage', 7990],
            ['Розовый сад', 'rose-wedding', 'wedding', 'rose', 9990],
            ['Вечернее золото', 'gold-wedding', 'wedding', 'midnight', 12990],
            ['Тёплая классика', 'classic-anniversary', 'anniversary', 'sand', 7990],
            ['Золотой юбилей', 'gold-anniversary', 'anniversary', 'midnight', 9990],
            ['Счастливый день', 'happy-birthday', 'birthday', 'rose', 7990],
        ] as [$name,$slug,$event,$theme,$price]) {
            Template::firstOrCreate(['slug' => $slug], ['name' => $name, 'category' => $event, 'event_type' => $event, 'price' => $price, 'config_json' => ['theme' => $theme], 'is_active' => true]);
        }
    }
}
