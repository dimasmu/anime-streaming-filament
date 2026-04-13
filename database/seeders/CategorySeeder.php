<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'TV Series',
                'icon' => 'heroicon-o-tv',
                'description' => 'Regular television anime series with multiple episodes',
            ],
            [
                'name' => 'Movies',
                'icon' => 'heroicon-o-film',
                'description' => 'Anime feature films and standalone movies',
            ],
            [
                'name' => 'OVA',
                'icon' => 'heroicon-o-video-camera',
                'description' => 'Original Video Animation - anime made specifically for home video',
            ],
            [
                'name' => 'ONA',
                'icon' => 'heroicon-o-globe-alt',
                'description' => 'Original Net Animation - anime made specifically for internet streaming',
            ],
            [
                'name' => 'Special',
                'icon' => 'heroicon-o-star',
                'description' => 'Special episodes, recap episodes, or bonus content',
            ],
            [
                'name' => 'Short',
                'icon' => 'heroicon-o-clock',
                'description' => 'Short-form anime with episodes typically under 15 minutes',
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['slug' => Str::slug($categoryData['name'])],
                [
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'icon' => $categoryData['icon'],
                ]
            );
        }
    }
}
