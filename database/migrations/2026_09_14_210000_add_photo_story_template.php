<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $content = [
            'title' => 'Махаббат хикаясы',
            'event_label' => 'ҮЙЛЕНУ ТОЙЫ',
            'intro_title' => 'ЕКІ ЖҮРЕКТІҢ БІР ХИКАЯСЫ',
            'invitation_text' => 'Өміріміздің ең әдемі тарауын сіздермен бірге бастап, қуанышымыздың қадірлі қонағы болуға шақырамыз.',
            'event_date' => now()->addMonths(2)->format('Y-m-d'),
            'event_time' => '18:00',
            'date_title' => 'Біздің ерекше күніміз',
            'gallery_title' => 'Біздің ерекше сәттеріміз',
            'venue_name' => 'Ақ Отау мейрамханасы',
            'venue_address' => 'Алматы қаласы, Абай даңғылы, 50',
            'hosts_title' => 'Той иелері',
            'hosts_name' => 'Қуаныш иелері',
            'rsvp_title' => 'Сізді күтеміз!',
            'rsvp_hint' => 'Тойға қатысуыңызды растауыңызды сұраймыз.',
            'closing_text' => 'Махаббат хикаямызға ортақ болыңыз!',
        ];
        $config = [
            'theme' => 'photo-story',
            'sample_names' => 'Махаббат хикаясы',
            'supports_photos' => true,
            'sample_photos' => [
                '/invitation-assets/wedding-hands.webp',
                '/invitation-assets/botanical-wedding.webp',
                '/invitation-assets/modern-evening-wedding.webp',
            ],
            'content_kk' => $content,
        ];

        DB::table('templates')->updateOrInsert(
            ['slug' => 'mahabbat-hikayasy'],
            [
                'name' => 'Махаббат хикаясы',
                'category' => 'wedding',
                'event_type' => 'wedding',
                'preview_image' => '/invitation-assets/wedding-hands.webp',
                'config_json' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'is_premium' => true,
                'is_active' => true,
                'price' => 12990,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('templates')->where('slug', 'mahabbat-hikayasy')->delete();
    }
};
