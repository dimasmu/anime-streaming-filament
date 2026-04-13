<?php

namespace App\Filament\Widgets;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\User;
use App\Models\WatchHistory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        // Total views across all episodes
        $totalViews = Episode::sum('views') ?? 0;

        // Total likes across all episodes
        $totalLikes = Episode::sum('likes') ?? 0;

        // Total users
        $totalUsers = User::count();

        // Total anime
        $totalAnime = Anime::published()->count();

        // Total episodes
        $totalEpisodes = Episode::published()->count();

        // Total watch time (in hours)
        $totalWatchTimeMinutes = WatchHistory::sum('watch_time_minutes') ?? 0;
        $totalWatchTimeHours = round($totalWatchTimeMinutes / 60, 1);

        // Most viewed episode
        $mostViewedEpisode = Episode::orderBy('views', 'desc')->first();
        $mostViewedEpisodeLabel = $mostViewedEpisode
            ? ($mostViewedEpisode->anime ? $mostViewedEpisode->anime->title.' - EP '.$mostViewedEpisode->episode_number : 'EP '.$mostViewedEpisode->episode_number)
            : 'N/A';
        $mostViewedEpisodeCount = $mostViewedEpisode ? $mostViewedEpisode->views : 0;

        // Most watched episode (based on watch history)
        $mostWatchedEpisode = DB::table('watch_history')
            ->select('episode_id', DB::raw('COUNT(*) as watch_count'), DB::raw('SUM(watch_time_minutes) as total_watch_time'))
            ->groupBy('episode_id')
            ->orderBy('watch_count', 'desc')
            ->first();

        $mostWatchedEpisodeLabel = 'N/A';
        $mostWatchedEpisodeCount = 0;

        if ($mostWatchedEpisode) {
            $episode = Episode::find($mostWatchedEpisode->episode_id);
            if ($episode) {
                $mostWatchedEpisodeLabel = $episode->anime
                    ? $episode->anime->title.' - EP '.$episode->episode_number
                    : 'EP '.$episode->episode_number;
                $mostWatchedEpisodeCount = $mostWatchedEpisode->watch_count;
            }
        }

        return [
            Stat::make('Total Views', number_format($totalViews))
                ->description('Total views across all episodes')
                ->descriptionIcon('heroicon-m-eye')
                ->chart([7, 12, 10, 14, 15, 18, 20])
                ->color('info'),

            Stat::make('Total Likes', number_format($totalLikes))
                ->description('Total likes across all episodes')
                ->descriptionIcon('heroicon-m-heart')
                ->color('success'),

            Stat::make('Most Viewed Video', $mostViewedEpisodeLabel)
                ->description(number_format($mostViewedEpisodeCount).' views')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('Most Watched Video', $mostWatchedEpisodeLabel)
                ->description($mostWatchedEpisodeCount.' times watched')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('primary'),

            Stat::make('Total Users', number_format($totalUsers))
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Total Watch Time', $totalWatchTimeHours.' hours')
                ->description('Total time spent watching')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Total Anime', number_format($totalAnime))
                ->description('Published anime')
                ->descriptionIcon('heroicon-m-film')
                ->color('primary'),

            Stat::make('Total Episodes', number_format($totalEpisodes))
                ->description('Published episodes')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('gray'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
