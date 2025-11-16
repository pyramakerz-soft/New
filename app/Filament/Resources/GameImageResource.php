<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameImageResource\Pages;
use App\Filament\Resources\GameImageResource\RelationManagers;
use App\Models\GameImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GameImageResource extends Resource
{
    protected static ?string $model = GameImage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
public static function canViewAny(): bool
    {
        return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('image'),
            ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\ImageColumn::make('image')
                ->label('Game Image') 
                ->url(fn($record) => asset('storage/' . $record->image)) 
                ->size(100) 
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
            'index' => Pages\ListGameImages::route('/'),
            'create' => Pages\CreateGameImage::route('/create'),
            'edit' => Pages\EditGameImage::route('/{record}/edit'),
        ];
    }
}
