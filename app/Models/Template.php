<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'category', 'event_type', 'preview_image', 'config_json', 'is_premium', 'is_active', 'price'];

    protected function casts(): array
    {
        return ['config_json' => 'array', 'is_premium' => 'boolean', 'is_active' => 'boolean'];
    }

    public function getDiscountPercentAttribute(?int $value): int
    {
        return (int) data_get($this->config_json, 'discount_percent', $value ?? 50);
    }

    public function discountAmount(): int
    {
        return intdiv($this->price * $this->discount_percent, 100);
    }

    public function salePrice(): int
    {
        return $this->price - $this->discountAmount();
    }
}
