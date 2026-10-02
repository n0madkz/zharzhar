<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BesikToiTemplateSeeder extends Seeder
{
    public function run(): void
    {
        (new InvitationCatalogSeeder)->run('besik-toi');
    }
}
