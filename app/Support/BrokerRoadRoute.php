<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;

class BrokerRoadRoute
{
    public function build(array $origin, array $venues): array
    {
        $key = config('services.openrouteservice.key');
        if (! is_string($key) || $key === '') {
            throw new RuntimeException('Маршрутизация по дорогам не настроена. Добавьте OPENROUTESERVICE_API_KEY на сервере.');
        }

        $locations = [[$origin['lng'], $origin['lat']]];
        foreach ($venues as $venue) {
            $locations[] = [$venue->longitude, $venue->latitude];
        }

        $client = Http::withHeaders(['Authorization' => $key])->acceptJson()->timeout(20);
        try {
            $matrix = $client->post('https://api.heigit.org/openrouteservice/v2/matrix/driving-car', [
                'locations' => $locations,
                'metrics' => ['distance'],
            ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Сервис дорожных маршрутов недоступен. Повторите попытку позже.', previous: $exception);
        }
        if (! $matrix->successful() || ! is_array($matrix->json('distances'))) {
            throw new RuntimeException('Не удалось рассчитать расстояния по дорогам. Повторите попытку позже.');
        }

        $distances = $matrix->json('distances');
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

        try {
            $route = $client->post('https://api.heigit.org/openrouteservice/v2/directions/driving-car/geojson', [
                'coordinates' => array_map(fn ($index) => $locations[$index], [0, ...$chosen]),
                'instructions' => false,
            ]);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Сервис дорожных маршрутов недоступен. Повторите попытку позже.', previous: $exception);
        }
        $geometry = $route->json('features.0.geometry.coordinates');
        if (! $route->successful() || ! is_array($geometry) || count($geometry) < 2) {
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
}
