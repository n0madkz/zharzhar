<?php

namespace App\Jobs;

use App\Support\BrokerParser;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

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

    public function handle(BrokerParser $parser): void
    {
        Cache::put('broker-import:'.$this->uniqueId(), 'Сбор залов выполняется…', 3600);
        $count = $parser->collect($this->city);
        Cache::put('broker-import:'.$this->uniqueId(), "Сбор завершён. Загружено записей: {$count}.", 86400);
    }

    public function failed(?\Throwable $exception): void
    {
        Cache::put('broker-import:'.$this->uniqueId(), 'Сбор не завершён. Проверьте Python, Chrome и доступность 2GIS.', 86400);
    }
}
