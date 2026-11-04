<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffRequest extends Model
{
    protected $fillable = ['restaurant_id', 'event_date', 'starts_at', 'ends_at', 'workers_needed', 'pay_per_worker', 'description', 'status'];

    protected function casts(): array
    {
        return ['event_date' => 'date'];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(StaffApplication::class);
    }
}
