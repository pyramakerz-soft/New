<?php

namespace App\Filament\Resources\StageResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProgramRelationManager extends RelationManager
{
    protected static string $relationship = 'program';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('name')
                    ->required()
                    ->options([
                        '2' => 'Mindbuzz',
                    ])->default('Mindbuzz'),
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'name')
                    ->required(),
                // Forms\Components\Select::make('school_id')
                //     ->relationship('school', 'name')
                //     ->required(),
                // Forms\Components\Select::make('stage_id')
                //     ->relationship('stage', 'name')
                //     ->preload()
                //     ->createOptionForm([
                //         Forms\Components\TextInput::make('name')
                //             ->required()
                //             ->maxLength(255),
                //     ])
                //     ->required(),
                Forms\Components\FileUpload::make('image')
                    ->required()
                    ->preserveFilenames(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('course.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('school.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('stage.name')->searchable()->sortable(),
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
