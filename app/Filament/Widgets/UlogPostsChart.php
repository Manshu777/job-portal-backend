<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class UlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Job Applications by Month';

    protected function getData(): array
    {
        // Generate random data for job applications over 6 months
        $data = [
            rand(10, 100), // Jan
            rand(10, 100), // Feb
            rand(10, 100), // Mar
            rand(10, 100), // Apr
            rand(10, 100), // May
            rand(10, 100), // Jun
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Job Applications',
                    'data' => $data,
                    'borderColor' => '#FF6384', // Pinkish-red for line
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)', // Light fill under line
                    'fill' => true,
                    'tension' => 0.4, // Smooth curve
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
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
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Applications',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Month',
                    ],
                ],
            ],
        ];
    }

   
}
