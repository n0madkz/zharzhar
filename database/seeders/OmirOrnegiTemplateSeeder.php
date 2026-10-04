<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OmirOrnegiTemplateSeeder extends Seeder
{
    public function run(): void
    {
        (new InvitationCatalogSeeder)->run('omir-ornegi');
    }
}
