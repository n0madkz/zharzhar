<?php

namespace App\Support;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BrokerRoadRoute
{
    public function build(array $origin, array $venues): array
    {
        if ($venues === []) {
            throw new RuntimeException('Для маршрута нет доступных ресторанов.');
        }

        $locations = [[$origin['lng'], $origin['lat']]];
        foreach ($venues as $venue) {
            $locations[] = [$venue->longitude, $venue->latitude];
        }

        $cacheKey = 'broker:road-route:'.sha1(json_encode([
            config('services.osrm.url'), $locations, array_map(fn ($venue) => $venue->id, $venues),
        ]));

        return Cache::remember($cacheKey, 300, function () use ($locations, $venues) {
            return $this->calculate($locations, $venues);
        });
    }

    private function calculate(array $locations, array $venues): array
    {
        $coordinates = $this->coordinates($locations);
        $baseUrl = rtrim(config('services.osrm.url'), '/');
        $matrix = $this->request($baseUrl.'/table/v1/driving/'.$coordinates, ['annotations' => 'distance']);
        $distances = $matrix->json('distances');
        if (! $matrix->successful() || $matrix->json('code') !== 'Ok' || ! is_array($distances)) {
            throw new RuntimeException('Не удалось рассчитать расстояния по дорогам. Повторите попытку позже.');
        }

        $remaining = range(1, count($venues));
        $chosen = [];
        $legDistances = [];
        $current = 0;
        while ($remaining !== [] && count($chosen) < 5) {
            usort($remaining, fn ($a, $b) => ($distances[$current][$a] ?? INF) <=> ($distances[$current][$b] ?? INF));
            $next = array_shift($remaining);
            $meters = $distances[$current][$next] ?? null;
            if (! is_numeric($meters)) {
                break;
            }
            $chosen[] = $next;
            $legDistances[] = (float) $meters;
            $current = $next;
        }
        if ($chosen === []) {
            throw new RuntimeException('Для выбранных ресторанов не найден автомобильный маршрут.');
        }

        $routeCoordinates = $this->coordinates(array_map(fn ($index) => $locations[$index], [0, ...$chosen]));
        $route = $this->request($baseUrl.'/route/v1/driving/'.$routeCoordinates, [
            'overview' => 'full', 'geometries' => 'geojson', 'steps' => 'false',
        ]);
        $geometry = $route->json('routes.0.geometry.coordinates');
        if (! $route->successful() || $route->json('code') !== 'Ok' || ! is_array($geometry) || count($geometry) < 2) {
            throw new RuntimeException('Не удалось построить линию маршрута по дорогам. Повторите попытку позже.');
        }

        return [
            'stops' => array_map(fn ($position, $index) => [
                'id' => $venues[$index - 1]->id,
                'distanceKm' => round($legDistances[$position] / 1000, 1),
            ], array_keys($chosen), $chosen),
            'geometry' => $geometry,
        ];
    }

    private function coordinates(array $locations): string
    {
        return implode(';', array_map(fn ($point) => implode(',', array_map(
            fn ($number) => rtrim(rtrim(sprintf('%.6F', (float) $number), '0'), '.'), $point
        )), $locations));
    }

    private function request(string $url, array $query)
    {
        // The public FOSSGIS service permits at most one request per second.
        try {
            return Cache::lock('broker:osrm-request', 30)->block(10, function () use ($url, $query) {
                $last = (float) Cache::get('broker:osrm-last-request', 0);
                $wait = 1.05 - (microtime(true) - $last);
                if ($wait > 0) {
                    usleep((int) ($wait * 1000000));
                }
                Cache::put('broker:osrm-last-request', microtime(true), 60);

                try {
                    return Http::withHeaders(['User-Agent' => 'ZharZharBroker/1.0 (https://zharzhar.kz)'])
                        ->acceptJson()->timeout(20)->get($url, $query);
                } catch (ConnectionException $exception) {
                    throw new RuntimeException('Сервис дорожных маршрутов недоступен. Повторите попытку позже.', previous: $exception);
                }
            });
        } catch (LockTimeoutException $exception) {
            throw new RuntimeException('Сервис дорожных маршрутов занят. Повторите попытку позже.', previous: $exception);
        }
    }
}
