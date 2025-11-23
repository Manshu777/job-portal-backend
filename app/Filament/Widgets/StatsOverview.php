<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Forms\Components\DatePicker;
use App\Models\Candidate;
use App\Models\Employer;
use App\Models\JobPosting;


use Carbon\Carbon;
/// Employer JobPosting
class StatsOverview extends BaseWidget
{

     protected function getFormSchema(): array
    {
        return [
           DatePicker::make('start_date')
                    ->label('From Date')
                    ->default(now()->startOfMonth())
                    ->maxDate(now()),

                DatePicker::make('end_date')
                    ->label('To Date')
                    ->default(now())
                    ->minDate(fn ($get) => $get('start_date'))
                    ->maxDate(now()),
        ];
    }

    protected function getStats(): array
    {
      $startDate = $this->data['start_date'] ?? null;
        $endDate   = $this->data['end_date'] ?? Carbon::today();
        // Query with date filters
        $query = Candidate::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        $filterByDate = function ($query) use ($startDate, $endDate) {
            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }
            return $query;
        };

        $totalCandidates     = $filterByDate(Candidate::query())->count();

        $newCandidatesToday  = Candidate::whereDate('created_at', today())->count();

        $totalEmployers      = Employer::count();
        $unverifiedEmployers = Employer::where('is_verified', false)->count();
        $blockedEmployers    = Employer::where('is_blocked', true)->count();

        $totalJobs           = JobPosting::count();
        $activeJobs          = JobPosting::where('status', 'active')->count();
        $pendingJobs         = JobPosting::where('status', 'pending')->count();
        $activeJobsPercent   = $totalJobs > 0 ? round(($activeJobs / $totalJobs) * 100) : 0;

        $totalApplications   = \App\Models\JobPostingApplication::count(); // adjust

        // Example static data (replace with real query later)
       
        $activeJobsPercentage = $totalJobs > 0 ? number_format(($activeJobs / $totalJobs) * 100, 0) . '%' : '0%';

        return [ 
            Stat::make('Total Candidates', number_format($totalCandidates))
                ->description('Registered candidates in selected period')
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 8, 9, 11, 14, 16, $totalCandidates > 20 ? 20 : $totalCandidates]) // dummy chart
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow duration-200',
                    'wire:click' => "dispatch('open-candidates')", // optional: trigger event
                ]),

           Stat::make('Active Job Postings', $activeJobs)
                ->description("{$activeJobsPercent}% of total jobs are live")
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('warning'),

            Stat::make('Pending Job Approvals', $pendingJobs)
                ->description('Jobs awaiting approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make('Total Employers', number_format($totalEmployers))
                ->description("{$unverifiedEmployers} pending verification")
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            Stat::make('Unverified Employers', $unverifiedEmployers)
                ->description('Need email/phone verification')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),


               Stat::make('Blocked Employers', $blockedEmployers)
                ->description('Suspended accounts')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color('danger'),

            Stat::make('Total Job Applications', number_format($totalApplications))
                ->description('All time applications received')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('New Candidates Today', $newCandidatesToday)
                ->description('Registered in last 24 hours')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('New Users Today', '22')
                ->description('Users registered today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([5, 8, 12, 15, 18, 22])
                ->color('gray')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                    'title' => 'Click to view new users',
                ]),

            
            // Stat::make('Block Employers ', '10')
            //     ->description('Companies using the platform')
            //     ->descriptionIcon('heroicon-m-arrow-trending-up')
            //     ->chart([30, 35, 38, 40, 42, 44])
            //     ->color('danger')
            //     ->extraAttributes([
            //         'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
            //         'title' => 'Click to view employer list',
            //     ]),
        ];
    }
}