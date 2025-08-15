<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateResource\Pages;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\ImportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Tables\Actions\Action;
use App\Exports\CandidateExport;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        // Your existing form code remains unchanged
        return $form
            ->schema([
                Section::make('Personal Information')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('full_name')
                                ->required()
                                ->placeholder('Enter Full Name')
                                ->columnSpan(2),
                            DatePicker::make('dob')
                                ->required()
                                ->placeholder('Select Date of Birth'),
                            Select::make('gender')
                                ->options([
                                    'male' => 'Male',
                                    'female' => 'Female',
                                    'other' => 'Other',
                                ])
                                ->required()
                                ->placeholder('Select Gender')
                                ->columnSpan(2),
                            Textarea::make('address')
                                ->columnSpanFull()
                                ->placeholder('Enter Address'),
                            TextInput::make('city')
                                ->placeholder('City'),
                            TextInput::make('state')
                                ->placeholder('State'),
                            TextInput::make('email')
                                ->email()
                                ->required()
                                ->placeholder('Email Address')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Job Preferences')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('prefers_night_shift')
                                ->label('Prefers Night Shift')
                                ->default(false),
                            Toggle::make('prefers_day_shift')
                                ->label('Prefers Day Shift')
                                ->default(true),
                            Toggle::make('work_from_home')
                                ->label('Work From Home')
                                ->default(false),
                            Toggle::make('work_from_office')
                                ->label('Work From Office')
                                ->default(true),
                            Toggle::make('field_job')
                                ->label('Field Job')
                                ->default(false),
                            TextInput::make('employment_type')
                                ->placeholder('Preferred Employment Type (e.g., Full-time, Part-time)'),
                        ]),
                    ]),

                Section::make('Resume & Skills')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('resume')
                                ->placeholder('Upload Resume File (e.g., URL or path)')
                                ->columnSpanFull(),
                            TagsInput::make('skills')
                                ->placeholder('Enter Skills (comma separated)')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Account Information')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('active_user')
                                ->label('Active User')
                                ->default(true),
                            Toggle::make('doneprofile')
                                ->label('Profile Completed')
                                ->default(false),
                            TextInput::make('password')
                                ->password()
                                ->placeholder('Enter Password')
                                ->columnSpanFull(),
                            TextInput::make('otp')
                                ->placeholder('OTP (if applicable)'),
                            DatePicker::make('otp_expires_at')
                                ->placeholder('OTP Expiry Date'),
                            DatePicker::make('last_login')
                                ->placeholder('Last Login Date')
                                ->disabled(),
                            TextInput::make('total_jobs_applied')
                                ->numeric()
                                ->placeholder('Total Jobs Applied')
                                ->disabled(),
                            TextInput::make('total_job_views')
                                ->numeric()
                                ->placeholder('Total Job Views')
                                ->disabled(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(
                [
    TextColumn::make('full_name')
        ->searchable()
        ->sortable()
        ->icon('heroicon-o-user'),
    TextColumn::make('email')
        ->searchable(),
    BadgeColumn::make('gender')
        ->colors([
            'primary' => 'male',
            'success' => 'female',
            'warning' => 'other',
        ]),
    TextColumn::make('city')
        ->searchable(),
    TextColumn::make('state')
        ->searchable(),
    TextColumn::make('dob')
        ->label('Date of Birth')
        ->date()
        ->sortable(),
    TextColumn::make('education_level')
        ->searchable()
        ->label('Degree'),
    TextColumn::make('specialization')
        ->searchable()
        ->label('Specialization'),
    BadgeColumn::make('experience_level')
        ->label('Experience')
        ->colors([
            'success' => 'Fresher',
            'primary' => 'Mid-level',
            'warning' => 'Senior',
            'secondary' => fn ($state) => !in_array($state, ['Fresher', 'Mid-level', 'Senior']),
        ]),
    TextColumn::make('highest_education')
        ->label('Highest Education')
        ->searchable(),
    TextColumn::make('complete_years')
        ->label('Graduation Year')
        ->sortable(),
    TextColumn::make('number')
        ->label('Phone Number')
        ->searchable(),
    IconColumn::make('prefers_night_shift')
        ->boolean()
        ->label('Night Shift')
        ->trueIcon('heroicon-o-moon')
        ->falseIcon('heroicon-o-x-circle'),
    IconColumn::make('prefers_day_shift')
        ->boolean()
        ->label('Day Shift')
        ->trueIcon('heroicon-o-sun')
        ->falseIcon('heroicon-o-x-circle'),
    IconColumn::make('work_from_home')
        ->boolean()
        ->label('WFH')
        ->trueIcon('heroicon-o-home')
        ->falseIcon('heroicon-o-x-circle'),
    IconColumn::make('work_from_office')
        ->boolean()
        ->label('Office')
        ->trueIcon('heroicon-o-building-office')
        ->falseIcon('heroicon-o-x-circle'),
    IconColumn::make('field_job')
        ->boolean()
        ->label('Field Job')
        ->trueIcon('heroicon-o-briefcase')
        ->falseIcon('heroicon-o-x-circle'),
    TextColumn::make('employment_type')
        ->label('Employment Type'),
    TextColumn::make('skills')
        ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)
        ->limit(25),
    TextColumn::make('total_jobs_applied')
        ->label('Applied')
        ->sortable(),
    TextColumn::make('total_job_views')
        ->label('Views')
        ->sortable(),
    IconColumn::make('active_user')
        ->boolean()
        ->label('Active')
        ->trueIcon('heroicon-o-check-circle')
        ->falseIcon('heroicon-o-x-circle'),
    IconColumn::make('doneprofile')
        ->boolean()
        ->label('Profile Done')
        ->trueIcon('heroicon-o-check-badge')
        ->falseIcon('heroicon-o-x-circle'),
    TextColumn::make('last_login')
        ->dateTime()
        ->sortable(),
    TextColumn::make('created_at')
        ->label('Profile Created')
        ->dateTime()
        ->sortable(),
]
            )
            ->headerActions([
                Action::make('import')
    ->label('Import Candidates')
    ->form([
        Forms\Components\FileUpload::make('file')
            ->label('Excel File')
            ->disk('local')
            ->directory('imports')
            ->acceptedFileTypes(['.xlsx', '.xls', '.csv'])
            ->required(),
    ])
    ->action(function (array $data) {
        Excel::import(new CandidatesImport, $data['file']);
        Filament\Notifications\Notification::make()
            ->title('Import Successful')
            ->success()
            ->send();
    }),
     
                ExportAction::make('export')
                    ->label('Export Candidates')
                    ->exports([
                        ExcelExport::make()->fromTable(),
                    ]),
               
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ]),
                Tables\Filters\TernaryFilter::make('active_user')
                    ->label('Active User'),
                Tables\Filters\TernaryFilter::make('doneprofile')
                    ->label('Profile Completed'),
            ])
            ->actions([
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportAction::make('export_selected')
                        ->label('Export Selected Candidates')
                        ->exports([
                            ExcelExport::make()->fromTable(),
                        ]),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'edit' => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }
}