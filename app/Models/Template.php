<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = ['name', 'slug', 'category', 'event_type', 'preview_image', 'config_json', 'is_premium', 'is_active'];
    protected function casts(): array { return ['config_json' => 'array', 'is_premium' => 'boolean', 'is_active' => 'boolean']; }
}
