<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    protected $fillable = ['restaurant_id', 'amount', 'kaspi_phone', 'status', 'paid_at', 'admin_note'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'paid_at' => 'datetime']; }
    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
}
