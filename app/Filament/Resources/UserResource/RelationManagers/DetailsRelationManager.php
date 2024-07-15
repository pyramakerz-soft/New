<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\School;
use Filament\Resources\RelationManagers\HasManyRelationManager;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('stage_id')
                    ->relationship('stage', 'name')
                    ->preload()
                    ->required(),
                    Forms\Components\Hidden::make('school_id')
                    ->default(fn ($livewire) => $livewire->ownerRecord->school_id)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('details')
            ->columns([
                // Tables\Columns\TextColumn::make('details'),
                Tables\Columns\TextColumn::make('stage.name')
                    ->numeric()
                    ->sortable(),
                    
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
