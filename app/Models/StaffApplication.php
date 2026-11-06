<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffApplication extends Model
{
    protected $fillable = ['staff_request_id', 'user_id', 'name', 'phone', 'message', 'status'];

    public function staffRequest(): BelongsTo
    {
        return $this->belongsTo(StaffRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
