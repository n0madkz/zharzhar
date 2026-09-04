<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invitation extends Model
{
    protected $fillable = ['event_id', 'template_id', 'slug', 'content_json', 'settings_json', 'status', 'published_at', 'views_total'];
    protected function casts(): array { return ['content_json' => 'array', 'settings_json' => 'array', 'published_at' => 'datetime']; }
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function template(): BelongsTo { return $this->belongsTo(Template::class); }
    public function rsvps(): HasMany { return $this->hasMany(Rsvp::class); }
}
