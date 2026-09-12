<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusTransaction extends Model
{
    protected $fillable = ['restaurant_id', 'booking_id', 'invitation_id', 'invitation_order_id', 'amount', 'type', 'status', 'note'];
    protected function casts(): array { return ['amount' => 'decimal:2']; }
    public function restaurant(): BelongsTo { return $this->belongsTo(Restaurant::class); }
    public function order(): BelongsTo { return $this->belongsTo(InvitationOrder::class, 'invitation_order_id'); }
}
