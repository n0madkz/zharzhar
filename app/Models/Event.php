<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    protected $fillable = ['user_id', 'restaurant_id', 'event_type', 'title', 'event_date', 'event_time', 'venue_name', 'venue_address', 'language', 'status'];
    protected function casts(): array { return ['event_date' => 'date']; }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
    public function invitation(): HasOne { return $this->hasOne(Invitation::class); }
}
