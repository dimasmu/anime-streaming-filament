<?php

namespace Database\Seeders;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Studio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AnimeSeeder extends Seeder
{
    public function run(): void
    {
        // Get studios
        $mappa = Studio::where('slug', 'mappa')->first();
        $ufotable = Studio::where('slug', 'ufotable')->first();
        $bones = Studio::where('slug', 'bones')->first();
        $a1 = Studio::where('slug', 'a-1-pictures')->first();

        // Get genres
        $action = Genre::where('slug', 'action')->first();
        $adventure = Genre::where('slug', 'adventure')->first();
        $fantasy = Genre::where('slug', 'fantasy')->first();
        $comedy = Genre::where('slug', 'comedy')->first();
        $drama = Genre::where('slug', 'drama')->first();
        $romance = Genre::where('slug', 'romance')->first();
        $sciFi = Genre::where('slug', 'sci-fi')->first();
        $thriller = Genre::where('slug', 'thriller')->first();
        $supernatural = Genre::where('slug', 'supernatural')->first();
        $school = Genre::where('slug', 'school')->first();
        $sports = Genre::where('slug', 'sports')->first();

        // Default images (placeholder)
        $defaultPoster = 'https://via.placeholder.com/300x450/1a1a2e/ffffff?text=Anime+Poster';
        $defaultCover = 'https://via.placeholder.com/1920x1080/1a1a2e/ffffff?text=Anime+Cover';
        $defaultThumbnail = 'https://via.placeholder.com/320x180/1a1a2e/ffffff?text=Episode';

        $animes = [
            [
                'title' => 'Attack on Titan: The Final Season',
                'slug' => 'attack-on-titan-final-season',
                'description' => 'The final season of the epic anime series. Humanity fights for survival against the Titans.',
                'synopsis' => 'After breaching Wall Maria, Titans force humanity to retreat behind Wall Rose. Eren Yeager vows to exterminate all Titans.',
                'type' => 'tv',
                'status' => 'completed',
                'episodes_count' => 28,
                'duration' => 24,
                'release_date' => '2020-12-07',
                'rating' => 9.1,
                'studio_id' => $mappa->id,
                'source' => 'Manga',
                'is_featured' => true,
                'is_published' => true,
                'genres' => [$action->id, $fantasy->id, $thriller->id],
                'episodes' => [
                    ['title' => 'The Other Side of the Sea', 'number' => 1, 'duration' => 24, 'air_date' => '2020-12-07'],
                    ['title' => 'Midnight Train', 'number' => 2, 'duration' => 24, 'air_date' => '2020-12-14'],
                    ['title' => 'The Door of Hope', 'number' => 3, 'duration' => 24, 'air_date' => '2020-12-21'],
                ],
            ],
            [
                'title' => 'Demon Slayer: Kimetsu no Yaiba',
                'slug' => 'demon-slayer',
                'description' => 'A young boy becomes a demon slayer after his family is slaughtered.',
                'synopsis' => 'Tanjiro Kamado\'s family is killed by demons while he is away selling charcoal. His sister Nezuko is turned into a demon.',
                'type' => 'tv',
                'status' => 'completed',
                'episodes_count' => 26,
                'duration' => 23,
                'release_date' => '2019-04-06',
                'rating' => 8.9,
                'studio_id' => $ufotable->id,
                'source' => 'Manga',
                'is_featured' => true,
                'is_published' => true,
                'genres' => [$action->id, $fantasy->id, $supernatural->id],
                'episodes' => [
                    ['title' => 'Cruelty', 'number' => 1, 'duration' => 23, 'air_date' => '2019-04-06'],
                    ['title' => 'Trainee Slayer', 'number' => 2, 'duration' => 23, 'air_date' => '2019-04-13'],
                    ['title' => 'Something Important', 'number' => 3, 'duration' => 23, 'air_date' => '2019-04-20'],
                ],
            ],
            [
                'title' => 'Jujutsu Kaisen',
                'slug' => 'jujutsu-kaisen',
                'description' => 'A boy swallows a cursed talisman and becomes cursed himself.',
                'synopsis' => 'Yuji Itadori swallows Ryomen Sukuna\'s finger and becomes a curse host.',
                'type' => 'tv',
                'status' => 'ongoing',
                'episodes_count' => 24,
                'duration' => 23,
                'release_date' => '2020-10-03',
                'rating' => 8.6,
                'studio_id' => $mappa->id,
                'source' => 'Manga',
                'is_featured' => true,
                'is_published' => true,
                'genres' => [$action->id, $fantasy->id, $supernatural->id],
                'episodes' => [
                    ['title' => 'Ryomen Sukuna', 'number' => 1, 'duration' => 23, 'air_date' => '2020-10-03'],
                    ['title' => 'Go and Search for It', 'number' => 2, 'duration' => 23, 'air_date' => '2020-10-10'],
                    ['title' => 'A Boy Who Can See', 'number' => 3, 'duration' => 23, 'air_date' => '2020-10-17'],
                ],
            ],
            [
                'title' => 'My Hero Academia',
                'slug' => 'my-hero-academia',
                'description' => 'In a world where most people have superpowers, Izuku Midoriya is born without any.',
                'synopsis' => 'Izuku Midoriya dreams of becoming a hero despite having no powers.',
                'type' => 'tv',
                'status' => 'ongoing',
                'episodes_count' => 138,
                'duration' => 23,
                'release_date' => '2016-04-05',
                'rating' => 8.0,
                'studio_id' => $bones->id,
                'source' => 'Manga',
                'is_featured' => true,
                'is_published' => true,
                'genres' => [$action->id, $comedy->id, $school->id],
                'episodes' => [
                    ['title' => 'Izuku Midoriya: The Origin', 'number' => 1, 'duration' => 23, 'air_date' => '2016-04-05'],
                    ['title' => 'What It Takes to Be a Hero', 'number' => 2, 'duration' => 23, 'air_date' => '2016-04-12'],
                    ['title' => 'The Roaring Sports Festival', 'number' => 3, 'duration' => 23, 'air_date' => '2016-04-19'],
                ],
            ],
            [
                'title' => 'One Piece',
                'slug' => 'one-piece',
                'description' => 'Follows the adventures of Monkey D. Luffy and his pirate crew.',
                'synopsis' => 'Monkey D. Luffy sets out to become the Pirate King.',
                'type' => 'tv',
                'status' => 'ongoing',
                'episodes_count' => 1100,
                'duration' => 23,
                'release_date' => '1999-10-20',
                'rating' => 8.7,
                'studio_id' => $a1->id,
                'source' => 'Manga',
                'is_featured' => true,
                'is_published' => true,
                'genres' => [$action->id, $adventure->id, $comedy->id, $fantasy->id],
                'episodes' => [
                    ['title' => 'I\'m Luffy!', 'number' => 1, 'duration' => 23, 'air_date' => '1999-10-20'],
                    ['title' => 'Enter the Great Swordsman!', 'number' => 2, 'duration' => 23, 'air_date' => '1999-10-27'],
                    ['title' => 'An Unbelievable Adventure!', 'number' => 3, 'duration' => 23, 'air_date' => '1999-11-03'],
                ],
            ],
            [
                'title' => 'Death Note',
                'slug' => 'death-note',
                'description' => 'A student discovers a notebook that can kill anyone whose name is written in it.',
                'synopsis' => 'Light Yagami finds the Death Note and decides to create a new world.',
                'type' => 'tv',
                'status' => 'completed',
                'episodes_count' => 37,
                'duration' => 23,
                'release_date' => '2006-10-04',
                'rating' => 9.0,
                'studio_id' => $bones->id,
                'source' => 'Manga',
                'is_featured' => true,
                'is_published' => true,
                'genres' => [$thriller->id, $drama->id, $supernatural->id],
                'episodes' => [
                    ['title' => 'Rebirth', 'number' => 1, 'duration' => 23, 'air_date' => '2006-10-04'],
                    ['title' => 'Confrontation', 'number' => 2, 'duration' => 23, 'air_date' => '2006-10-11'],
                    ['title' => 'Dealings', 'number' => 3, 'duration' => 23, 'air_date' => '2006-10-18'],
                ],
            ],
        ];

        foreach ($animes as $animeData) {
            $episodesData = $animeData['episodes'];
            unset($animeData['episodes']);

            // Create anime
            $anime = Anime::create([
                'title' => $animeData['title'],
                'slug' => $animeData['slug'],
                'description' => $animeData['description'],
                'synopsis' => $animeData['synopsis'],
                'poster_image' => $defaultPoster,
                'cover_image' => $defaultCover,
                'type' => $animeData['type'],
                'status' => $animeData['status'],
                'episodes_count' => $animeData['episodes_count'],
                'duration' => $animeData['duration'],
                'release_date' => $animeData['release_date'],
                'rating' => $animeData['rating'],
                'studio_id' => $animeData['studio_id'],
                'source' => $animeData['source'],
                'is_featured' => $animeData['is_featured'],
                'is_published' => $animeData['is_published'],
            ]);

            // Attach genres
            if (isset($animeData['genres'])) {
                $anime->genres()->attach($animeData['genres']);
            }

            // Create episodes
            foreach ($episodesData as $epData) {
                Episode::create([
                    'anime_id' => $anime->id,
                    'title' => $epData['title'],
                    'episode_number' => $epData['number'],
                    'description' => "Episode {$epData['number']} of {$animeData['title']}: {$epData['title']}",
                    'thumbnail' => $defaultThumbnail,
                    'video_url' => 'https://www.youtube.com/watch?v=dummy',
                    'duration' => $epData['duration'],
                    'air_date' => $epData['air_date'],
                    'is_published' => true,
                    'likes' => rand(50, 500),
                    'views' => rand(1000, 50000),
                ]);
            }
        }

        $this->command->info('Anime seeded successfully!');
    }
}
