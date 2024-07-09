<?php

namespace App\Filament\Teacher\Resources;

use App\Filament\Teacher\Resources\ProgramResource\Pages;
use App\Filament\Teacher\Resources\ProgramResource\RelationManagers;
use App\Filament\Teacher\Resources\UnitResource\Pages\ListUnits;
use App\Models\Program;
use App\Models\Unit;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('name')
                    ->required()
                    ->options([
                        'Mindbuzz' => 'Mindbuzz',
                    ])
                    ->searchable()
                    ->preload()
                    ->preload(),
                Forms\Components\Select::make('course_id')
                    ->relationship('course', 'name')
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(14)->unique(),
                    ])->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
                            $existingData = Program::where('course_id', $get('course_id'))->where('school_id', $get('school_id'))->where('stage_id', $get('stage_id'))->where('id', '!=', $get('id'))->first();

                            if ($existingData) {
                                // $number = ucwords($get('number'));
                                $fail("The course  already exists .");
                            }
                        };
                    }),
                Forms\Components\Select::make('school_id')
                    ->relationship('school', 'name')
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                    ])->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
                            $existingData = Program::where('school_id', $get('school_id'))->where('stage_id', $get('stage_id'))->where('course_id', $get('course_id'))->where('id', '!=', $get('id'))->first();

                            if ($existingData) {
                                // $number = ucwords($get('number'));
                                $fail("The school  already exists .");
                            }
                        };
                    }),
                Forms\Components\Select::make('stage_id')
                    ->relationship('stage', 'name')
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
                            $existingData = Program::where('stage_id', $get('stage_id'))->where('school_id', $get('school_id'))->where('course_id', $get('course_id'))->where('id', '!=', $get('id'))->first();

                            if ($existingData) {
                                // $number = ucwords($get('number'));
                                $fail("The stage id already exists.");
                            }
                        };
                    })

                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->required()
                    ->preserveFilenames()

                ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('course.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('school.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('stage.name')->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function sidebar(Model $record): FilamentPageSidebar
    {
        $units = Unit::where("program_id", $record->id)->get();

        $unitNavigationItems = $units->map(function ($unit) {
            return PageNavigationItem::make($unit->name)
                ->url(function () use ($unit) {
                    return URL("teacher/units/" . $unit->id . "/view");
                })
                ->icon('heroicon-o-rectangle-stack');
        });
        $unitNavigationItems = $unitNavigationItems->toArray();


        // array_push(
        //     $unitNavigationItems,
        //     PageNavigationItem::make('Create units')
        //         ->url(function () use ($record) {
        //             return static::getUrl('create_unit');
        //         })
        //         ->icon('heroicon-o-plus')
        //         ->label("Add Unit")
        // );
        // dd($unitNavigationItems);
        return FilamentPageSidebar::make()
            ->setTitle('Units')
            ->setNavigationItems($unitNavigationItems);


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
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'view' => Pages\ViewResource::route('/programs/{record}/view'),
            // 'units' => ListUnits::route('/show_units'),

            // 'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
