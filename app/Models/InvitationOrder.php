<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InvitationOrder extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['token', 'responses_token', 'request_key'];

    protected function casts(): array
    {
        return ['details' => 'array', 'submitted_at' => 'datetime', 'paid_at' => 'datetime'];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function bonusTransaction(): HasOne
    {
        return $this->hasOne(BonusTransaction::class);
    }

    public function statusLabel(): string
    {
        if (app()->isLocale('kk')) {
            return match ($this->status) {
                'review' => 'Төлем тексерілуде', 'paid' => 'Шақыру дайын',
                'rejected' => 'Тапсырыс қабылданбады', default => 'Төлем күтілуде',
            };
        }

        return match ($this->status) {
            'review' => 'Проверяем оплату', 'paid' => 'Приглашение готово',
            'rejected' => 'Заказ отклонён', default => 'Ожидает оплаты',
        };
    }

    public function publicUrl(string $path): string
    {
        return rtrim(config('store.public_url'), '/').'/'.ltrim($path, '/');
    }
}
