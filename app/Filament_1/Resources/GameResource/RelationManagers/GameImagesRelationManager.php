<?php

namespace App\Filament\Resources\GameResource\RelationManagers;

use App\Models\GameImage;
use App\Models\GameLetter;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Collection;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GameImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'gameImages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('image')
                    ->preserveFilenames(),
                Forms\Components\Select::make('game_letter_id')->label('Choose Letter')
                    ->options(static function (\Livewire\Component $livewire) {
                        // RevisionQuestionsBank::where('bank_id','=',$get('bank_id'))->get()
                        $letters = GameLetter::where('game_id', $livewire->ownerRecord->id)->get();
                        return $letters->pluck('letter', 'id')->toArray();

                    })->searchable()
                    ->preload()
                    ->required(),
                    Forms\Components\Toggle::make('correct')->required(),
                    Forms\Components\TextInput::make('word')->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('image')
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('gameLetter.letter'),
                

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
