<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TemplateFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => 'Нежный шалфей', 'slug' => fake()->unique()->slug(), 'category' => 'wedding', 'event_type' => 'wedding', 'price' => 7990, 'config_json' => ['theme' => 'sage'], 'is_active' => true];
    }
}
