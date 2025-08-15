<?php

namespace App\Filament\Resources\JobPostingApplicationResource\Pages;

use App\Filament\Resources\JobPostingApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJobPostingApplication extends EditRecord
{
    protected static string $resource = JobPostingApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
