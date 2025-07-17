<?php

namespace Database\Seeders;

use App\Models\VideoUploadType;
use Illuminate\Database\Seeder;

class VideoUploadTypeSeeder extends Seeder
{
    public function run(): void
    {
        $uploadTypes = [
            [
                'name' => 'URL',
                'description' => 'Direct video URL link',
                'is_active' => true,
            ],
            [
                'name' => 'Local',
                'description' => 'Video file uploaded to local server',
                'is_active' => true,
            ],
            [
                'name' => 'YouTube',
                'description' => 'YouTube video link',
                'is_active' => true,
            ],
            [
                'name' => 'Embedded',
                'description' => 'Embedded video player code',
                'is_active' => true,
            ],
            [
                'name' => 'x265',
                'description' => 'x265 encoded video format',
                'is_active' => true,
            ],
        ];

        foreach ($uploadTypes as $uploadType) {
            VideoUploadType::firstOrCreate(
                ['name' => $uploadType['name']],
                $uploadType
            );
        }
    }
}
