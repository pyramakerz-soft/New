<?php

namespace App\Filament\Resources\QuestionBankResource\RelationManagers;

use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';
    protected static  ?string $title = 'View Questions';
    protected static  ?string $heading = 'View Questions';
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->columns([
                Tables\Columns\TextColumn::make('question.question')
                ->numeric()
                ->sortable(),
                    Tables\Columns\TextColumn::make("question.qtype")->label('Question Type'),
                    Tables\Columns\TextColumn::make("question.type")->label('
                    Solve by'),
                    Tables\Columns\TextColumn::make("question.sec_type")->label('Object Type'),
                    Tables\Columns\TextColumn::make("question.control.hint")->label('Hint'),
                
                Tables\Columns\TextColumn::make('question.number')->label('Number')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('question.time')->label('Time')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('question.difficulty')->label('Difficulty')
                    ->numeric()
                    ->sortable(),
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
                 
            ])
            ->headerActions([
                
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
