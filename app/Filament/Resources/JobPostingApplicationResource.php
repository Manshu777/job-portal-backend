<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobPostingApplicationResource\Pages;
use App\Filament\Resources\JobPostingApplicationResource\RelationManagers;
use App\Models\JobPostingApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
class JobPostingApplicationResource extends Resource
{
    protected static ?string $model = JobPostingApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('candidate.full_name')
                ->label('Candidate Name')
                ->searchable()
                ->sortable(),

            TextColumn::make('jobPosting.job_title')
                ->label('Job Title')
                ->searchable()
                ->sortable(),

            TextColumn::make('jobPosting.employer.company_name')
                ->label('Company Name')
                ->searchable(),

            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'applied' => 'gray',
                    'interview' => 'yellow',
                    'rejected' => 'red',
                    'hired' => 'green',
                }),
            
            TextColumn::make('created_at')
                ->label('Applied On')
                ->dateTime('d M Y'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobPostingApplications::route('/'),
            'create' => Pages\CreateJobPostingApplication::route('/create'),
            'edit' => Pages\EditJobPostingApplication::route('/{record}/edit'),
        ];
    }
}
