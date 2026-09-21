<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BrokerWebCollector
{
    /** @return array<int, array<string, mixed>> */
    public function collect(string $cityAlias): array
    {
        // The upstream parser's URL generator uses alphabetical sorting to
        // prevent duplicate result ordering while paging through 2GIS.
        $baseUrl = 'https://2gis.kz/'.$cityAlias.'/search/'.rawurlencode('Банкетные залы').'/rubricId/10803';
        $first = $this->state($this->request(), $baseUrl);
        $items = $this->items($first);
        // Public SSR exposes only the first five distinct pages. The bundled
        // city directory fills the remaining pages for Atyrau.
        $pages = min(5, max(1, $this->pageCount($first)));

        for ($page = 2; $page <= $pages; $page++) {
            usleep(200_000);
            $items = [...$items, ...$this->items($this->state($this->request(), $baseUrl.'/page/'.$page))];
        }

        $items = collect($items)
            ->filter(fn ($item) => is_array($item) && isset($item['id']))
            ->unique(fn ($item) => explode('_', (string) $item['id'])[0])
            ->values()->all();

        if ($items === []) {
            throw new RuntimeException('2GIS returned no banquet halls.');
        }

        return $items;
    }

    private function request(): PendingRequest
    {
        return Http::accept('text/html,application/xhtml+xml')
            ->withUserAgent('Mozilla/5.0 (compatible; ZharZharBroker/1.0; +https://zharzhar.kz)')
            ->connectTimeout(15)->timeout(60)
            ->retry([1000, 2500, 5000], throw: false);
    }

    /** @return array<string, mixed> */
    private function state(PendingRequest $request, string $url): array
    {
        $response = $request->get($url);
        if (! $response->successful()) {
            throw new RuntimeException("2GIS returned HTTP {$response->status()}.");
        }

        $marker = "var initialState = JSON.parse('";
        $html = $response->body();
        $start = strpos($html, $marker);
        if ($start === false) {
            throw new RuntimeException('2GIS page does not contain initialState.');
        }
        $start += strlen($marker);
        $escaped = false;
        $length = strlen($html);
        for ($end = $start; $end < $length; $end++) {
            $character = $html[$end];
            if ($character === "'" && ! $escaped) {
                break;
            }
            if ($character === '\\' && ! $escaped) {
                $escaped = true;
            } else {
                $escaped = false;
            }
        }
        if ($end >= $length) {
            throw new RuntimeException('2GIS initialState is incomplete.');
        }

        $encoded = substr($html, $start, $end - $start);
        $json = $this->decodeJavascriptString($encoded);

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    private function decodeJavascriptString(string $value): string
    {
        $decoded = '';
        $length = strlen($value);
        $escapes = ['n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'f' => "\f", 'v' => "\v", '0' => "\0"];

        for ($index = 0; $index < $length; $index++) {
            if ($value[$index] !== '\\' || $index + 1 >= $length) {
                $decoded .= $value[$index];

                continue;
            }

            $next = $value[++$index];
            if ($next === 'u' && preg_match('/\A[0-9a-f]{4}/i', substr($value, $index + 1), $match)) {
                $escape = '\\u'.$match[0];
                $index += 4;
                if (preg_match('/\A\\\\u[0-9a-f]{4}/i', substr($value, $index + 1), $surrogate)) {
                    $escape .= $surrogate[0];
                    $index += 6;
                }
                $decoded .= json_decode('"'.$escape.'"', true, 512, JSON_THROW_ON_ERROR);
            } elseif ($next === 'x' && preg_match('/\A[0-9a-f]{2}/i', substr($value, $index + 1), $match)) {
                $decoded .= chr(hexdec($match[0]));
                $index += 2;
            } elseif ($next === "\n") {
                // JavaScript line continuation.
            } elseif ($next === "\r") {
                if (($value[$index + 1] ?? null) === "\n") {
                    $index++;
                }
            } else {
                $decoded .= $escapes[$next] ?? $next;
            }
        }

        return $decoded;
    }

    /** @param array<string, mixed> $state */
    private function pageCount(array $state): int
    {
        $search = collect(data_get($state, 'data.search.profile', []))->first();

        return (int) data_get($search, 'data.pages', 1);
    }

    /** @param array<string, mixed> $state
     * @return array<int, array<string, mixed>>
     */
    private function items(array $state): array
    {
        return collect(data_get($state, 'data.entity.profile', []))
            ->pluck('data')->filter(fn ($item) => is_array($item))->values()->all();
    }
}
