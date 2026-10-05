<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default seeding is safe for production: no users or business examples.
        $this->call(ProductionRolesSeeder::class);
    }
}
