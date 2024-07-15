<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\RelationManagers\UserCoursesRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\DetailsRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use JaOcero\RadioDeck\Forms\Components\RadioDeck;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('school_id')
                    ->relationship('school', 'name')
                    ->preload()
                    ->required()
                ,
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(25),
                // Forms\Components\Select::make('role')
                //     ->required()
                //     ->options([
                //         'Admin' => 'Admin',
                //         'Owner' => 'Owner',
                //         'Head' => 'Head',
                //         'Teacher'=> 'Teacher',
                //         'Student' => 'Student',
                //     ]),

                Forms\Components\Select::make('role')->relationship('roles', 'name')->searchable()
                    ->required()
                    ->preload()
                ,


                // ->iconPosition(IconPosition::Before) // Before | After | (string - before | after)
                // ->alignment(Alignment::Center) // Start | Center | End | (string - start | center | end)
                // ->gap('gap-5') // Gap between Icon and Description (Any TailwindCSS gap-* utility)
                //  // Padding around the deck (Any TailwindCSS padding utility)
                // ->direction('column') // Column | Row (Allows to place the Icon on top)
                // ->extraOptionsAttributes([ // Extra Attributes to add to the option HTML element
                //     'class' => ' leading-none w-full flex flex-col items-center justify-center p-4'
                // ])
                // ->extraDescriptionsAttributes([ // Extra Attributes to add to the description HTML element
                //     'class' => 'text-sm font-light text-center'
                // ])
                // ->color('primary') // supports all color custom or not
                // ->columns(5),


                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context == 'create')
                    ->dehydrated(true)
                    ->maxLength(16)
                    ->confirmed(),
                Forms\Components\TextInput::make('password_confirmation')->password()
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrated(false)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->recordUrl(null)
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
            UserCoursesRelationManager::class,
            DetailsRelationManager::class,
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
