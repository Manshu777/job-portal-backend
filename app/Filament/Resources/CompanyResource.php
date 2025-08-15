<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Companies';

    protected static ?string $modelLabel = 'Company';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('employer_id')
                    ->label('Employer')
                    ->relationship('employer', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->label('Company Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company_location')
                    ->label('Location')
                    ->maxLength(255),
                Forms\Components\TextInput::make('contact_person')
                    ->label('Contact Person')
                    ->maxLength(255),
                Forms\Components\TextInput::make('contact_phone')
                    ->label('Contact Phone')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\FileUpload::make('gst_certificate')
                    ->label('GST Certificate')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(2048) // 2MB
                    ->disk('public')
                    ->directory('documents')
                    ->downloadable()
                    ->previewable(false),
                Forms\Components\FileUpload::make('other_certificate')
                    ->label('Other Certificate')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(2048) // 2MB
                    ->disk('public')
                    ->directory('documents')
                    ->downloadable()
                    ->previewable(false),
                Forms\Components\Toggle::make('is_approved')
                    ->label('Approved')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ToggleColumn::make('is_approved')
                    ->label('Verified')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger')
                    ->afterStateUpdated(function ($record, $state) {
                        // Optional: Add logic, e.g., notify employer
                    }),
                Tables\Columns\TextColumn::make('employer.name')
                    ->label('Employer Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Company Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('company_location')
                    ->label('Location')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->label('Contact Person')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('Contact Phone')
                    ->sortable()
                    ->searchable(),
                    Tables\Columns\TextColumn::make('gst_certificate')
                ->label('GST Certificate')
                ->formatStateUsing(fn ($state) => $state ? 'Available' : 'N/A'),
            Tables\Columns\TextColumn::make('other_certificate')
                ->label('Other Certificate')
                ->formatStateUsing(fn ($state) => $state ? 'Available' : 'N/A'),
    
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_approved')
                    ->label('Approval Status')
                    ->options([
                        '1' => 'Approved',
                        '0' => 'Pending',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_gst')
                ->label('View GST Doc')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->url(fn ($record) => $record->gst_certificate ? Storage::disk('public')->url($record->gst_certificate) : null)
                ->openUrlInNewTab()
                ->visible(fn ($record) => $record->gst_certificate && Storage::disk('public')->exists($record->gst_certificate)),
            Tables\Actions\Action::make('download_gst')
                ->label('Download GST')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn ($record) => $record->gst_certificate ? Storage::disk('public')->url($record->gst_certificate) : null)
                ->openUrlInNewTab()
                ->extraAttributes(['download' => true])
                ->visible(fn ($record) => $record->gst_certificate && Storage::disk('public')->exists($record->gst_certificate)),
            Tables\Actions\Action::make('view_other')
                ->label('View Other Doc')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->url(fn ($record) => $record->other_certificate ? Storage::disk('public')->url($record->other_certificate) : null)
                ->openUrlInNewTab()
                ->visible(fn ($record) => $record->other_certificate && Storage::disk('public')->exists($record->other_certificate)),
            Tables\Actions\Action::make('download_other')
                ->label('Download Other')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn ($record) => $record->other_certificate ? Storage::disk('public')->url($record->other_certificate) : null)
                ->openUrlInNewTab()
                ->extraAttributes(['download' => true])
                ->visible(fn ($record) => $record->other_certificate && Storage::disk('public')->exists($record->other_certificate)),
            
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}