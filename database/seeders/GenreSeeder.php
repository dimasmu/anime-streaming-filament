<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            ['name' => 'Action', 'color' => '#FF6B6B', 'description' => 'High-energy anime with fighting, battles, and intense sequences'],
            ['name' => 'Adventure', 'color' => '#4ECDC4', 'description' => 'Stories involving journeys, exploration, and quests'],
            ['name' => 'Comedy', 'color' => '#FFE66D', 'description' => 'Humorous anime designed to make viewers laugh'],
            ['name' => 'Drama', 'color' => '#A8E6CF', 'description' => 'Serious, emotional stories with character development'],
            ['name' => 'Fantasy', 'color' => '#DDA0DD', 'description' => 'Stories set in magical or supernatural worlds'],
            ['name' => 'Romance', 'color' => '#FFB3BA', 'description' => 'Stories focused on love relationships and emotional connections'],
            ['name' => 'Sci-Fi', 'color' => '#87CEEB', 'description' => 'Science fiction with futuristic technology and concepts'],
            ['name' => 'Thriller', 'color' => '#F0A500', 'description' => 'Suspenseful stories designed to keep viewers on edge'],
            ['name' => 'Horror', 'color' => '#8B0000', 'description' => 'Scary anime designed to frighten and create suspense'],
            ['name' => 'Mystery', 'color' => '#483D8B', 'description' => 'Stories involving puzzles, crimes, or unexplained events'],
            ['name' => 'Slice of Life', 'color' => '#98FB98', 'description' => 'Realistic portrayals of everyday life and ordinary experiences'],
            ['name' => 'Sports', 'color' => '#FFA500', 'description' => 'Stories centered around athletic competitions and sports'],
            ['name' => 'Supernatural', 'color' => '#9370DB', 'description' => 'Stories involving paranormal or otherworldly elements'],
            ['name' => 'Mecha', 'color' => '#708090', 'description' => 'Stories featuring giant robots or mechanical suits'],
            ['name' => 'School', 'color' => '#87CEFA', 'description' => 'Stories set in educational institutions'],
            ['name' => 'Historical', 'color' => '#D2691E', 'description' => 'Stories set in past time periods'],
            ['name' => 'Military', 'color' => '#556B2F', 'description' => 'Stories involving armed forces and warfare'],
            ['name' => 'Music', 'color' => '#FF69B4', 'description' => 'Stories centered around musical themes and performances'],
        ];

        foreach ($genres as $genreData) {
            Genre::create([
                'name' => $genreData['name'],
                'slug' => Str::slug($genreData['name']),
                'description' => $genreData['description'],
                'color' => $genreData['color'],
            ]);
        }
    }
}