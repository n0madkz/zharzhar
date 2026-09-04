<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = ['restaurant_id', 'restaurant_slot_id', 'visitor_name', 'event_type', 'phone', 'booking_date', 'booking_time', 'guest_count', 'price_per_guest', 'prepayment', 'status', 'note', 'color'];
    protected function casts(): array { return ['booking_date' => 'date', 'guest_count' => 'integer', 'price_per_guest' => 'decimal:2', 'prepayment' => 'decimal:2']; }

    public function getTotalAmountAttribute(): float
    {
        return (float) $this->price_per_guest * (int) $this->guest_count;
    }
    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
    public function slot(): BelongsTo { return $this->belongsTo(RestaurantSlot::class, 'restaurant_slot_id'); }
}
