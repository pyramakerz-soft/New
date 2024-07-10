<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BeginningResource\Pages\ListBeginnings;
use App\Filament\Resources\LessonResource\Pages\ViewLessons;
use App\Filament\Resources\ProgramResource\Pages\ShowUnit;
use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
use App\Models\Lesson;
use App\Models\Program;
use App\Models\Unit;
use AymanAlhattami\FilamentPageWithSidebar\FilamentPageSidebar;
use AymanAlhattami\FilamentPageWithSidebar\PageNavigationItem;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;
    protected static ?string $navigationGroup = 'Units';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('program_id')
                    ->options(function (Builder $query, Forms\Get $get) {
                        return Program::join('courses', 'programs.course_id', 'courses.id')
                            ->join('stages', 'programs.stage_id', 'stages.id')
                            ->select(DB::raw("CONCAT(programs.name, ' / ', courses.name, ' / ', stages.name  ) AS full_name"), 'programs.id')
                            ->pluck('full_name', 'programs.id');

                    })

                    ->preload()
                    ->searchable()
                    ->label("Program")
                    ->required(),
                    Forms\Components\FileUpload::make('image')
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context == 'create')
                    ->dehydrated(true)
                    ->preserveFilenames()
                    ->rules(['mimes:jpg,jpeg,png', 'max:10000'])

                ,
                Hidden::make('id'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(30)
                    // ->notRegex('/^.+$/i')
                    ->unique(ignoreRecord: true)
                    ->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
                            $existingNumber = Unit::where('program_id', $get('program_id'))->where('name', $get('name'))->where('id', '!=', $get('id'))->first();
                            if ($existingNumber) {
                                $name = ucwords($get('name'));
                                $fail("The name \"{$value}\" already exists for the chosen test.");
                            }
                        };
                    }),
                Forms\Components\TextInput::make('number')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->step(1)
                    ->maxLength(2)
                    ->integer()
                    ->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
                            $existingNumber = Unit::where('program_id', $get('program_id'))->where('number', $get('number'))->where('id', '!=', $get('id'))->first();
                            if ($existingNumber) {
                                $number = ucwords($get('number'));
                                $fail("The number \"{$value}\" already exists for the chosen test.");
                            }
                        };
                    })
                ,
            ]);
    }

    public static function sidebar(Model $record): FilamentPageSidebar
    {
        $lessons = Lesson::where("unit_id", $record->id)->get();

        $unitNavigationItems = $lessons->map(function ($lessons) {
            return PageNavigationItem::make($lessons->name)
                ->url(function () use ($lessons) {
                    return URL("admin/units/" . $lessons->id . "/lesson_view");
                })
            ;
        });

        $unitNavigationItems = $unitNavigationItems->toArray();
        array_unshift(
            $unitNavigationItems,
            PageNavigationItem::make('beginning')
                ->url(function () use ($record) {
                    return static::getUrl('beginning', ['record' => $record->id]);
                })
        );


        // dd($unitNavigationItems);
        return FilamentPageSidebar::make()
            ->setNavigationItems($unitNavigationItems)->topbarNavigation();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->numeric()
                    ->label("Unit name")
                    ->sortable(),
                Tables\Columns\TextColumn::make('program.course.name')->label("Program")
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
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
            'view' => Pages\ViewUnit::route('/{record}/view'),
            'lesson_view' => ViewLessons::route('/{record}/lesson_view'),
            'beginning' => ListBeginnings::route('/{record}/show_beginning'),


        ];
    }
}
