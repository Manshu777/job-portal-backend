<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobPostingResource\Pages;
use App\Filament\Resources\JobPostingResource\RelationManagers;
use App\Models\JobPosting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;


class JobPostingResource extends Resource
{
    protected static ?string $model = JobPosting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('employer_id')
                    ->label('Employer')
                    ->relationship('employer', 'company_name')
                    ->required(),

                TextInput::make('job_title')
                    ->required()
                    ->maxLength(255),

                Select::make('job_type')
                    ->options([
                        'Full-Time' => 'Full Time',
                        'Part-Time' => 'Part Time',
                        'Freelance' => 'Freelance',
                    ])
                    ->required(),

                TextInput::make('location')
                    ->required()
                    ->maxLength(255),

                Select::make('work_location_type')
                    ->options([
                        'Work from Home' => 'Remote',
                        'Work from Office' => 'Onsite',
                        'Hybrid' => 'Hybrid',
                    ])
                    ->required(),

                TextInput::make('min_salary')
                    ->label('Minimum Salary')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->prefix('₹'),

                TextInput::make('max_salary')
                    ->label('Maximum Salary')
                    ->numeric()
                    ->nullable()
                    ->minValue(0)
                    ->prefix('₹')
                    ->rules(['gte:min_salary']),

                TextInput::make('incentive')
                    ->label('Incentive')
                    ->numeric()
                    ->nullable()
                    ->minValue(0)
                    ->prefix('₹'),

                Select::make('pay_type')
                    ->options([
                        'Salary' => 'Salary',
                        'Salary + Incentive' => 'Salary + Incentive',
                        'Hourly' => 'Hourly',
                        'Per Project' => 'Per Project',
                    ])
                    ->required(),

                Select::make('industry')
                    ->options([
                        'Technology' => 'Technology',
                        'Finance' => 'Finance',
                        'Healthcare' => 'Healthcare',
                        'Education' => 'Education',
                        'Manufacturing' => 'Manufacturing',
                        'Retail' => 'Retail',
                        'Marketing' => 'Marketing',
                    ])
                    ->required(),

                Select::make('department')
                    ->options([
                        'Engineering' => 'Engineering',
                        'Human Resources' => 'Human Resources',
                        'Marketing' => 'Marketing',
                        'Sales' => 'Sales',
                        'Finance' => 'Finance',
                        'Operations' => 'Operations',
                        'Customer Service' => 'Customer Service',
                    ])
                    ->required(),

                TextInput::make('job_role')
                    ->required()
                    ->maxLength(255),

                Toggle::make('joining_fee')
                    ->label('Joining Fee Required?')
                    ->default(false),

                Textarea::make('basic_requirements')
                    ->label('Key Responsibilities')
                    ->nullable(),

                Repeater::make('additional_requirements')
                    ->schema([
                        TextInput::make('value')
                            ->label('Skill'),
                    ])
                    ->label('Required Skills')
                    ->defaultItems(1)
                    ->addActionLabel('Add Skill'),

                Textarea::make('job_description')
                    ->label('Job Overview')
                    ->nullable(),

                Toggle::make('is_walkin_interview')
                    ->label('Is Walk-In Interview?')
                    ->default(false),

                Select::make('communication_preference')
                    ->options([
                        'call' => 'Call',
                        'email' => 'Email',
                        'whatsapp' => 'WhatsApp',
                        'phone' => 'Phone',
                        'No Preference' => 'No Preference',
                        'LinkedIn' => 'LinkedIn',
                    ])
                    ->required(),

                TextInput::make('total_experience_required')
                    ->numeric()
                    ->label('Minimum Experience Required (Years)')
                    ->nullable()
                    ->minValue(0),

                TextInput::make('total_experience_max')
                    ->numeric()
                    ->label('Maximum Experience Required (Years)')
                    ->nullable()
                    ->minValue(0)
                    ->rules(['gte:total_experience_required']),

                Repeater::make('other_job_titles')
                    ->schema([
                        TextInput::make('value')
                            ->label('Job Title'),
                    ])
                    ->label('Preferred Roles')
                    ->defaultItems(1)
                    ->addActionLabel('Add Title'),

                Repeater::make('degree_specialization')
                    ->schema([
                        TextInput::make('value')
                            ->label('Specialization'),
                    ])
                    ->label('Degree/Specialization')
                    ->defaultItems(1)
                    ->addActionLabel('Add Specialization'),

                TextInput::make('job_expire_time')
                    ->numeric()
                    ->label('Job Expiry Days')
                    ->required()
                    ->minValue(1)
                    ->default(7),

                TextInput::make('number_of_candidates_required')
                    ->numeric()
                    ->required()
                    ->label('Number of Candidates Required')
                    ->minValue(1)
                    ->default(1),

                Select::make('english_level')
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                        'Fluent' => 'Fluent',
                    ])
                    ->nullable(),

                Select::make('gender_preference')
                    ->options([
                        'No Preference' => 'No Preference',
                        'Male' => 'Male',
                        'Female' => 'Female',
                        'Other' => 'Other',
                    ])
                    ->nullable(),

                Repeater::make('perks')
                    ->schema([
                        TextInput::make('value')
                            ->label('Perk'),
                    ])
                    ->label('Perks')
                    ->defaultItems(1)
                    ->addActionLabel('Add Perk'),

                TextInput::make('interview_location')
                    ->label('Interview Location')
                    ->nullable()
                    ->maxLength(255),

                TextInput::make('contact_email')
                    ->label('Contact Email')
                    ->email()
                    ->nullable()
                    ->maxLength(255),

                TextInput::make('contact_phone')
                    ->label('Contact Phone')
                    ->nullable()
                    ->maxLength(20),

                DatePicker::make('interview_date')
                    ->label('Interview Date')
                    ->nullable(),

                TextInput::make('interview_time')
                    ->label('Interview Time')
                    ->nullable(),

                Toggle::make('not_email')
                    ->label('Disable Email Notifications?')
                    ->default(false),

                Toggle::make('viewed_number')
                    ->label('Show Candidate Count?')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                ToggleColumn::make('is_verified')
                    ->label('Verified')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger')
                    ->afterStateUpdated(function ($record, $state) {
                        // Add any logic for verification updates
                    }),
                TextColumn::make('employer.company_name')
                    ->label('Employer')
                    ->sortable(),
                TextColumn::make('job_title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('job_type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('pay_type')
                    ->sortable(),
                TextColumn::make('min_salary')
                    ->label('Min Salary')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('max_salary')
                    ->label('Max Salary')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('incentive')
                    ->label('Incentive')
                    ->money('INR')
                    ->sortable(),
                TextColumn::make('industry')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('department')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('job_role')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('job_expire_time')
                    ->label('Expiry Days')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('job_type')
                    ->options([
                        'Full-Time' => 'Full Time',
                        'Part-Time' => 'Part Time',
                        'Freelance' => 'Freelance',
                    ]),
                Tables\Filters\SelectFilter::make('work_location_type')
                    ->options([
                        'Work from Home' => 'Remote',
                        'Work from Office' => 'Onsite',
                        'Hybrid' => 'Hybrid',
                    ]),
                Tables\Filters\SelectFilter::make('industry')
                    ->options([
                        'Technology' => 'Technology',
                        'Finance' => 'Finance',
                        'Healthcare' => 'Healthcare',
                        'Education' => 'Education',
                        'Manufacturing' => 'Manufacturing',
                        'Retail' => 'Retail',
                        'Marketing' => 'Marketing',
                    ]),
                Tables\Filters\SelectFilter::make('department')
                    ->options([
                        'Engineering' => 'Engineering',
                        'Human Resources' => 'Human Resources',
                        'Marketing' => 'Marketing',
                        'Sales' => 'Sales',
                        'Finance' => 'Finance',
                        'Operations' => 'Operations',
                        'Customer Service' => 'Customer Service',
                    ]),
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
            // Add any relations if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobPostings::route('/'),
            'create' => Pages\CreateJobPosting::route('/create'),
            'edit' => Pages\EditJobPosting::route('/{record}/edit'),
        ];
    }
}