<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MinistryDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Delegates to separate MinistrySeeder and DepartmentSeeder for clean single-responsibility.
     */
    public function run(): void
    {
        $this->call([
            MinistrySeeder::class,
            DepartmentSeeder::class,
        ]);
    }
}
