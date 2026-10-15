<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductionRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Production entry point: roles, permissions and one default department; no accounts or business examples.
        $this->call([RolesSeeder::class, DepartmentsSeeder::class]);
    }
}
