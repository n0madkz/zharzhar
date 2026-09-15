<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Invitation extends Model
{
    protected $fillable = ['event_id', 'template_id', 'slug', 'content_json', 'settings_json', 'status', 'published_at', 'views_total'];
    protected function casts(): array { return ['content_json' => 'array', 'settings_json' => 'array', 'published_at' => 'datetime']; }
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function template(): BelongsTo { return $this->belongsTo(Template::class); }
    public function rsvps(): HasMany { return $this->hasMany(Rsvp::class); }

    public function moveToArchive(): void
    {
        DB::transaction(function (): void {
            $this->update(['status' => 'archived']);
            $this->event()->update(['status' => 'archived']);
        });
    }

    public static function archiveExpired(?CarbonInterface $today = null): int
    {
        $today = ($today ? Carbon::instance($today) : today())->copy()->startOfDay();
        $candidateCutoff = $today->copy()->subDays(28)->toDateString();
        $archivedCount = 0;

        static::query()
            ->with('event:id,event_date,status')
            ->whereIn('status', ['published', 'active'])
            ->whereHas('event', fn ($query) => $query->whereDate('event_date', '<=', $candidateCutoff))
            ->eachById(function (Invitation $invitation) use ($today, &$archivedCount): void {
                if (! $invitation->event?->event_date->copy()->startOfDay()->addMonthNoOverflow()->lte($today)) {
                    return;
                }

                $invitation->moveToArchive();
                $archivedCount++;
            }, 100);

        return $archivedCount;
    }
}
