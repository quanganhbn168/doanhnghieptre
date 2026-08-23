<?php

namespace Database\Seeders;

use App\Models\SiteAsset;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            RoleAndPermissionSeeder::class,
            DntFoundationSeeder::class,
        ];

        $seeders[] = app()->environment('testing')
            ? TestingContentSeeder::class
            : UserSeeder::class;

        $this->call($seeders);

        SiteAsset::current();
    }
}
