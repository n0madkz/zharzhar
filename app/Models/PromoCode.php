<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'type', 'value', 'max_uses', 'is_active', 'expires_at'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'expires_at' => 'datetime'];
    }

    public function discountFor(int $price): int
    {
        if (! $this->is_active || ($this->expires_at && $this->expires_at->isPast()) || ($this->max_uses !== null && $this->uses >= $this->max_uses)) {
            throw ValidationException::withMessages(['promo_code' => 'Промокод не действует или его лимит исчерпан.']);
        }

        return min($price, $this->type === 'percent' ? intdiv($price * $this->value, 100) : $this->value);
    }
}
