<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class BrokerParser
{
    public function __construct(private readonly BrokerDirectory $directory) {}

    public function collect(string $city): int
    {
        $alias = config("broker.cities.{$city}");
        $python = config('broker.parser_python');

        if (! $alias || ! $python || ! is_file($python)) {
            throw ValidationException::withMessages([
                'city' => 'Парсер 2GIS ещё не установлен на сервере. Администратору нужно один раз выполнить команду broker:parser-install.',
            ]);
        }

        $path = tempnam(storage_path('app'), 'broker-');

        try {
            $process = new Process([
                $python, base_path('tools/broker/collect.py'),
                '-i', 'https://2gis.kz/'.$alias.'/search/'.rawurlencode('Банкетные залы'),
                '-o', $path, '-f', 'json', '--chrome.headless', 'yes',
                '--chrome.start-maximized', 'yes', '--chrome.silent-browser', 'yes', '--chrome.disable-images', 'yes',
                '--parser.max-records', '1000', '--parser.delay_between_clicks', '250',
                '--writer.encoding', 'utf8', '--writer.verbose', 'no',
            ], base_path(), ['DEBUG' => ''], null, 1740);
            $process->mustRun();

            return $this->directory->import(file_get_contents($path), $city);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages([
                'city' => 'Не удалось получить данные из 2GIS. Проверьте установку parser-2gis и Chrome, затем повторите.',
            ]);
        } finally {
            if ($path && is_file($path)) {
                unlink($path);
            }
        }
    }
}
