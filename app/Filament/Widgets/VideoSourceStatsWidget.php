<?php

namespace App\Filament\Widgets;

use App\Models\VideoUploadSource;
use Filament\Widgets\ChartWidget;

class VideoSourceStatsWidget extends ChartWidget
{
    protected static ?string $heading = 'Episodes per Video Source';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $videoSources = VideoUploadSource::withCount('episodes')
            ->has('episodes')
            ->get()
            ->sortByDesc('episodes_count');

        return [
            'datasets' => [
                [
                    'label' => 'Number of Episodes',
                    'data' => $videoSources->pluck('episodes_count')->toArray(),
                    'backgroundColor' => [
                        'rgb(59, 130, 246)',  // blue
                        'rgb(16, 185, 129)',  // green
                        'rgb(245, 158, 11)',  // yellow
                        'rgb(239, 68, 68)',   // red
                        'rgb(139, 92, 246)',  // purple
                        'rgb(236, 72, 153)',  // pink
                        'rgb(6, 182, 212)',   // cyan
                        'rgb(107, 114, 128)', // gray
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                        'rgb(6, 182, 212)',
                        'rgb(107, 114, 128)',
                    ],
                ],
            ],
            'labels' => $videoSources->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
