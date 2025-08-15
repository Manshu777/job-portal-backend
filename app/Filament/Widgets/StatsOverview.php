<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Forms\Components\DatePicker;
class StatsOverview extends BaseWidget
{

     protected function getFormSchema(): array
    {
        return [
            DatePicker::make('start_date')
                ->label('Start Date')
                ->default(now()->startOfMonth()),

            DatePicker::make('end_date')
                ->label('End Date')
                ->default(now()),
        ];
    }

    protected function getStats(): array
    {
        // Calculate percentage for Active Jobs
        $activeJobs = 150;
        $totalJobs = 200;
        $activeJobsPercentage = number_format(($activeJobs / $totalJobs) * 100, 0) . '%';

        return [
            Stat::make('Total Candidates', '240')
                ->description('Registered candidates')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([150, 170, 190, 210, 230, 240])
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                    'title' => 'Click to view candidate details',
                ]),

            Stat::make('Total Employers', '44')
                ->description('Companies using the platform')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([30, 35, 38, 40, 42, 44])
                ->color('primary')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                    'title' => 'Click to view employer list',
                ]),



            Stat::make('Total Jobs', '200')
                ->description('Jobs posted')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([120, 140, 160, 180, 190, 200])
                ->color('info')
                ->icon('heroicon-o-clipboard-document')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                    'title' => 'Click to view all jobs',
                ]),

            
            Stat::make('Pending Employers Verify', '29')
                ->description('Companies using the platform')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([30, 35, 38, 40, 42, 44])
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                    'title' => 'Click to view employer list',
                ]),


                  Stat::make('Pending Job Post ', '12')
                ->description('Companies using the platform')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([30, 35, 38, 40, 42, 44])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                    'title' => 'Click to view employer list',
                ]),

            Stat::make('Active Jobs', $activeJobsPercentage)
                ->description("{$activeJobs} of {$totalJobs} jobs are active")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([100, 110, 120, 130, 140, 150])
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                    'title' => 'Click to view active jobs',
                ]),

            Stat::make('Job Applications', '120')
                ->description('Total job applications submitted')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([50, 70, 90, 100, 110, 120])
                ->color('danger')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                    'title' => 'Click to view applications',
                ]),

            Stat::make('New Users Today', '22')
                ->description('Users registered today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([5, 8, 12, 15, 18, 22])
                ->color('gray')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                    'title' => 'Click to view new users',
                ]),

            
            Stat::make('Block Employers ', '10')
                ->description('Companies using the platform')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([30, 35, 38, 40, 42, 44])
                ->color('danger')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                    'title' => 'Click to view employer list',
                ]),
        ];
    }
}