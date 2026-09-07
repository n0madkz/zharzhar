<?php

namespace Database\Factories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvitationOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'template_id' => Template::factory(), 'token' => Str::random(64), 'responses_token' => Str::random(64), 'request_key' => (string) Str::uuid(),
            'customer_name' => 'Айгүл', 'customer_phone' => '+7 700 123 45 67', 'subtotal' => 7990, 'discount' => 0, 'total' => 7990, 'status' => 'pending',
            'details' => ['names' => 'Алихан & Аружан', 'hosts' => 'Ерлан – Айгүл', 'event_type' => 'wedding', 'event_date' => now()->addMonth()->format('Y-m-d'), 'event_time' => '18:00', 'venue_name' => 'Салтанат', 'venue_address' => 'Алматы, Абая 1', 'language' => 'kk', 'theme' => 'sage', 'template_name' => 'Нежный шалфей', 'music_url' => null, 'music_name' => null],
        ];
    }
}
