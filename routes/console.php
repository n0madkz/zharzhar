<?php

use App\Models\Invitation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;
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
    file_put_contents($marker, now()->toIso8601String());
    $this->newLine();
    $this->info('parser-2gis installed: '.$python);

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

    file_put_contents((string) config('broker.parser_marker'), now()->toIso8601String());
    $this->info('Парсер готов: '.$python);

    return self::SUCCESS;
})->purpose('Check the broker parser-2gis installation');

Schedule::call(fn () => Invitation::archiveExpired())
    ->dailyAt('02:15')
    ->name('archive-expired-invitations')
    ->withoutOverlapping();
