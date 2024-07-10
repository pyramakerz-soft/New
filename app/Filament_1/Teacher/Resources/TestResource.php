<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\TestResource\Pages;
use App\Filament\Teacher\Resources\TestResource\RelationManagers;
use App\Models\Lesson;
use App\Models\Test;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TestResource extends Resource
{
    protected static ?string $model = Test::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        '0' => 'Test',
                        '1' => 'Quiz',
                        '2' => 'Homework',
                    ]),
                Forms\Components\Select::make("lesson_id")
                    ->options(Lesson::all()->pluck("name", "id")),
                Forms\Components\TextInput::make("duration")->required()
                    ->numeric(),
                Hidden::make("user_id"),
                Toggle::make('status')
                    ->onColor('success')
                    ->offColor('danger')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->numeric()
                ->label("Unit name")
                ->sortable(),
                Tables\Columns\TextColumn::make('type')
                ->sortable(),
            

            Tables\Columns\TextColumn::make('lesson.name')
                ->sortable(),
            Tables\Columns\TextColumn::make('duration')
                ->sortable(),
            Tables\Columns\IconColumn::make('status')->boolean(),
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
            'index' => Pages\ListTests::route('/'),
            'create' => Pages\CreateTest::route('/create'),
            'view' => Pages\ViewTest::route('/{record}'),
            'edit' => Pages\EditTest::route('/{record}/edit'),
        ];
    }
}
