<?php

namespace Tests\Unit;

use App\Support\BrokerRoadRoute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class BrokerRoadRouteTest extends TestCase
{
    public function test_orders_stops_by_driving_distance_and_returns_road_geometry(): void
    {
        config()->set('services.osrm.url', 'https://routing.example.test/routed-car');
        Cache::flush();
        Http::fake([
            '*/table/v1/driving/*' => Http::response(['code' => 'Ok', 'distances' => [
                [0, 900, 500, 1200],
                [900, 0, 300, 600],
                [500, 400, 0, 1000],
                [1200, 600, 300, 0],
            ]]),
            '*/route/v1/driving/*' => Http::response(['code' => 'Ok', 'routes' => [[
                'geometry' => ['coordinates' => [[76.9, 43.2], [76.91, 43.21], [76.92, 43.22]]],
            ]]]),
        ]);
        $venues = [
            (object) ['id' => 1, 'longitude' => 76.91, 'latitude' => 43.21],
            (object) ['id' => 2, 'longitude' => 76.92, 'latitude' => 43.22],
            (object) ['id' => 3, 'longitude' => 76.93, 'latitude' => 43.23],
        ];

        $route = (new BrokerRoadRoute)->build(['lat' => 43.2, 'lng' => 76.9], $venues);

        $this->assertSame([2, 1, 3], array_column($route['stops'], 'id'));
        $this->assertSame([0.5, 0.4, 0.6], array_column($route['stops'], 'distanceKm'));
        $this->assertCount(3, $route['geometry']);
        Http::assertSentCount(2);

        (new BrokerRoadRoute)->build(['lat' => 43.2, 'lng' => 76.9], $venues);
        Http::assertSentCount(2);
    }

    public function test_requires_a_restaurant(): void
    {
        $this->expectException(RuntimeException::class);
        (new BrokerRoadRoute)->build(['lat' => 43.2, 'lng' => 76.9], []);
    }
}
