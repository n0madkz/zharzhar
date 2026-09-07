<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PromoCodeFactory extends Factory
{
    public function definition(): array
    {
        return ['code' => strtoupper(Str::random(10)), 'type' => 'percent', 'value' => 10, 'is_active' => true, 'max_uses' => 10];
    }
}
