<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Run full ETL migration command to populate from legacy database and add modern presets
        $this->command->info('Running ETL Legacy Migration & Presets Seeder...');
        Artisan::call('migrate:legacy-laundry', [], $this->command->getOutput());
    }
}
