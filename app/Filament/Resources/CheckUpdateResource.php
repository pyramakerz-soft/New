<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckUpdateResource\Pages;
use App\Filament\Resources\CheckUpdateResource\RelationManagers;
use App\Models\CheckUpdate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CheckUpdateResource extends Resource
{
    protected static ?string $model = CheckUpdate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('teach_or_stud')->label("Teacher/Student")
                    ->disabled()
                ,
                Forms\Components\TextInput::make('version')
                    ->required()
                ,
                Forms\Components\TextInput::make('version_url')
                    ->required()
                ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teach_or_stud')->label("Teacher/Student")
                    ->sortable(),
                Tables\Columns\TextColumn::make('version')
                    ->sortable(),
                Tables\Columns\TextColumn::make('version_url')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListCheckUpdates::route('/'),
            'create' => Pages\CreateCheckUpdate::route('/create'),
            'edit' => Pages\EditCheckUpdate::route('/{record}/edit'),
        ];
    }
}
