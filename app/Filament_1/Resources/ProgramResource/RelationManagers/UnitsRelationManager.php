<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use App\Models\Unit;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('number')
                    ->required()
                    ->maxLength(2)
                    ->minValue(1)
                    ->integer()
                    ->numeric()

                    ->rule(static function (Forms\Set $set, Forms\Get $get, Forms\Components\Component $component, \Livewire\Component $livewire): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($livewire, $get, $component) {
                            $existingNumber = Unit::where('program_id', $livewire->ownerRecord->id)->where('number', $get('number'))->where('id', '!=', $get('id'))->first();

                            if ($existingNumber) {
                                $number = ucwords($get('number'));
                                $fail("The number \"{$value}\" already exists for the chosen test.");
                            }
                        };
                    })
                    ->required(), // ignore current record when editing,

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('number'),
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
