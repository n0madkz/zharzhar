<?php

namespace Tests\Unit;

use App\Support\BrokerWebCollector;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BrokerWebCollectorTest extends TestCase
{
    public function test_it_reads_every_server_rendered_search_page_without_chromium(): void
    {
        Http::fake(function ($request) {
            $page = str_ends_with($request->url(), '/page/2') ? 2 : 1;
            $item = [
                'id' => (string) (70000000000000000 + $page),
                'name_ex' => ['primary' => "Зал {$page}"],
                'address_name' => "Адрес {$page}",
                'point' => ['lat' => 47.1 + $page, 'lon' => 51.9 + $page],
            ];
            $state = [
                'data' => [
                    'entity' => ['profile' => [$item['id'] => ['data' => $item]]],
                    'search' => ['profile' => ['search-id' => ['data' => ['pages' => 2]]]],
                ],
            ];
            $json = json_encode($state, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

            return Http::response("<script>var initialState = JSON.parse('{$json}');</script>");
        });

        $items = app(BrokerWebCollector::class)->collect('atyrau');

        $this->assertCount(2, $items);
        $this->assertSame('Зал 1', $items[0]['name_ex']['primary']);
        $this->assertSame('Зал 2', $items[1]['name_ex']['primary']);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/rubricId/10803'));
        Http::assertSentCount(2);
    }
}
