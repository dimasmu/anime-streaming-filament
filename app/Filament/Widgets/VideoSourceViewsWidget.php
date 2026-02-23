<?php

namespace App\Filament\Widgets;

use App\Models\VideoUploadSource;
use Filament\Widgets\ChartWidget;

class VideoSourceViewsWidget extends ChartWidget
{
    protected static ?string $heading = 'Total Views per Video Source';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $videoSources = VideoUploadSource::with('episodes')
            ->get()
            ->map(function ($source) {
                $source->total_views = $source->episodes->sum('views');

                return $source;
            })
            ->sortByDesc('total_views')
            ->take(10);

        return [
            'datasets' => [
                [
                    'label' => 'Total Views',
                    'data' => $videoSources->pluck('total_views')->toArray(),
                    'backgroundColor' => 'rgb(59, 130, 246)',
                    'borderColor' => 'rgb(37, 99, 235)',
                ],
            ],
            'labels' => $videoSources->pluck('name')->toArray(),
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
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
