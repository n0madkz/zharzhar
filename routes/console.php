<?php

use App\Models\Invitation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Symfony\Component\Process\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('broker:parser-install {--python=python3}', function () {
    $directory = storage_path('app/broker-parser');
    $venv = new Process([(string) $this->option('python'), '-m', 'venv', $directory], base_path(), null, null, 300);
    $venv->setTty(false)->mustRun(fn ($type, $buffer) => $this->output->write($buffer));
    $python = PHP_OS_FAMILY === 'Windows' ? $directory.'/Scripts/python.exe' : $directory.'/bin/python';
    $install = new Process([$python, '-m', 'pip', 'install', '--disable-pip-version-check', '-r', base_path('tools/broker/requirements.txt')], base_path(), null, null, 900);
    $install->setTty(false)->mustRun(fn ($type, $buffer) => $this->output->write($buffer));
    $this->newLine();
    $this->info('parser-2gis installed: '.$python);

    return self::SUCCESS;
})->purpose('Install the pinned interlark/parser-2gis package for the broker cabinet');

Schedule::call(fn () => Invitation::archiveExpired())
    ->dailyAt('02:15')
    ->name('archive-expired-invitations')
    ->withoutOverlapping();
