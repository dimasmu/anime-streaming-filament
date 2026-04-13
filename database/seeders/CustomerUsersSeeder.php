<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Comment;
use App\Models\Anime;
use App\Models\Episode;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class CustomerUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create CUSTOMER role if it doesn't exist
        $customerRole = Role::firstOrCreate(
            ['name' => 'CUSTOMER'],
            ['guard_name' => 'web']
        );

        $this->command->info('CUSTOMER role created/verified.');

        // Create sample customer users
        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Sarah Williams',
                'email' => 'sarah@example.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Chris Brown',
                'email' => 'chris@example.com',
                'password' => Hash::make('password123'),
            ],
        ];

        $createdUsers = [];

        foreach ($customers as $customerData) {
            $user = User::firstOrCreate(
                ['email' => $customerData['email']],
                [
                    'name' => $customerData['name'],
                    'password' => $customerData['password'],
                ]
            );

            // Assign CUSTOMER role
            if (!$user->hasRole('CUSTOMER')) {
                $user->assignRole('CUSTOMER');
            }

            $createdUsers[] = $user;
            $this->command->info("Customer user created: {$user->email}");
        }

        // Create sample comments if anime and episodes exist
        $this->seedComments($createdUsers);
    }

    /**
     * Seed sample comments for anime and episodes
     */
    private function seedComments(array $users): void
    {
        $anime = Anime::with('episodes')->take(5)->get();

        if ($anime->isEmpty()) {
            $this->command->warn('No anime found. Skipping comment seeding.');
            return;
        }

        $sampleComments = [
            'This anime is absolutely amazing! The animation quality is top-notch.',
            'Just finished watching this. What an incredible journey!',
            'The story keeps getting better with each episode.',
            'Highly recommended for anyone who loves this genre.',
            'The character development in this series is outstanding.',
            'Can\'t wait for the next season!',
            'This episode gave me chills!',
            'The plot twist was unexpected and brilliant.',
            'One of the best anime I\'ve watched this year.',
            'The soundtrack is phenomenal.',
        ];

        $sampleReplies = [
            'I totally agree with you!',
            'Same here! This is so good.',
            'Couldn\'t have said it better myself.',
            'Absolutely! The production quality is incredible.',
            'Right? I was thinking the same thing.',
        ];

        $commentsCreated = 0;

        foreach ($anime as $singleAnime) {
            // Create anime comments
            for ($i = 0; $i < 3; $i++) {
                $user = $users[array_rand($users)];
                $comment = Comment::create([
                    'user_id' => $user->id,
                    'anime_id' => $singleAnime->id,
                    'episode_id' => null,
                    'parent_id' => null,
                    'comment' => $sampleComments[array_rand($sampleComments)],
                    'likes_count' => rand(0, 50),
                ]);

                $commentsCreated++;

                // Add some replies
                if (rand(1, 3) === 1) {
                    $replyUser = $users[array_rand($users)];
                    Comment::create([
                        'user_id' => $replyUser->id,
                        'anime_id' => $singleAnime->id,
                        'episode_id' => null,
                        'parent_id' => $comment->id,
                        'comment' => $sampleReplies[array_rand($sampleReplies)],
                        'likes_count' => rand(0, 20),
                    ]);

                    $commentsCreated++;
                }
            }

            // Create episode comments for first episode
            if ($singleAnime->episodes->isNotEmpty()) {
                $firstEpisode = $singleAnime->episodes->first();

                for ($i = 0; $i < 2; $i++) {
                    $user = $users[array_rand($users)];
                    Comment::create([
                        'user_id' => $user->id,
                        'anime_id' => $singleAnime->id,
                        'episode_id' => $firstEpisode->id,
                        'parent_id' => null,
                        'comment' => $sampleComments[array_rand($sampleComments)],
                        'likes_count' => rand(0, 30),
                    ]);

                    $commentsCreated++;
                }
            }
        }

        $this->command->info("Created {$commentsCreated} sample comments.");
    }
}
