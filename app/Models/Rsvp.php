<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rsvp extends Model
{
    protected $table = 'rsvps';
    protected $fillable = ['invitation_id', 'guest_name', 'attendance_status', 'guest_count', 'companions', 'message'];
    public function invitation(): BelongsTo { return $this->belongsTo(Invitation::class); }
}
