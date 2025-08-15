<?php

namespace App\Filament\Resources\JobPostingApplicationResource\Pages;

use App\Filament\Resources\JobPostingApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobPostingApplications extends ListRecords
{
    protected static string $resource = JobPostingApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
