<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentRelationManager extends RelationManager
{
    protected static string $relationship = 'group_students';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('student_id')->label('Student')
                    ->relationship('student', 'name')
                    ->required()
                    ->options(User::where('role', 2)->pluck('name', 'id'))
                    ->searchable()
                    ->reactive(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('student.name'),
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
