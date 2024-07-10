<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\UserResource\Pages;
use App\Filament\Teacher\Resources\UserResource\RelationManagers;
use App\Filament\Teacher\Resources\UserResource\RelationManagers\ProgramRelationManager;
use App\Models\School;
use App\Models\Stage;
use App\Models\User;
use App\Models\UserDetails;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 2);
    }
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord:true)
                    ->maxLength(255),
                Hidden::make('role')->default('2'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    // ->hiddenOn('edit')
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context):bool => $context == 'create')
                    ->maxLength(255),
                Forms\Components\Select::make("stage_id")
                    ->options(Stage::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable()
                    ->required()
                    ->label("Stage"),
                Forms\Components\Select::make("school_id")
                    ->options(School::all()->pluck('name', 'id'))
                    ->preload()
                    ->required()
                    ->searchable()
                    ->label("School"),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('details.stage.name')
                    ->label("Stage")
                    ->searchable(),
                Tables\Columns\TextColumn::make('school.name')
                    ->searchable(),
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
            ProgramRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
