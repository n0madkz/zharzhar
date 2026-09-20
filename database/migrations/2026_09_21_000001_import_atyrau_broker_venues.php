<?php

use App\Support\BrokerDirectory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // This is production reference data. Tests import their own fixtures so
        // assertions remain isolated and deterministic.
        if (app()->environment('testing')) {
            return;
        }

        $path = database_path('data/broker-atyrau.json');
        if (is_file($path)) {
            app(BrokerDirectory::class)->import(file_get_contents($path), 'Атырау');
        }
    }

    public function down(): void
    {
        // Imported prospects may already contain broker notes and deal state.
        // A rollback must not destroy that operational data.
    }
};
