<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    protected $table = 'music';
    protected $fillable = ['name', 'category', 'categories', 'audio_url', 'is_active'];

    protected function casts(): array
    {
        return ['categories' => 'array', 'is_active' => 'boolean'];
    }

    public function categoryLabel(): string
    {
        $labels = config('store.music_categories');
        $selected = collect($this->categories ?? [])->map(fn (string $category) => $labels[$category] ?? $category)->filter();

        return $selected->isNotEmpty() ? $selected->join(', ') : $this->category;
    }

    public function supportsCategory(string $category): bool
    {
        return empty($this->categories) || in_array($category, $this->categories, true);
    }
}
