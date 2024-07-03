<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\LessonResource\Pages;
use App\Filament\Teacher\Resources\LessonResource\RelationManagers;
use App\Models\Lesson;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('warmup_id')
                    ->relationship('warmup', 'name')
                    ->preload()
                    ->searchable()->required(),
                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'name')
                    ->preload()
                    ->searchable()->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(65535)
                    ->unique()
                ,
                Forms\Components\TextInput::make('number')
                    ->required()
                    ->numeric()
                    ->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
                            $existingNumber = Lesson::where('unit_id', $get('unit_id'))->where('number', $get('number'))->where('id', '!=', $get('id'))->first();

                            if ($existingNumber) {
                                $number = ucwords($get('number'));
                                $fail("The number \"{$value}\" already exists for the chosen test.");
                            }
                        };
                    })
                ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('unit.name')
                ->numeric()
                ->sortable(),

            Tables\Columns\TextColumn::make('warmup.name')
                ->numeric()

                ->sortable(),

            Tables\Columns\TextColumn::make('number')
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
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'view' => Pages\ViewLesson::route('/{record}'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}
