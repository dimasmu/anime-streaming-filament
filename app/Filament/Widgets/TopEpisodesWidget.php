<?php

namespace App\Filament\Widgets;

use App\Models\Episode;
use Filament\Widgets\ChartWidget;

class TopEpisodesWidget extends ChartWidget
{
    protected static ?string $heading = 'Top 10 Episodes by Views';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $topEpisodes = Episode::with(['anime'])
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        $labels = $topEpisodes->map(function ($episode) {
            $animeTitle = $episode->anime ? $episode->anime->title : 'Unknown';
            $title = $episode->title ?? "EP {$episode->episode_number}";

            return "{$animeTitle} - EP {$episode->episode_number}";
        })->toArray();

        $data = $topEpisodes->pluck('views')->toArray();

        $likesData = $topEpisodes->pluck('likes')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Views',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Likes',
                    'data' => $likesData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
                'x' => [
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 45,
                        'autoSkip' => false,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
            'indexAxis' => 'y',
        ];
    }
}
