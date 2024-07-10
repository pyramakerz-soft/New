<?php

namespace App\Filament\Resources\WarmupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarmupTestRelationManager extends RelationManager
{
    protected static string $relationship = 'warmup_test';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('warmup_id')->default(fn(\Livewire\Component $livewire): int => $livewire->ownerRecord->id),
                Forms\Components\Select::make('test_id')
                ->relationship('test','name')
                ->preload()
                ->searchable()
            ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('test.name'),
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
