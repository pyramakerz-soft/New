<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarmupResource\Pages;
use App\Filament\Resources\WarmupResource\RelationManagers;
use App\Filament\Resources\WarmupResource\RelationManagers\TestRelationManager;
use App\Filament\Resources\WarmupResource\RelationManagers\WarmupTestRelationManager;
use App\Models\Warmup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarmupResource extends Resource
{
    protected static ?string $model = Warmup::class;

    protected static ?string $navigationGroup = 'Lessons';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\FileUpload::make('doc')
                    ->required()
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context == 'create')
                    ->dehydrated(true)
                    ->preserveFilenames()
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->maxSize(5000)

                ,
                Forms\Components\FileUpload::make('video')
                    ->preserveFilenames()
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context == 'create')
                    ->dehydrated(true)
                    ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/mkv', 'video/webm'])
                    ->maxSize(500000)

                ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                ,
                Tables\Columns\IconColumn::make('doc')
                    ->label('documentation')
                    ->boolean()
                    ->toggleable()
                    ->default(1)
                    ->action(fn(Warmup $record) => file_exists(public_path('/storage') . '/' . $record['doc']) ? response()->download(public_path('/storage') . '/' . $record['doc']) : null)
                    ->icon(fn(Warmup $record) =>
                        (file_exists(public_path('/storage') . '/' . $record['doc'])) ? 'heroicon-o-document-text' : 'heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\RestoreAction::make(),
                // Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    // Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // WarmupTestRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarmups::route('/'),
            'create' => Pages\CreateWarmup::route('/create'),
            'edit' => Pages\EditWarmup::route('/{record}/edit'),
        ];
    }
}
