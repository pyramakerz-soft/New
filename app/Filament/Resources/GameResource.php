<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameResource\Pages;
use App\Filament\Resources\GameResource\RelationManagers;
use App\Filament\Resources\GameResource\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\GameResource\RelationManagers\GameImagesRelationManager;
use App\Filament\Resources\GameResource\RelationManagers\GameLettersRelationManager;
use App\Models\Game;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use DB;
class GameResource extends Resource
{
    protected static ?string $model = Game::class;
    protected static ?string $navigationGroup = 'Games';
    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';
    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
              
                    
                     Forms\Components\Toggle::make('is_active')
                    ->required()
                    ,
                     Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(150),
                  
                   
                    
                Forms\Components\Select::make('lesson_id')
                    ->required()
                    ->relationship('lesson', 'name')
                    ->preload()
                    ->searchable(),
                    
                    
                Forms\Components\Select::make('game_type_id')
                    ->required()
                    ->relationship('gameTypes', 'name')
                    ->preload()
                    ->searchable(),
                    
                    
                    
                // Forms\Components\Select::make('prev_game_id')
                //     ->required()
                //     ->preload()
                //     ->searchable(),
                    
                    
                   

                    // ->preload()
                    // ->searchable()
                    // ->label("Previous Game")
                    // ->required(),
                    
                    Forms\Components\Select::make('prev_game_id')
    ->options(function (Builder $query, Forms\Get $get) {
        return Game::join('game_types', 'games.game_type_id', 'game_types.id')
            ->join('lessons', 'games.lesson_id', 'lessons.id')
            ->join('game_letters', 'games.id', 'game_letters.game_id')
            ->select(DB::raw("CONCAT('ID : ',games.id,' / Type : ', game_types.name, ' / Lesson : ', lessons.name, ' / Main Letter : ', games.main_letter, ' / Game Letters : ', GROUP_CONCAT(game_letters.letter ORDER BY game_letters.letter SEPARATOR ', ')) AS name"), 'games.id as id')
            ->groupBy('games.id', 'game_types.name', 'lessons.name', 'games.main_letter')
            ->pluck('name', 'id')
            ->toArray();
    })->preload()
     ->searchable()
                    ->label("Previous Game"),
                    
                    
                    Forms\Components\Select::make('next_game_id')
    ->options(function (Builder $query, Forms\Get $get) {
        return Game::join('game_types', 'games.game_type_id', 'game_types.id')
            ->join('lessons', 'games.lesson_id', 'lessons.id')
            ->join('game_letters', 'games.id', 'game_letters.game_id')
            ->select(DB::raw("CONCAT('ID : ',games.id,' / Type : ', game_types.name, ' / Lesson : ', lessons.name, ' / Main Letter : ', games.main_letter, ' / Game Letters : ', GROUP_CONCAT(game_letters.letter ORDER BY game_letters.letter SEPARATOR ', ')) AS name"), 'games.id as id')
            ->groupBy('games.id', 'game_types.name', 'lessons.name', 'games.main_letter')
            ->pluck('name', 'id')
            ->toArray();
    })->preload()
     ->searchable()
                    ->label("Next Game"),
                    
                    
                    
                // Forms\Components\Select::make('next_game_id')
                
                //     ->relationship('nextGame','id')
                //     ->required()
                //     ->preload()
                //     ->searchable(),
                    
                    
                    
                Forms\Components\Select::make('audio_flag')
                    ->required()
                    ->searchable()
                    ->options([
                        0 => 'OFF',
                        1 => 'ON',

                    ]),
                     Forms\Components\TextInput::make('inst')->label('Instruction')->required(),
                     
                Forms\Components\TextInput::make('num_of_letters')->maxLength(1)
                    // ->afterStateUpdated(function (?string $state, Forms\Contracts\HasForms $livewire, Forms\Components\TextInput $component) {
                    //     $livewire->validate();
                    // })
                    ->reactive()
                    ->required()
                    ->numeric()
                    ->default(16)->hidden(),
                Forms\Components\TextInput::make('num_of_letter_repeat')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('main_letter')->required(),
                Forms\Components\TextInput::make('num_of_trials')
                    ->required()
                    ->numeric()
                    ->default(50),
                Forms\Components\TextInput::make('correct_ans')->label('Correct Answer'),
                Forms\Components\TextInput::make('sentence')->label('Sentence'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_active')
    ->boolean(),
                Tables\Columns\TextColumn::make('id')
                    ->numeric()
                    ->sortable(),
                                    Tables\Columns\TextColumn::make('name'),

                Tables\Columns\TextColumn::make('lesson.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gameTypes.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('main_letter')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gameTypes.name')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('num_of_letters')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('num_of_letter_repeat')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('inst')->label('Instruction')
                    // ->numeric()
                    ->sortable()
                    ->searchable(),
                    Tables\Columns\IconColumn::make('audio_flag')
    ->boolean(),
                    Tables\Columns\IconColumn::make('isEdited')
    ->boolean(),
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
                //
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

    public static function getRelations(): array
    {
        return [
            GameLettersRelationManager::class,
            GameImagesRelationManager::class,
            AssignmentsRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGames::route('/'),
            'create' => Pages\CreateGame::route('/create'),
            'edit' => Pages\EditGame::route('/{record}/edit'),
        ];
    }
}
