<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\GroupResource\Pages;
use App\Filament\Teacher\Resources\GroupResource\RelationManagers;
use App\Filament\Teacher\Resources\GroupResource\RelationManagers\StudentRelationManager;
use App\Models\Group;
use App\Models\Program;
use App\Models\School;
use App\Models\Stage;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('name')
                    ->required()
                    ->maxLength(65535)
                ,
                Forms\Components\Select::make("school_id")
                    ->options(School::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable()
                    ->label("School"),
                Forms\Components\Select::make("stage_id")
                    ->options(Stage::all()->pluck('name', 'id'))
                    ->preload()
                    ->searchable()
                    ->label("Stage"),
                Forms\Components\Select::make('program_id')
                    ->options(function (Builder $query, Forms\Get $get) {
                        return Program::join('courses', 'programs.course_id', 'courses.id')
                            ->select(DB::raw("CONCAT(programs.name, ' / ', courses.name) AS full_name"), 'programs.id')
                            ->pluck('full_name', 'programs.id');
                        //     return Program::join('courses','programs.course_id','courses.id')->select(DB::raw("CONCAT(programs.name,' / ',courses.name)")   
                        // )->get();
                    })

                    ->preload()
                    ->searchable()
                    ->label("Program")
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('school.name'),
                Tables\Columns\TextColumn::make('stage.name'),
                Tables\Columns\TextColumn::make('program.course.name')->label("Program"),
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
                Tables\Actions\EditAction::make()->label("Edit/Add student"),
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
            StudentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
