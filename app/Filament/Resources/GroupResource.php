<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
use App\Filament\Resources\GroupResource\RelationManagers\StudentRelationManager;
use App\Models\Group;
use App\Models\Program;
use App\Models\School;
use App\Models\Stage;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use DB;
class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $modelLabel = "Classes";


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\TextInput::make('sec_name')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\Select::make('school_id')->label('School')
                    ->relationship('school', 'name')
                    ->required()
                    ->options(School::all()->pluck('name', 'id'))
                    ->searchable()

                    ->reactive(),
                Forms\Components\Select::make('stage_id')->label('Stage')
                    ->relationship('stage', 'name')
                    ->required()
                    ->options(Stage::all()->pluck('name', 'id'))
                    ->searchable()

                    ->reactive(),
                Forms\Components\Select::make('program_id')
    ->label('Program')
    ->relationship('program', 'name')
    ->required()
    ->options(Program::join('courses', 'programs.course_id', 'courses.id')
        ->join('stages', 'programs.stage_id', 'stages.id')
        ->select(DB::raw("CONCAT(courses.name, ' / ', stages.name) as name"), 'programs.id as id')
        ->pluck('name', 'id'))
    ->searchable()
    ->reactive(),

                    // (DB::raw("CONCAT('Type : ', game_types.name, ' / Lesson : ', lessons.name, ' / Main Letter : ', games.main_letter, ' / Game Letters : ', GROUP_CONCAT(game_letters.letter ORDER BY game_letters.letter SEPARATOR ', ')) AS name")
                Forms\Components\Select::make('teacher_id')->label('Teacher')
                    ->relationship('teacher', 'name')
                    ->required()
                    ->options(User::where('role', 1)->pluck('name', 'id'))
                    ->searchable()
                    ->reactive(),

                // Forms\Components\FileUpload::make('image')
                //     ->dehydrated(fn($state) => filled($state))
                //     ->required(fn(string $context): bool => $context == 'create')
                //     ->dehydrated(true)
                //     ->preserveFilenames()
                //     ->rules(['mimes:jpg,jpeg,png'])
                 Forms\Components\FileUpload::make('image')
                  
                    
                    ->dehydrated(fn($state) => filled($state))
    ->required(fn(string $context): bool => $context == 'create')
    ->preserveFilenames()
                        ->directory('storage')

    ->rules(['mimes:jpg,jpeg,png'])
                ,


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('sec_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('school.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('stage.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('program.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('program.course.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')->searchable()->sortable(),
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
