<?php

namespace Database\Seeders;

use App\Models\VideoUploadSource;
use Illuminate\Database\Seeder;

class VideoUploadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            [
                'name' => 'Google Drive',
                'description' => 'Videos hosted on Google Drive',
                'is_active' => true,
            ],
            [
                'name' => 'MediaFire',
                'description' => 'Videos hosted on MediaFire',
                'is_active' => true,
            ],
            [
                'name' => 'Mega',
                'description' => 'Videos hosted on Mega.nz',
                'is_active' => true,
            ],
            [
                'name' => 'Dropbox',
                'description' => 'Videos hosted on Dropbox',
                'is_active' => true,
            ],
            [
                'name' => 'OneDrive',
                'description' => 'Videos hosted on Microsoft OneDrive',
                'is_active' => false,
            ],
            [
                'name' => 'Direct Link',
                'description' => 'Direct video URLs',
                'is_active' => true,
            ],
            [
                'name' => 'Vimeo',
                'description' => 'Videos hosted on Vimeo',
                'is_active' => false,
            ],
            [
                'name' => 'Self-Hosted',
                'description' => 'Videos hosted on own server',
                'is_active' => true,
            ],
        ];

        foreach ($sources as $source) {
            VideoUploadSource::updateOrCreate(
                ['name' => $source['name']],
                [
                    'description' => $source['description'],
                    'is_active' => $source['is_active'],
                ]
            );
        }
    }
}
