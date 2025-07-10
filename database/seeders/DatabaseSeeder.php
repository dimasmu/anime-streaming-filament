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

        User::factory()->create([
            'name' => 'dimasmu',
            'email' => 'dimasdemond@gmail.com',
            'password' => 'dimas1213'
        ]);

        $this->call([
            RolePermissionSeeder::class,
            StudioSeeder::class,
            GenreSeeder::class,
            CategorySeeder::class,
        ]);
    }
}
