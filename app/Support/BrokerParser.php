<?php

namespace App\Support;

use App\Models\BrokerVenue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class BrokerParser
{
    public function __construct(
        private readonly BrokerDirectory $directory,
        private readonly BrokerWebCollector $webCollector,
    ) {}

    public function collect(string $city): int
    {
        $alias = config("broker.cities.{$city}");
        if (! $alias) {
            throw ValidationException::withMessages(['city' => 'Неизвестный город 2GIS.']);
        }

        $lock = Cache::lock('broker-parser:'.sha1($city), 1800);
        if (! $lock->get()) {
            throw ValidationException::withMessages([
                'city' => 'Загрузка этого города уже выполняется. Дождитесь завершения и обновите страницу.',
            ]);
        }

        try {
            $this->importBundledDirectory($city);
            $browserException = null;
            if ($this->browserIsInstalled()) {
                try {
                    $this->collectWithBrowser($city, $alias);

                    return $this->venueCount($city);
                } catch (\Throwable $exception) {
                    $browserException = $exception;
                    report($exception);
                }
            }

            try {
                $items = $this->webCollector->collect($alias);

                $this->directory->import(
                    json_encode($items, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                    $city,
                );

                return $this->venueCount($city);
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (\Throwable $webException) {
                report($webException);
                $message = 'Не удалось получить данные из публичной выдачи 2GIS: '.$webException->getMessage();
                if ($browserException) {
                    $message .= ' parser-2gis: '.$browserException->getMessage();
                }

                throw ValidationException::withMessages(['city' => $message]);
            }
        } finally {
            $lock->release();
        }
    }

    private function importBundledDirectory(string $city): void
    {
        if ($city !== 'Атырау') {
            return;
        }

        $path = database_path('data/broker-atyrau.json');
        if (is_file($path)) {
            $this->directory->import(file_get_contents($path), $city);
        }
    }

    private function venueCount(string $city): int
    {
        return BrokerVenue::query()->where('city', $city)->count();
    }

    private function browserIsInstalled(): bool
    {
        $python = config('broker.parser_python');
        $chrome = trim((string) @file_get_contents((string) config('broker.chrome_path_file')));

        return is_string($python) && is_file($python) && is_file($chrome);
    }

    private function collectWithBrowser(string $city, string $alias): int
    {
        $python = config('broker.parser_python');
        $chrome = trim((string) @file_get_contents((string) config('broker.chrome_path_file')));
        $path = tempnam(storage_path('app'), 'broker-');
        try {
            $process = new Process([
                $python, base_path('tools/broker/collect.py'),
                '-i', 'https://2gis.kz/'.$alias.'/search/'.rawurlencode('Банкетные залы').'/filters/sort=name',
                '-o', $path, '-f', 'json', '--chrome.headless', 'yes',
                '--chrome.binary_path', $chrome,
                '--chrome.start-maximized', 'yes', '--chrome.silent-browser', 'yes', '--chrome.disable-images', 'yes',
                '--parser.max-records', '1000', '--parser.delay_between_clicks', '250',
                '--writer.encoding', 'utf8', '--writer.verbose', 'no',
            ], base_path(), ['DEBUG' => ''], null, 1740);
            $process->mustRun();

            return $this->directory->import(file_get_contents($path), $city);
        } finally {
            if ($path && is_file($path)) {
                unlink($path);
            }
        }
    }
}
