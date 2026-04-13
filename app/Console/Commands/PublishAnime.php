<?php

namespace App\Console\Commands;

use App\Models\Anime;
use Illuminate\Console\Command;

class PublishAnime extends Command
{
    protected $signature = 'anime:publish
        {--all : Publish all anime}
        {--id=* : Anime IDs to publish (can be passed multiple times)}
        {--unpublish : Unpublish instead of publish}';

    protected $description = 'Publish or unpublish anime to make them visible in the API';

    public function handle(): int
    {
        $all = (bool) $this->option('all');
        $ids = array_values(array_filter((array) $this->option('id')));
        $unpublish = (bool) $this->option('unpublish');

        if (! $all && count($ids) === 0) {
            $this->error('Provide --all or one/more --id= values.');
            return self::INVALID;
        }

        $query = Anime::query();

        if (! $all) {
            $query->whereIn('id', $ids);
        }

        $count = $query->count();
        $action = $unpublish ? 'unpublish' : 'publish';

        if ($count === 0) {
            $this->warn("No anime found to {$action}.");
            return self::SUCCESS;
        }

        if ($all && ! $this->confirm("Are you sure you want to {$action} {$count} anime?", true)) {
            $this->info('Cancelled.');
            return self::SUCCESS;
        }

        $updated = $query->update(['is_published' => ! $unpublish]);

        $this->info("Successfully {$action}ed {$updated} anime.");

        return self::SUCCESS;
    }
}
