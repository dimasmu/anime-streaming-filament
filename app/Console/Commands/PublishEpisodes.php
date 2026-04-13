<?php

namespace App\Console\Commands;

use App\Models\Episode;
use App\Models\Anime;
use Illuminate\Console\Command;

class PublishEpisodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'episodes:publish
                            {--all : Publish all episodes}
                            {--anime-slug= : Publish episodes for a specific anime by slug}
                            {--anime-id= : Publish episodes for a specific anime by ID}
                            {--unpublish : Unpublish instead of publish}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish or unpublish episodes for viewing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isPublish = !$this->option('unpublish');
        $action = $isPublish ? 'publish' : 'unpublish';

        if ($this->option('all')) {
            if (!$this->confirm("Are you sure you want to {$action} ALL episodes?")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }

            $count = Episode::query()->update(['is_published' => $isPublish]);
            $this->info("Successfully {$action}ed {$count} episodes.");

        } elseif ($animeSlug = $this->option('anime-slug')) {
            $anime = Anime::where('slug', $animeSlug)->first();

            if (!$anime) {
                $this->error("Anime not found with slug: {$animeSlug}");
                return Command::FAILURE;
            }

            $count = Episode::where('anime_id', $anime->id)
                ->update(['is_published' => $isPublish]);

            $this->info("Successfully {$action}ed {$count} episodes for '{$anime->title}'.");

        } elseif ($animeId = $this->option('anime-id')) {
            $anime = Anime::find($animeId);

            if (!$anime) {
                $this->error("Anime not found with ID: {$animeId}");
                return Command::FAILURE;
            }

            $count = Episode::where('anime_id', $anime->id)
                ->update(['is_published' => $isPublish]);

            $this->info("Successfully {$action}ed {$count} episodes for '{$anime->title}'.");

        } else {
            $this->error('Please specify --all, --anime-slug, or --anime-id');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
