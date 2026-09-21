<?php

use App\Support\BrokerDirectory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
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
        // Broker notes and deal states must survive a deployment rollback.
    }
};
