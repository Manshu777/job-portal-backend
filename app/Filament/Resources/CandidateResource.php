<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateResource\Pages;
use App\Imports\CandidatesImport;
use App\Models\Candidate;
use App\Exports\CandidateTemplateExport;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Illuminate\Database\Eloquent\Builder;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
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
                                    'male'   => 'Male',
                                    'female' => 'Female',
                                    'other'  => 'Other',
                                ])
                                ->required()
                                ->placeholder('Select Gender'),
                            TextInput::make('email')
                                ->email()
                                ->required()
                                ->placeholder('Email Address')
                                ->columnSpanFull(),
                            Textarea::make('address')
                                ->columnSpanFull()
                                ->placeholder('Enter Address'),
                            TextInput::make('city')
                                ->placeholder('City'),
                            TextInput::make('state')
                                ->placeholder('State'),
                            TextInput::make('number')
                                ->placeholder('Phone Number'),
                            TextInput::make('profile_pic')
                                ->placeholder('Profile Picture URL'),
                        ]),
                    ]),

                Section::make('Education Details')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('degree')
                                ->placeholder('Degree'),
                            TextInput::make('specialization')
                                ->placeholder('Specialization'),
                            TextInput::make('college_name')
                                ->placeholder('College Name'),
                            TextInput::make('education_level')
                                ->placeholder('Education Level'),
                            TextInput::make('highest_education')
                                ->placeholder('Highest Education'),
                            TextInput::make('school_medium')
                                ->placeholder('School Medium'),
                            TextInput::make('passing_marks')
                                ->numeric()
                                ->placeholder('Passing Marks'),
                            Toggle::make('currently_pursuing')
                                ->label('Currently Pursuing')
                                ->default(false),
                            Toggle::make('pursuing')
                                ->label('Pursuing')
                                ->default(false),
                            TextInput::make('complete_years')
                                ->numeric()
                                ->placeholder('Completion Years'),
                            TextInput::make('complete_month')
                                ->numeric()
                                ->placeholder('Completion Month'),
                            TextInput::make('english_level')
                                ->placeholder('English Proficiency Level'),
                        ]),
                    ]),

                Section::make('Job Preferences')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('job_title')
                                ->placeholder('Job Title'),
                            TextInput::make('company_name')
                                ->placeholder('Company Name'),
                            TextInput::make('experience_type')
                                ->placeholder('Experience Type'),
                            TextInput::make('employment_type')
                                ->placeholder('Employment Type'),
                            TextInput::make('preferred_language')
                                ->placeholder('Preferred Language'),
                            TagsInput::make('preferred_job_titles')
                                ->placeholder('Preferred Job Titles')
                                ->columnSpanFull(),
                            TagsInput::make('preferred_locations')
                                ->placeholder('Preferred Locations')
                                ->columnSpanFull(),
                            TagsInput::make('preferred_languages')
                                ->placeholder('Preferred Languages')
                                ->columnSpanFull(),
                            TextInput::make('current_salary')
                                ->numeric()
                                ->placeholder('Current Salary'),
                            TextInput::make('notice_period')
                                ->placeholder('Notice Period'),
                            Toggle::make('is_working')
                                ->label('Is Working')
                                ->default(false),
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
                        ]),
                    ]),

                Section::make('Experience')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('experience_years')
                                ->numeric()
                                ->placeholder('Experience Years'),
                            TextInput::make('experience_months')
                                ->numeric()
                                ->placeholder('Experience Months'),
                            TextInput::make('experience_level')
                                ->placeholder('Experience Level'),
                            DatePicker::make('start_date')
                                ->placeholder('Job Start Date'),
                            DatePicker::make('end_date')
                                ->placeholder('Job End Date'),
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
                        Grid::make(3)->schema([
                            TextInput::make('password')
                                ->password()
                                ->placeholder('Enter Password')
                                ->columnSpanFull(),
                            TextInput::make('otp')
                                ->placeholder('OTP (if applicable)'),
                            DatePicker::make('otp_expires_at')
                                ->placeholder('OTP Expiry Date'),
                            TextInput::make('token')
                                ->placeholder('Token'),
                            Toggle::make('active_user')
                                ->label('Active User')
                                ->default(true),
                            Toggle::make('doneprofile')
                                ->label('Profile Completed')
                                ->default(false),
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
                            DatePicker::make('created_at')
                                ->placeholder('Profile Created')
                                ->disabled(),
                            DatePicker::make('updated_at')
                                ->placeholder('Profile Updated')
                                ->disabled(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $filters = request()->only([
                    'degree',
                    'specialization',
                    'city',
                    'min_experience',
                    'max_experience',
                    'min_salary',
                    'max_salary',
                    'gender',
                    'employment_type',
                    'shift_preference',
                ]);

                return $query->filter($filters);
            })
            ->columns([
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
                TextColumn::make('number')
                    ->label('Phone Number')
                    ->searchable(),
                TextColumn::make('address')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('state')
                    ->searchable(),
                TextColumn::make('dob')
                    ->label('Date of Birth')
                    ->date()
                    ->sortable(),
                TextColumn::make('degree')
                    ->searchable(),
                TextColumn::make('specialization')
                    ->searchable(),
                TextColumn::make('college_name')
                    ->searchable(),
                TextColumn::make('education_level')
                    ->searchable(),
                TextColumn::make('highest_education')
                    ->searchable(),
                TextColumn::make('school_medium')
                    ->searchable(),
                TextColumn::make('passing_marks')
                    ->searchable(),
                IconColumn::make('currently_pursuing')
                    ->boolean()
                    ->label('Currently Pursuing'),
                IconColumn::make('pursuing')
                    ->boolean()
                    ->label('Pursuing'),
                TextColumn::make('complete_years')
                    ->label('Graduation Year')
                    ->sortable(),
                TextColumn::make('complete_month')
                    ->label('Graduation Month'),
                TextColumn::make('english_level')
                    ->label('English Level'),
                TextColumn::make('job_title')
                    ->searchable(),
                TextColumn::make('company_name')
                    ->searchable(),
                TextColumn::make('experience_type')
                    ->searchable(),
                TextColumn::make('employment_type')
                    ->searchable(),
                TextColumn::make('preferred_language')
                    ->searchable(),
                TextColumn::make('preferred_job_titles')
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->limit(25),
                TextColumn::make('preferred_locations')
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->limit(25),
                TextColumn::make('preferred_languages')
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->limit(25),
                TextColumn::make('current_salary')
                    ->sortable(),
                TextColumn::make('notice_period')
                    ->searchable(),
                IconColumn::make('is_working')
                    ->boolean()
                    ->label('Is Working'),
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
                TextColumn::make('experience_years')
                    ->sortable(),
                TextColumn::make('experience_months')
                    ->sortable(),
                TextColumn::make('experience_level')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('resume')
                    ->limit(25),
                TextColumn::make('skills')
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->limit(25),
                TextColumn::make('profile_pic')
                    ->limit(25),
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
                TextColumn::make('total_jobs_applied')
                    ->label('Applied')
                    ->sortable(),
                TextColumn::make('total_job_views')
                    ->label('Views')
                    ->sortable(),
                TextColumn::make('last_login')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Profile Created')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Profile Updated')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('otp')
                    ->label('OTP'),
                TextColumn::make('otp_expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('token')
                    ->limit(25),
            ])
            ->headerActions([
                Action::make('import')
                    ->label('Import Candidates')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('Excel File')
                            ->disk('local')
                            ->directory('imports')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $filePath = storage_path('app/imports/' . $data['file']);
                        Excel::import(new CandidatesImport, $filePath);
                        \Filament\Notifications\Notification::make()
                            ->title('Import Successful')
                            ->success()
                            ->send();
                    }),

                Action::make('export_template')
                    ->label('Download Excel Template')
                    ->action(function () {
                        $query = Candidate::query();
                        $export = new CandidateTemplateExport($query);
                        return Excel::download($export, 'candidates_template.xlsx');
                    })
                    ->color('success'),

                ExportAction::make('export')
                    ->label('Export Candidates')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->askForWriterType()
                            ->askForFilename(),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('degree')
                    ->options(fn () => Candidate::query()
                        ->whereNotNull('degree')
                        ->pluck('degree', 'degree')
                        ->unique()
                        ->toArray()),

                Tables\Filters\SelectFilter::make('specialization')
                    ->options(fn () => Candidate::query()
                        ->whereNotNull('specialization')
                        ->pluck('specialization', 'specialization')
                        ->unique()
                        ->toArray()),

                Tables\Filters\SelectFilter::make('city')
                    ->options(fn () => Candidate::query()
                        ->whereNotNull('city')
                        ->pluck('city', 'city')
                        ->unique()
                        ->toArray()),

                Tables\Filters\Filter::make('experience')
                    ->form([
                        Forms\Components\TextInput::make('min_experience')->numeric(),
                        Forms\Components\TextInput::make('max_experience')->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min_experience'], fn ($q, $val) =>
                                $q->whereRaw('(experience_years * 12 + experience_months) >= ?', [(int)$val * 12])
                            )
                            ->when($data['max_experience'], fn ($q, $val) =>
                                $q->whereRaw('(experience_years * 12 + experience_months) <= ?', [(int)$val * 12])
                            );
                    }),

                Tables\Filters\Filter::make('salary')
                    ->form([
                        Forms\Components\TextInput::make('min_salary')->numeric(),
                        Forms\Components\TextInput::make('max_salary')->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min_salary'], fn ($q, $val) => $q->where('current_salary', '>=', $val))
                            ->when($data['max_salary'], fn ($q, $val) => $q->where('current_salary', '<=', $val));
                    }),
            ])
            ->paginated([10, 25, 50, 100])
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
            'index'  => Pages\ListCandidates::route('/'),
            'create' => Pages\CreateCandidate::route('/create'),
            'edit'   => Pages\EditCandidate::route('/{record}/edit'),
        ];
    }
}