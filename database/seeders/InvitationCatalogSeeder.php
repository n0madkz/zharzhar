<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class InvitationCatalogSeeder extends Seeder
{
    public function run(): void
    {
        Template::whereIn('slug', [
            'sage-wedding', 'rose-wedding', 'gold-wedding',
            'classic-anniversary', 'gold-anniversary', 'happy-birthday',
        ])->update(['is_active' => false]);

        foreach ([
            ['Ақ інжу', 'ak-inju', 'wedding', 'pearl', 7990, '/invitation-assets/pearl-ethno.webp', 'Алихан & Аружан'],
            ['Royal кеш', 'royal-kesh', 'wedding', 'royal', 8990, '/invitation-assets/royal-ethno.webp', 'Әли & Аяулым'],
            ['Нәзік ботаника', 'nazik-botanika', 'wedding', 'botanical', 9990, '/invitation-assets/botanical-ethno.webp', 'Нұрлан & Жансая'],
            ['Ақ жібек', 'ak-zhibek', 'wedding', 'silk', 10990, '/invitation-assets/silk-ethno.webp', 'Мирас & Айдана'],
            ['Алтын Nomad', 'altyn-nomad', 'wedding', 'nomad', 11990, '/invitation-assets/nomad-horse.webp', 'Ерасыл & Томирис'],
            ['Мерейлі шеңбер', 'mereyli-shenber', 'anniversary', 'jubilee', 9990, '/invitation-assets/jubilee-ethno.webp', 'Мерейлі 60 жас'],
            ['Ару қыз ұзату', 'aru-qyz-uzatu', 'qyz_uzatu', 'qyz', 10990, '/invitation-assets/qyz-ethno.webp', 'Аружан'],
        ] as [$name, $slug, $event, $theme, $price, $image, $sampleNames]) {
            Template::updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'category' => $event,
                'event_type' => $event,
                'price' => $price,
                'preview_image' => $image,
                'config_json' => ['theme' => $theme, 'sample_names' => $sampleNames],
                'is_active' => true,
            ]);
        }
    }
}
