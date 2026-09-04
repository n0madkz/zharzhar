<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantSlot extends Model
{
    protected $fillable = ['restaurant_id', 'slot_key', 'label', 'start_time', 'end_time', 'color'];
    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
    public function bookings(): HasMany { return $this->hasMany(Booking::class); }
}
