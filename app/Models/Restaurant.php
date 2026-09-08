<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    protected $fillable = ['name', 'city', 'address', 'two_gis_url', 'phone', 'max_seats', 'default_price_per_guest', 'status', 'partner_user_id'];

    protected function casts(): array
    {
        return ['default_price_per_guest' => 'decimal:2'];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_user_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(RestaurantSlot::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(BonusTransaction::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(PayoutRequest::class);
    }
}
