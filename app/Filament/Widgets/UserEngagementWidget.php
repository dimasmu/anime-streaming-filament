<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\WatchHistory;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UserEngagementWidget extends ChartWidget
{
    protected static ?string $heading = 'User Engagement Growth (Last 30 Days)';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $last30Days = collect(range(29, 0))->map(function ($days) {
            return now()->subDays($days)->format('Y-m-d');
        });

        // New users per day
        $newUsersData = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Total views per day
        $viewsData = WatchHistory::select(
            DB::raw('DATE(last_watched_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('last_watched_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Completed watches per day
        $completedData = WatchHistory::select(
            DB::raw('DATE(completed_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('completed_at', '>=', now()->subDays(30))
            ->where('is_completed', true)
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $labels = $last30Days->filter(function ($date) use ($newUsersData, $viewsData, $completedData) {
            return isset($newUsersData[$date]) || isset($viewsData[$date]) || isset($completedData[$date]);
        })->map(function ($date) {
            return now()->createFromDate($date)->format('M j');
        })->values()->toArray();

        $newUsers = $last30Days->filter(function ($date) use ($newUsersData) {
            return isset($newUsersData[$date]);
        })->map(function ($date) use ($newUsersData) {
            return $newUsersData[$date] ?? 0;
        })->values()->toArray();

        $views = $last30Days->filter(function ($date) use ($viewsData) {
            return isset($viewsData[$date]);
        })->map(function ($date) use ($viewsData) {
            return $viewsData[$date] ?? 0;
        })->values()->toArray();

        $completed = $last30Days->filter(function ($date) use ($completedData) {
            return isset($completedData[$date]);
        })->map(function ($date) use ($completedData) {
            return $completedData[$date] ?? 0;
        })->values()->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $newUsers,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
                [
                    'label' => 'Total Watches',
                    'data' => $views,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
                [
                    'label' => 'Completed Watches',
                    'data' => $completed,
                    'backgroundColor' => 'rgba(139, 92, 246, 0.5)',
                    'borderColor' => 'rgb(139, 92, 246)',
                    'borderWidth' => 2,
                    'fill' => false,
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
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
