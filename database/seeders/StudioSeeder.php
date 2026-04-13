<?php

namespace Database\Seeders;

use App\Models\Studio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudioSeeder extends Seeder
{
    public function run(): void
    {
        $studios = [
            [
                'name' => 'Studio Ghibli',
                'description' => 'Japanese animation film studio headquartered in Koganei, Tokyo. Known for animated feature films such as Spirited Away, My Neighbor Totoro, and Princess Mononoke.',
                'website' => 'https://www.ghibli.jp/',
                'founded_year' => 1985,
            ],
            [
                'name' => 'Toei Animation',
                'description' => 'Japanese animation studio primarily controlled by the Toei Company. Known for Dragon Ball, One Piece, and Sailor Moon.',
                'website' => 'https://www.toei-anim.co.jp/',
                'founded_year' => 1948,
            ],
            [
                'name' => 'Madhouse',
                'description' => 'Japanese animation studio founded by ex-Mushi Pro staff. Known for Death Note, One Punch Man, and Hunter x Hunter.',
                'website' => 'https://www.madhouse.co.jp/',
                'founded_year' => 1972,
            ],
            [
                'name' => 'Studio Pierrot',
                'description' => 'Japanese animation studio established in 1979. Known for Naruto, Bleach, and Tokyo Ghoul.',
                'website' => 'https://pierrot.jp/',
                'founded_year' => 1979,
            ],
            [
                'name' => 'Bones',
                'description' => 'Japanese animation studio. Known for Fullmetal Alchemist, My Hero Academia, and Mob Psycho 100.',
                'website' => 'https://www.bones.co.jp/',
                'founded_year' => 1998,
            ],
            [
                'name' => 'Mappa',
                'description' => 'Japanese animation studio established by former Madhouse producer. Known for Attack on Titan (final season), Jujutsu Kaisen, and Chainsaw Man.',
                'website' => 'https://www.mappa.co.jp/',
                'founded_year' => 2011,
            ],
            [
                'name' => 'Studio Trigger',
                'description' => 'Japanese animation studio founded by former Gainax employees. Known for Kill la Kill, Little Witch Academia, and Cyberpunk: Edgerunners.',
                'website' => 'https://www.st-trigger.co.jp/',
                'founded_year' => 2011,
            ],
            [
                'name' => 'Wit Studio',
                'description' => 'Japanese animation studio founded by former Production I.G and Madhouse staff. Known for Attack on Titan (seasons 1-3) and Spy x Family.',
                'website' => 'https://witstudio.co.jp/',
                'founded_year' => 2012,
            ],
            [
                'name' => 'A-1 Pictures',
                'description' => 'Japanese animation studio established as a subsidiary of Aniplex. Known for Sword Art Online, Fairy Tail, and Your Lie in April.',
                'website' => 'https://www.a1p.jp/',
                'founded_year' => 2005,
            ],
            [
                'name' => 'Kyoto Animation',
                'description' => 'Japanese animation studio known for high-quality animation. Known for K-On!, Violet Evergarden, and A Silent Voice.',
                'website' => 'https://www.kyotoanimation.co.jp/',
                'founded_year' => 1981,
            ],
            [
                'name' => 'Ufotable',
                'description' => 'Japanese animation studio known for their use of digital animation and 3D backgrounds. Known for Demon Slayer and Fate series.',
                'website' => 'https://www.ufotable.com/',
                'founded_year' => 2000,
            ],
            [
                'name' => 'CloverWorks',
                'description' => 'Japanese animation studio established as a subsidiary of Aniplex. Known for The Promised Neverland, Horimiya, and Spy x Family.',
                'website' => 'https://www.cloverworks.co.jp/',
                'founded_year' => 2018,
            ],
            [
                'name' => 'Production I.G',
                'description' => 'Japanese animation studio known for Ghost in the Shell, Haikyuu!!, and Psycho-Pass.',
                'website' => 'https://www.production-ig.co.jp/',
                'founded_year' => 1987,
            ],
            [
                'name' => 'Sunrise',
                'description' => 'Japanese animation studio known for mecha anime series. Known for Gundam series, Code Geass, and Cowboy Bebop.',
                'website' => 'https://www.sunrise-inc.co.jp/',
                'founded_year' => 1972,
            ],
            [
                'name' => 'TMS Entertainment',
                'description' => 'Japanese animation studio. Known for Lupin III, Detective Conan, and Megalobox.',
                'website' => 'https://www.tms-e.co.jp/',
                'founded_year' => 1946,
            ],
        ];

        foreach ($studios as $studioData) {
            Studio::firstOrCreate(
                ['slug' => Str::slug($studioData['name'])],
                [
                    'name' => $studioData['name'],
                    'description' => $studioData['description'],
                    'website' => $studioData['website'],
                    'founded_year' => $studioData['founded_year'],
                    'is_active' => true,
                ]
            );
        }
    }
}
