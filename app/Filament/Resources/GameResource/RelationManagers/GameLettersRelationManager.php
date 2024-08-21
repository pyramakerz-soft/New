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
                    
                    ->required()
                    ->maxLength(100),
                Forms\Components\Toggle::make('main_question')
                    
                    ->required(),
                    Forms\Components\Toggle::make('type_of_tool'),
                Forms\Components\Toggle::make('hide_rings'),
                Forms\Components\TextInput::make('sec_letter')
                    
                    ->maxLength(100),
                //     Forms\Components\FileUpload::make('image')
                //     ->nullable()

                //     ->preserveFilenames()

                //     ->disk('public')
                // ,
Forms\Components\FileUpload::make('image')
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrated(true)
                    ->preserveFilenames()
                    ->rules(['mimes:jpg,jpeg,png', 'max:10000'])

                ,
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('letter')
            ->columns([
                Tables\Columns\TextColumn::make('letter'),
                Tables\Columns\TextColumn::make('sec_letter'),
                Tables\Columns\ToggleColumn::make('main_question'),
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
