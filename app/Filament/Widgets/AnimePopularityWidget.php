<?php

namespace App\Filament\Widgets;

use App\Models\Anime;
use Filament\Widgets\ChartWidget;

class AnimePopularityWidget extends ChartWidget
{
    protected static ?string $heading = 'Most Popular Anime (by Total Views)';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $topAnime = Anime::with('episodes')
            ->get()
            ->map(function ($anime) {
                $anime->total_views = $anime->episodes->sum('views');
                $anime->total_likes = $anime->episodes->sum('likes');

                return $anime;
            })
            ->sortByDesc('total_views')
            ->take(10);

        $labels = $topAnime->pluck('title')->toArray();
        $viewsData = $topAnime->pluck('total_views')->toArray();
        $likesData = $topAnime->pluck('total_likes')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Views',
                    'data' => $viewsData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.6)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Total Likes',
                    'data' => $likesData,
                    'backgroundColor' => 'rgba(236, 72, 153, 0.6)',
                    'borderColor' => 'rgb(236, 72, 153)',
                    'borderWidth' => 2,
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
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
