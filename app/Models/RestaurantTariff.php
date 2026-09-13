<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantTariff extends Model
{
    protected $fillable = ['restaurant_service_id', 'name', 'description', 'price_per_guest', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['price_per_guest' => 'decimal:2', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(RestaurantService::class, 'restaurant_service_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
