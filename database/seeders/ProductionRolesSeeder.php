<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Production entry point: shared role defaults, no demo users or business records.
        $this->call(RolesSeeder::class);
    }
}
