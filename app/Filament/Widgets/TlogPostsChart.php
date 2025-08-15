<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class TlogPostsChart extends ChartWidget
{
    protected static ?string $heading = 'Total Number Of  Candidates Registered';

    protected function getData(): array
    {
        // Generate random data for 6 months
        $data = [
            rand(50, 500), // Jan
            rand(50, 500), // Feb
            rand(50, 500), // Mar
            rand(50, 500), // Apr
            rand(50, 500), // May
            rand(50, 500), // Jun
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Number Candidates Registered',
                    'data' => $data,
                    'backgroundColor' => '#36A2EB', // Blue for bars
                    'borderColor' => '#1E90FF',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
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
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Number of Candidates',
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
