<?php

namespace App\Filament\Resources\GameResource\RelationManagers;

use App\Models\GameLetter;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GameLettersRelationManager extends RelationManager
{
    protected static string $relationship = 'gameLetters';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('letter')
                    // ->rule(static function (Forms\Get $get, Forms\Components\Component $component, \Livewire\Component $livewire): Closure {
                    //     return static function (string $attribute, $value, Closure $fail) use ($livewire, $get, $component) {

                    //         $repeat_check = GameLetter::join('games', 'game_letters.game_id', 'games.id')->where('game_letters.game_id', $livewire->ownerRecord->id)->where('game_letters.letter', $get('letter'))->count();

                    //         $nums = GameLetter::join('games', 'game_letters.game_id', 'games.id')->where('game_letters.game_id', $livewire->ownerRecord->id)->count();
                    //         // dd($nums);

                    //         if(isset($livewire->cachedMountedTableActionRecord->letter) && strtoupper($get('letter')) != strtoupper($livewire->cachedMountedTableActionRecord->letter)){
                    //         if ($repeat_check >= 1) {
                    //             $number = ucwords($get('number'));
                    //             $fail("You already added the required number of times for letter [ " . $get('letter') . " ]");
                    //         }
                    //     }
                    //     else{
                    //         if ($repeat_check >= 1) {
                    //             $number = ucwords($get('number'));
                    //             $fail("You already added the required number of times for letter [ " . $get('letter') . " ]");
                    //         }
                    //         if ($nums >= 16) {
                    //             $number = ucwords($get('number'));
                    //             $fail("Reached the maximum number of letters for this game.");
                    //         }
                    //     }
                    //         // if ($repeat_check) {
                    //         //     $number = ucwords($get('number'));
                    //         //     $fail("The number \"{$value}\" already exists for the chosen test.");
                    //         // }
                    //     };
                    // })
                    ->required()
                    ->maxLength(100),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('letter')
            ->columns([
                Tables\Columns\TextColumn::make('letter'),
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
