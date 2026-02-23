<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create or update the admin user
        User::firstOrCreate(
            ['email' => 'dimasdemond@gmail.com'],
            [
                'name' => 'dimasmu',
                'password' => bcrypt('dimas1213'),
            ]
        );

        $this->call([
            RolePermissionSeeder::class,
            VideoUploadSourceSeeder::class,
            StudioSeeder::class,
            GenreSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
