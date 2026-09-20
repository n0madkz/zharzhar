<?php

namespace App\Jobs;

use App\Support\BrokerDirectory;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;

class CollectBrokerVenues implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public int $tries = 1;

    public int $uniqueFor = 3600;

    public function __construct(public string $city)
    {
        $this->onConnection('broker')->onQueue('broker');
    }

    public function uniqueId(): string
    {
        return hash('sha256', $this->city);
    }

    public function handle(BrokerDirectory $directory): void
    {
        $alias = config('broker.cities')[$this->city] ?? null;
        $python = config('broker.parser_python');
        if (! $alias || ! $python) {
            throw new \RuntimeException('Parser worker is not configured.');
        }
        $path = tempnam(storage_path('app'), 'broker-');
        try {
            Cache::put('broker-import:'.$this->uniqueId(), 'Сбор залов выполняется…', 3600);
            $process = new Process([
                $python, base_path('tools/broker/collect.py'),
                '-i', 'https://2gis.kz/'.$alias.'/search/'.rawurlencode('Банкетные залы'),
                '-o', $path, '-f', 'json', '--chrome.headless', 'yes',
                '--chrome.silent-browser', 'yes', '--chrome.disable-images', 'yes',
                '--parser.max-records', '1000', '--parser.delay_between_clicks', '250',
                '--writer.encoding', 'utf8', '--writer.verbose', 'no',
            ], base_path(), null, null, 1740);
            $process->disableOutput();
            $process->mustRun();
            $count = $directory->import(file_get_contents($path), $this->city);
            Cache::put('broker-import:'.$this->uniqueId(), "Сбор завершён. Загружено записей: {$count}. Обновите список.", 86400);
        } finally {
            if ($path && is_file($path)) {
                unlink($path);
            }
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Cache::put('broker-import:'.$this->uniqueId(), 'Сбор не завершён. Администратору нужно проверить Python, Chrome и доступность 2GIS. Можно загрузить JSON вручную.', 86400);
    }
}
