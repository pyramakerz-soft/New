<?php

namespace App\Filament\Resources\GameResource\RelationManagers;

use App\Models\Test;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'gameQuestions';
    protected static ?string $modelLabel = 'Assignment';
    protected static ?string $title = 'Assignments';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('test_id')->label('Test')
                    ->relationship('test', 'name')
                    ->required()
                    ->options(Test::all()->pluck('name', 'id'))
                    ->searchable()
                    ->reactive(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Assignments')
            ->columns([
                Tables\Columns\TextColumn::make('test.name')->label(''),
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
