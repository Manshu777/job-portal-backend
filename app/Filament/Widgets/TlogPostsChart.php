<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Candidate;
use Carbon\Carbon;
use Flowframe\Trend\Trend; // Optional but recommended

class TlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Candidates Registered Per Month';
    // protected static ?int $sort = 3;

    // protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        // Best & Cleanest Way: Using Flowframe/Trend
        $trend = Trend::model(Candidate::class)
            ->between(
                start: now()->subMonths(11)->startOfMonth(),
                end: now()->endOfMonth()
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'New Candidates',
                    'data' => $trend->map(fn ($value) => $value->aggregate)->toArray(),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.8)',  // Solid blue
                    'borderColor' => '#1e90ff',
                    'borderWidth' => 2,
                    'borderRadius' => 8,
                    'borderSkipped' => false,
                    'barThickness' => 30,
                    'categoryPercentage' => 0.8,
                    'barPercentage' => 0.9,
                ],
            ],
            'labels' => $trend->map(fn ($value) => Carbon::parse($value->date)->format('M Y'))->toArray(),
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
                'title' => [
                    'display' => true,
                    'text' => 'Candidate Registrations (Last 12 Months)',
                    'font' => ['size' => 16, 'weight' => 'bold'],
                    'color' => '#1f2937',
                ],
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'cornerRadius' => 6,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0, 0, 0, 0.05)',
                    ],
                    'ticks' => [
                        'stepSize' => 50,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Candidates',
                        'font' => ['size' => 14],
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Month',
                        'font' => ['size' => 14],
                    ],
                ],
            ],
            'animation' => [
                'duration' => 1200,
                'easing' => 'easeOutQuart',
            ],
            'hover' => [
                'animationDuration' => 300,
            ],
        ];
    }
}