<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BenchmarkResource\Pages;
use App\Filament\Resources\BenchmarkResource\RelationManagers;
use App\Models\Benchmark;
use App\Models\Program;
use App\Models\Unit;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class BenchmarkResource extends Resource
{
    protected static ?string $model = Benchmark::class;
    protected static ?int $navigationSort = 12;
    protected static ?string $navigationGroup = 'Programs';
    protected static ?string $navigationIcon = 'heroicon-o-stop-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('program_id')
                    // ->relationship('program', 'name')
                    ->required()
                    ->options(function (Builder $query, Forms\Get $get) {
                        return Program::join('courses', 'programs.course_id', 'courses.id')
                            ->join('stages', 'programs.stage_id', 'stages.id')
                            ->select(DB::raw("CONCAT(programs.name, ' / ', courses.name, ' / ', stages.name  ) AS full_name"), 'programs.id')
                            ->pluck('full_name', 'programs.id');
                        //     return Program::join('courses','programs.course_id','courses.id')->select(DB::raw("CONCAT(programs.name,' / ',courses.name)")   
                        // )->get();
                    })

                    ->preload()
                    ->searchable()
                    ->label("Program"),
                Forms\Components\Select::make('unit_id')
                    ->options(fn(\Livewire\Component $livewire, Unit $units, Forms\Get $get): array => $units->where('program_id', $get('program_id'))->get()->pluck('name', 'id')->toArray())
                    ->live()
                    ->multiple()
                    ->reactive()
                    ->required(fn(string $context): bool => $context == 'create')
                    ->searchable()
                    ->preload()
                    ->label("Unit"),
                Forms\Components\Select::make('test_id')
                    ->relationship('test', 'name')
                    ->preload()
                    ->searchable()->required(),
                Forms\Components\TextInput::make('number')
                    ->required()
                    ->maxLength(2)
                    ->minValue(1)
                    ->integer()
                    ->numeric()
                    ->rule(static function (Forms\Get $get, Forms\Components\Component $component, $record): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component, $record) {
                            $existingNumber = Benchmark::where('test_id', $get('test_id'))->where('program_id', $get('program_id'))->where('number', $get('number'))->where('id', '!=', $get('id'))->first();

                            if ($existingNumber) {
                                $number = ucwords($get('number'));
                                $fail("The number \"{$value}\" already exists for the chosen test.");
                            }
                        };
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('program.course.name')->label("Program")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('test.name')
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
            'index' => Pages\ListBenchmarks::route('/'),
            'create' => Pages\CreateBenchmark::route('/create'),
            'edit' => Pages\EditBenchmark::route('/{record}/edit'),
            'view' => Pages\ViewBenchmark::route('/{record}/view'),
        ];
    }
}
