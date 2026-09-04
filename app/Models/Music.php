<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    protected $table = 'music';
    protected $fillable = ['name', 'category', 'audio_url', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
}
