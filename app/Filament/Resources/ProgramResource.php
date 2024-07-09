<?php
namespace App\Filament\Resources;

use App\Filament\Resources\BeginningResource\Pages\ListBeginnings;
use App\Filament\Resources\BenchmarkResource\Pages\ListBenchmarks;
use App\Filament\Resources\ProgramResource\Pages;
use App\Filament\Resources\ProgramResource\RelationManagers;
use App\Filament\Resources\ProgramResource\RelationManagers\BeginningRelationManager;
use App\Filament\Resources\ProgramResource\RelationManagers\BenchmarkRelationManager;
use App\Filament\Resources\ProgramResource\RelationManagers\EndingRelationManager;
use App\Filament\Resources\ProgramResource\RelationManagers\UnitRelationManager;
use App\Filament\Resources\ProgramResource\RelationManagers\UnitsRelationManager;
use App\Filament\Resources\ProgramResource\RelationManagers\TestAssignmentRelationManager;
use App\Filament\Resources\UnitResource\Pages\CreateUnit;
use App\Filament\Resources\UnitResource\Pages\ListUnits;
use App\Models\Beginning;
use App\Models\Benchmark;
use App\Models\BenchmarkAssignTo;
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
    protected static ?string $navigationGroup = 'Programs';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-circle';
    protected static ?int $navigationSort = 4;
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
                            ->unique()
                            ->maxLength(30),
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
                            ->unique()

                            ->maxLength(16),
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
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context == 'create')
                    ->dehydrated(true)
                    ->preserveFilenames()
                    ->rules(['mimes:jpg,jpeg,png', 'max:10000'])

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

    public static function sidebar(Model $record): FilamentPageSidebar
    {
        $units = Unit::where("program_id", $record->id)->get();

        $unitNavigationItems = $units->map(function ($unit) {
            return PageNavigationItem::make($unit->name)
                ->url(function () use ($unit) {
                    return URL("admin/units/" . $unit->id . "/view");
                })
                ->icon('heroicon-o-rectangle-stack');
        });


        $unitNavigationItems = $unitNavigationItems->toArray();
        $beginnig = Beginning::get();
        if (count($beginnig) > 0) {
            array_unshift(
                $unitNavigationItems,
                PageNavigationItem::make('beginning')
                    ->url(function () {
                        return static::getUrl('beginning');
                    })->icon('heroicon-o-forward')
            );
        }
        $benchmarks = BenchmarkAssignTo::orderBy('unit_id')->orderBy('benchmark_id')->get()->groupBy('benchmark_id');
        $benchmarkExist = Benchmark::where("program_id", $record->id)->exists();
        $benchmarkNames = [];

        foreach ($benchmarks as $benchmarkId => $assignments) {

            $benchmark = Benchmark::find($benchmarkId);
            if ($benchmark) {
                $benchmarkNames[$benchmarkId]['id'] = $benchmark->id;
                $benchmarkNames[$benchmarkId]['number'] = $benchmark->number;
            }
            if ($benchmarkExist) {

                array_push(
                    $unitNavigationItems,
                    PageNavigationItem::make("Benchmark" . ' ' . $benchmarkNames[$benchmarkId]["number"])
                        ->url(function () use ($benchmarkId, $benchmarkNames) {
                            return URL("admin/benchmarks/" . $benchmarkNames[$benchmarkId]["id"] . "/view");
                        })
                        ->icon('heroicon-o-stop-circle')
                );
            }
        }

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
            BeginningRelationManager::class,
            UnitsRelationManager::class,
            BenchmarkRelationManager::class,
            EndingRelationManager::class,
            TestAssignmentRelationManager::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'create_unit' => CreateUnit::route('create_unit'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
            'view' => Pages\view::route('program/{record}/view'),
            'units' => ListUnits::route('/show_units'),
            'beginning' => ListBeginnings::route('/show_beginning'),
            'benchmark' => ListBenchmarks::route('/show_benchmark'),
        ];
    }
}