<?php

namespace App\Filament\Widgets;

use App\Models\WatchHistory;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RecentActivityWidget extends ChartWidget
{
    protected static ?string $heading = 'Watch Activity (Last 7 Days)';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $last7Days = collect(range(6, 0))->map(function ($days) {
            return now()->subDays($days)->format('Y-m-d');
        });

        $activityData = WatchHistory::select(
            DB::raw('DATE(last_watched_at) as date'),
            DB::raw('COUNT(*) as watch_count'),
            DB::raw('SUM(watch_time_minutes) as total_minutes')
        )
            ->where('last_watched_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('watch_count', 'date')
            ->toArray();

        $labels = $last7Days->map(function ($date) {
            return now()->createFromDate($date)->format('M j');
        })->toArray();

        $data = $last7Days->map(function ($date) use ($activityData) {
            return $activityData[$date] ?? 0;
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Number of Watches',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
