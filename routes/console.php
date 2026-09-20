<?php

use App\Models\Invitation;
use App\Support\BrokerDirectory;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('broker:import {file=broker-atyrau.json} {--city=Атырау}', function () {
    $filename = basename((string) $this->argument('file'));
    $path = storage_path('app/'.$filename);
    if ($filename !== $this->argument('file') || ! is_file($path) || filesize($path) > 20 * 1024 * 1024) {
        $this->error('JSON file was not found in storage/app or is too large.');

        return self::FAILURE;
    }

    $count = app(BrokerDirectory::class)->import(file_get_contents($path), (string) $this->option('city'));
    $this->info("Imported or updated {$count} broker venues.");

    return self::SUCCESS;
})->purpose('Import parser-2gis JSON from storage/app');

Schedule::call(fn () => Invitation::archiveExpired())
    ->dailyAt('02:15')
    ->name('archive-expired-invitations')
    ->withoutOverlapping();
