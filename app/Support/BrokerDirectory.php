<?php

namespace App\Support;

use App\Models\BrokerVenue;
use App\Models\Restaurant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BrokerDirectory
{
    public static function normalize(?string $value): string
    {
        return preg_replace('/[^\pL\pN]/u', '', mb_strtolower($value ?? ''));
    }

    public static function phone(?string $value): string
    {
        $digits = preg_replace('/\D/', '', $value ?? '');

        return strlen($digits) === 11 && $digits[0] === '8' ? '7'.substr($digits, 1) : $digits;
    }

    /** Reject businesses that 2GIS also returns for the banquet-hall rubric. */
    public static function isClearlyNotBanquetHall(array $item): bool
    {
        $extension = mb_strtolower((string) data_get($item, 'name_ex.extension', ''));

        if (str_contains($extension, 'банкет') || str_contains($extension, 'зал торжеств')) {
            return false;
        }

        return preg_match('/кафе|кофейн|караоке|(?:^|[-\s])бар(?:$|[-\s])|паб|столов|пицц|кондитер|диско[\s-]*клуб|быстрое питание/u', $extension) === 1;
    }

    /** Only unambiguous branch matches count as an existing partner. */
    public function match(BrokerVenue $venue, Collection $restaurants): ?Restaurant
    {
        if ($venue->restaurant_id) {
            return $restaurants->firstWhere('id', $venue->restaurant_id);
        }
        $matches = $restaurants->filter(function ($restaurant) use ($venue) {
            if (preg_match('~/(?:firm|geo)/(\d+)~', $restaurant->two_gis_url ?? '', $id)
                && $id[1] === $venue->source_id) {
                return true;
            }

            return self::normalize($restaurant->city) === self::normalize($venue->city)
                && self::normalize($restaurant->name) === self::normalize($venue->name)
                && ((self::normalize($venue->address) !== ''
                    && self::normalize($restaurant->address) === self::normalize($venue->address))
                    || (strlen(self::phone($venue->phone)) >= 10
                    && self::phone($restaurant->phone) === self::phone($venue->phone)));
        });

        return $matches->count() === 1 ? $matches->first() : null;
    }

    /** Accept the actual JSON array written by interlark/parser-2gis JSONWriter. */
    public function import(string $json, string $fallbackCity): int
    {
        try {
            $items = json_decode(ltrim($json, "\xEF\xBB\xBF"), true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ValidationException::withMessages(['file' => 'Не удалось прочитать JSON парсера.']);
        }
        if (! is_array($items) || ! array_is_list($items) || count($items) > 10000) {
            throw ValidationException::withMessages(['file' => 'Нужен JSON-список, максимум 10 000 залов.']);
        }
        $rows = [];
        $rejectedIds = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $id = explode('_', (string) ($item['id'] ?? ''))[0];
            $name = data_get($item, 'name_ex.primary') ?: ($item['name'] ?? null);
            if (! preg_match('/^\d{1,100}$/D', $id) || ! is_string($name) || trim($name) === '') {
                continue;
            }
            if (self::isClearlyNotBanquetHall($item)) {
                $rejectedIds[] = $id;

                continue;
            }
            $divisions = collect($item['adm_div'] ?? []);
            $city = $divisions->firstWhere('type', 'city')['name'] ?? $fallbackCity;
            $district = $divisions->firstWhere('type', 'district')['name'] ?? $divisions->firstWhere('type', 'living_area')['name'] ?? null;
            $contacts = collect($item['contact_groups'] ?? [])->flatMap(fn ($group) => $group['contacts'] ?? []);
            $lat = data_get($item, 'point.lat');
            $lng = data_get($item, 'point.lon');
            $validPoint = is_numeric($lat) && is_numeric($lng) && abs((float) $lat) <= 90 && abs((float) $lng) <= 180;
            $rows[] = [
                'source_id' => $id, 'name' => mb_substr(trim($name), 0, 255),
                'city' => mb_substr((string) $city, 0, 100), 'district' => $district ? mb_substr($district, 0, 255) : null,
                'address' => mb_substr((string) ($item['address_name'] ?? ''), 0, 255),
                'phone' => mb_substr((string) ($contacts->firstWhere('type', 'phone')['value'] ?? ''), 0, 50),
                'email' => mb_substr((string) ($contacts->firstWhere('type', 'email')['value'] ?? ''), 0, 255),
                'latitude' => $validPoint ? $lat : null, 'longitude' => $validPoint ? $lng : null,
                'two_gis_url' => 'https://2gis.kz/firm/'.$id,
            ];
        }
        if (! $rows && ! $rejectedIds) {
            throw ValidationException::withMessages(['file' => 'В файле нет залов с идентификатором 2GIS и названием.']);
        }

        return DB::transaction(function () use ($rows, $rejectedIds) {
            if ($rejectedIds) {
                BrokerVenue::query()
                    ->whereIn('source_id', array_unique($rejectedIds))
                    ->whereNull('restaurant_id')
                    ->where(function ($query) {
                        $query->whereNull('deal_status')->orWhere('deal_status', '!=', 'closed');
                    })
                    ->delete();
            }
            foreach ($rows as $row) {
                BrokerVenue::updateOrCreate(['source_id' => $row['source_id']], $row);
            }

            return count($rows);
        });
    }
}
