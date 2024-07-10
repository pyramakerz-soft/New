<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages\CreateProgram;
use App\Filament\Resources\StageResource\Pages;
use App\Filament\Resources\ProgramResource\Pages\ListPrograms;
use App\Filament\Resources\StageResource\Pages\ShowPrograms;
use App\Filament\Resources\StageResource\RelationManagers\ProgramRelationManager;
use resources\views\filament\pages\programs\show;
use App\Filament\Resources\StageResource\RelationManagers;
use App\Models\Program;
use App\Models\Stage;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StageResource extends Resource
{
    public Stage $record;
    protected static ?string $model = Stage::class;
    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = "Stage";
    protected static ?string $navigationGroup = 'Starter';
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')

                    ->maxLength(16)

                    ->unique()
                    ->required(),
                // Forms\Components\Select::make('program_id')

                // ->label('Program')
                // ->searchable()
                // ->options([
                //     'Mindbuzz' => 'Mindbuzz',
                // ])->required(),

                //     Forms\Components\Select::make('course_id')
                //     ->relationship('course', 'name')
                //     ->preload()
                //     ->searchable()
                //     ->required(),
                // Forms\Components\Select::make('school_id')

                // ->preload()
                //     ->relationship('school', 'name')
                //     ->searchable()
                //     ->required(),


                // Forms\Components\FileUpload::make('program.image')->required()->unique(),
            ]);
    }

    public static function sidebar(Model $record): FilamentPageSidebar
    {
        if (Program::where('stage_id', $record->id)->count() > 0) {
            return FilamentPageSidebar::make()
                ->setTitle('Stages')
                ->setNavigationItems([

                    PageNavigationItem::make('Programs')
                        ->url(function () use ($record) {
                            $programs = Program::where('stage_id', $record->id)->first()->stage_id;
                            return static::getUrl("show_programs", compact('programs'));
                            // return static::getUrl('program', ['stage_id' => $record->id]);
                        })->icon('heroicon-o-rectangle-stack'),

                    // ... more items
                ]);
        } else
            return FilamentPageSidebar::make()
                ->setNavigationItems([

                    PageNavigationItem::make('Create Program')
                        ->url(function () {
                            return static::getUrl('create_program');
                        })
                        ->icon('heroicon-o-plus')
                        ->label("Add Program"),
                    // PageNavigationItem::make('Please create a program first !')
                ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name'),


            ])->recordUrl(null)
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStages::route('/'),
            'create' => Pages\CreateStage::route('/create'),
            'edit' => Pages\EditStage::route('{record}/edit'),
            'view' => Pages\ViewStage::route('/{record}/view'),
            'program' => ListPrograms::route('/program'),
            'create_program' => CreateProgram::route('/create_program'),
            'show_programs' => ShowPrograms::route('/show_program'),
        ];
    }


}
