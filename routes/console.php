<?php

use App\Models\Invitation;
use App\Support\BrokerParser;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('broker:parser-install {--python=}', function () {
    $requested = trim((string) $this->option('python'));
    $candidates = $requested !== ''
        ? [[$requested]]
        : (PHP_OS_FAMILY === 'Windows'
            ? [['python'], ['py', '-3']]
            : array_map(fn ($path) => [$path], [
                '/opt/alt/python312/bin/python3', '/opt/alt/python311/bin/python3',
                '/opt/alt/python310/bin/python3', '/opt/alt/python39/bin/python3',
                '/opt/alt/python38/bin/python3', '/usr/local/bin/python3.12',
                '/usr/local/bin/python3.11', '/usr/local/bin/python3.10',
                '/usr/local/bin/python3.9', '/usr/local/bin/python3.8',
                '/usr/bin/python3.12', '/usr/bin/python3.11', '/usr/bin/python3.10',
                '/usr/bin/python3.9', '/usr/bin/python3.8', 'python3', 'python',
            ]));
    $systemPython = null;

    foreach ($candidates as $candidate) {
        $check = new Process([...$candidate, '-c', 'import sys; print(".".join(map(str, sys.version_info[:3]))); raise SystemExit(0 if sys.version_info >= (3, 8) else 2)'], base_path(), null, null, 15);
        $check->run();
        if ($check->isSuccessful()) {
            $systemPython = $candidate;
            break;
        }
        if ($check->getExitCode() === 2) {
            $this->warn(implode(' ', $candidate).': Python '.trim($check->getOutput()).' пропущен, требуется 3.8 или новее.');
        }
    }

    if (! $systemPython) {
        $this->error('Python 3.8 или новее не найден. Включите современный Python в Plesk/CloudLinux или передайте путь: broker:parser-install --python=/путь/python3');

        return self::FAILURE;
    }

    $this->info('Python: '.implode(' ', $systemPython));
    $directory = storage_path('app/broker-parser');
    $marker = (string) config('broker.parser_marker');
    if (File::isDirectory($directory)) {
        File::deleteDirectory($directory);
    }
    File::ensureDirectoryExists(dirname($directory));
    $venv = new Process([...$systemPython, '-m', 'venv', $directory], base_path(), null, null, 300);
    $venv->setTty(false)->mustRun(fn ($type, $buffer) => $this->output->write($buffer));
    $python = PHP_OS_FAMILY === 'Windows' ? $directory.'/Scripts/python.exe' : $directory.'/bin/python';
    $upgrade = new Process([$python, '-m', 'pip', 'install', '--disable-pip-version-check', '--upgrade', 'pip', 'setuptools', 'wheel'], base_path(), null, null, 600);
    $upgrade->setTty(false)->mustRun(fn ($type, $buffer) => $this->output->write($buffer));
    $install = new Process([$python, '-m', 'pip', 'install', '--disable-pip-version-check', '-r', base_path('tools/broker/requirements.txt')], base_path(), null, null, 900);
    $install->setTty(false)->mustRun(fn ($type, $buffer) => $this->output->write($buffer));
    $browserDirectory = (string) config('broker.browser_directory');
    File::ensureDirectoryExists($browserDirectory);
    $browserEnvironment = ['PLAYWRIGHT_BROWSERS_PATH' => $browserDirectory];
    $browserInstall = new Process([$python, '-m', 'playwright', 'install', 'chromium'], base_path(), $browserEnvironment, null, 1200);
    $browserInstall->setTty(false)->mustRun(fn ($type, $buffer) => $this->output->write($buffer));
    $browserPath = new Process([$python, '-c', 'from playwright.sync_api import sync_playwright; p=sync_playwright().start(); print(p.chromium.executable_path); p.stop()'], base_path(), $browserEnvironment, null, 60);
    $browserPath->mustRun();
    $chrome = trim($browserPath->getOutput());
    if (! is_file($chrome)) {
        $this->error('Chromium загружен, но исполняемый файл не найден: '.$chrome);

        return self::FAILURE;
    }
    file_put_contents((string) config('broker.chrome_path_file'), $chrome);
    file_put_contents($marker, now()->toIso8601String());
    $this->newLine();
    $this->info('parser-2gis installed: '.$python);
    $this->info('Chromium installed: '.$chrome);

    return self::SUCCESS;
})->purpose('Install the pinned interlark/parser-2gis package for the broker cabinet');

Artisan::command('broker:parser-check', function () {
    $python = (string) config('broker.parser_python');
    if (! is_file($python)) {
        $this->error('Парсер не установлен: '.$python);
        $this->line('Выполните: broker:parser-install');

        return self::FAILURE;
    }

    $check = new Process([$python, '-c', 'import parser_2gis, pychrome; print("parser-2gis OK")'], base_path(), null, null, 30);
    $check->run(fn ($type, $buffer) => $this->output->write($buffer));
    if (! $check->isSuccessful()) {
        $this->error('Установка повреждена. Повторите: broker:parser-install');

        return self::FAILURE;
    }

    $chrome = trim((string) @file_get_contents((string) config('broker.chrome_path_file')));
    if (! is_file($chrome)) {
        $this->error('Chromium для парсера не найден. Повторите: broker:parser-install');

        return self::FAILURE;
    }
    $browserCheck = new Process([$chrome, '--headless', '--no-sandbox', '--disable-gpu', '--dump-dom', 'about:blank'], base_path(), null, null, 30);
    $browserCheck->run();
    if (! $browserCheck->isSuccessful()) {
        $this->warn('Chromium недоступен; будет использоваться прямой сбор публичных страниц 2GIS без браузера.');
        $this->line(trim($browserCheck->getErrorOutput()));
        $this->info('Прямой сбор 2GIS готов и не требует системных библиотек Chromium.');

        return self::SUCCESS;
    }

    file_put_contents((string) config('broker.parser_marker'), now()->toIso8601String());
    $this->info('Парсер готов: '.$python);
    $this->info('Chromium готов: '.$chrome);

    return self::SUCCESS;
})->purpose('Check the broker parser-2gis installation');

Artisan::command('broker:collect {city=Атырау}', function () {
    $city = (string) $this->argument('city');
    if (! array_key_exists($city, config('broker.cities'))) {
        $this->error('Неизвестный город: '.$city);

        return self::FAILURE;
    }

    $this->info('Загрузка банкетных залов из 2GIS: '.$city);
    try {
        $count = app(BrokerParser::class)->collect($city);
    } catch (ValidationException $exception) {
        $this->error(collect($exception->errors())->flatten()->first() ?: 'Не удалось загрузить данные из 2GIS.');

        return self::FAILURE;
    }
    $this->info('Загружено залов: '.$count);

    return self::SUCCESS;
})->purpose('Load a city banquet hall directory from 2GIS');

Schedule::call(fn () => Invitation::archiveExpired())
    ->dailyAt('02:15')
    ->name('archive-expired-invitations')
    ->withoutOverlapping();
