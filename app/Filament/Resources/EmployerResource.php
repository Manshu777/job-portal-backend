<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployerResource\Pages;
use App\Models\Employer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;

class EmployerResource extends Resource
{
    protected static ?string $model = Employer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->disabled(),

                TextInput::make('contact_email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->disabled(),

                // TextInput::make('company_name')
                //     ->required()
                //     ->maxLength(255),

                // TextInput::make('company_location')
                //     ->required()
                //     ->maxLength(255),

                // TextInput::make('contact_person')
                //     ->required()
                //     ->maxLength(255),

                // TextInput::make('contact_phone')
                //     ->tel()
                //     ->required()
                //     ->maxLength(20),


                Toggle::make('is_blocked')
                    ->label('Blocked')
                    ->onColor('danger')
                    ->offColor('success')
                    ->reactive()
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state && !$record->remark) {
                            $record->remark = '';
                        }
                    }),

                Textarea::make('remark')
                    ->label('Reason for Block')
                    ->maxLength(500)
                    ->nullable()
                    ->visible(fn($get) => $get('is_blocked') === true)
                    ->required(fn($get) => $get('is_blocked') === true)
                    ->reactive(),


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
                    ->onColor('danger')
                    ->offColor('success'),

                // ToggleColumn::make('is_blocked')
                //     ->label('Blocked')
                //     ->sortable()
                //     ->onColor('danger')
                //     ->offColor('success'),
                
                TextColumn::make('name')->label('Full Name')->sortable()->searchable(),
                TextColumn::make('contact_email')->label('Email')->sortable()->searchable(),
                TextColumn::make('companies.contact_phone')->label('Phone')->sortable()->searchable()->limit(19),
                TextColumn::make('companies.name')->label('Company Name')->sortable()->searchable(),
                TextColumn::make('companies.company_location')->label('Company Location')->sortable()->searchable()->limit(50),
                TextColumn::make('remark')->label('Block Reason')->sortable()->searchable(), // Add remark to the table
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_verified')->label('Verified'),
                Tables\Filters\TernaryFilter::make('is_blocked')->label('Blocked'),
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
            // Add relations if any
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployers::route('/'),
            'create' => Pages\CreateEmployer::route('/create'),
            'edit' => Pages\EditEmployer::route('/{record}/edit'),
        ];
    }
}
