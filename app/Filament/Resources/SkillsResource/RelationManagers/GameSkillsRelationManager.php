<?php

namespace App\Filament\Resources\SkillsResource\RelationManagers;

use Filament\Forms;
use App\Models\Skills;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GameSkillsRelationManager extends RelationManager
{
    protected static string $relationship = 'gameSkills';

    public function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Select::make('lesson_id')
                ->label('Lesson')
                ->options(function () {
                    return \App\Models\Lesson::with('unit.program.school')
                        ->get()
                        ->mapWithKeys(function ($lesson) {
                            $schoolName = $lesson->unit->program->school->name ?? 'No School';
                            return [$lesson->id => $lesson->name . ' (' . $schoolName . ')'];
                        });
                })
                ->required()
                ->preload()
                ->searchable()
                ->reactive(),

            Forms\Components\Select::make('game_type_id')
                ->label('Game Type')
                ->relationship('game_type', 'name')
                ->required()
                ->preload()
                ->searchable()
                ->reactive(),
        ]);
}


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('skill')
            ->columns([
                Tables\Columns\TextColumn::make('skill.skill')->label('Skill'),
                Tables\Columns\TextColumn::make('lesson.name')->label('Lesson'),
                Tables\Columns\TextColumn::make('game_type.name')->label('Game Type'),
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
