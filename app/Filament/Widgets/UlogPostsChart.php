<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use App\Models\JobPostingApplication;

class UlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Job Applications by Month';

    protected function getData(): array
    {
        $trend = Trend::query(JobPostingApplication::query())
            ->between(
                start: now()->subMonths(11)->startOfMonth(),
                end: now()->endOfMonth()
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Job Applications',
                    'data' => $trend->map(fn($item) => $item->aggregate)->toArray(),
                    'borderColor' => '#10b981', // Emerald green
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#10b981',
                    'pointHoverBackgroundColor' => '#059669',
                    'pointRadius' => 5,
                ],
            ],
            'labels' => $trend->map(fn($item) => Carbon::parse($item->date)->format('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

   protected function getOptions(): array   // ← FIXED HERE
{
    return [
        'plugins' => [
            'title' => [
                'display' => true,
                'text' => 'Monthly Job Applications (Last 12 Months)',
                'font' => ['size' => 16],
            ],
            'legend' => [
                'position' => 'top',
            ],
            'tooltip' => [
                'mode' => 'index',
                'intersect' => false,
            ],
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'grid' => ['display' => true],
                'title' => [
                    'display' => true,
                    'text' => 'Number of Applications',
                    'font' => ['size' => 14],
                ],
            ],
            'x' => [
                'grid' => ['display' => false],
                'title' => [
                    'display' => true,
                    'text' => 'Month',
                    'font' => ['size' => 14],
                ],
            ],
        ],
        'interaction' => [
            'mode' => 'nearest',
            'axis' => 'x',
            'intersect' => false,
        ],
        'animation' => [
            'duration' => 1500,
            'easing' => 'easeOutQuart',
        ],
    ];
}

   
}
