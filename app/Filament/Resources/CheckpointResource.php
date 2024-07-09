<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CheckpointResource\Pages;
use App\Filament\Resources\CheckpointResource\RelationManagers;
use App\Models\Checkpoint;
use App\Models\CheckpointAssignedTo;
use App\Models\Lesson;
use App\Models\Program;
use App\Models\Unit;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class CheckpointResource extends Resource
{
    protected static ?string $model = Checkpoint::class;
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationGroup = 'Lessons';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('program_id')->label('Programs')
                    // ->relationship('program', 'name')
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context == 'create')
                    ->dehydrated(false)
                    ->disablePlaceholderSelection(fn(string $context): bool => $context == 'edit')
                    ->options(function (Builder $query, Forms\Get $get, $state, $livewire, ?Checkpoint $record) {
                        if ($record) {
                            $assigned = CheckpointAssignedTo::where('checkpoint_id', $record->id)->first();
                            if ($assigned) {
                                return Program::where('programs.id', $assigned->program_id)->join('courses', 'programs.course_id', 'courses.id')
                                    ->join('stages', 'programs.stage_id', 'stages.id')
                                    ->select(DB::raw("CONCAT(programs.name, ' / ', courses.name, ' / ', stages.name  ) AS full_name"), 'programs.id')
                                    ->pluck('full_name', 'programs.id');
                            }
                        }
                        return Program::join('courses', 'programs.course_id', 'courses.id')
                            ->join('stages', 'programs.stage_id', 'stages.id')
                            ->select(DB::raw("CONCAT(programs.name, ' / ', courses.name, ' / ', stages.name  ) AS full_name"), 'programs.id')
                            ->pluck('full_name', 'programs.id');
                        //     return Program::join('courses','programs.course_id','courses.id')->select(DB::raw("CONCAT(programs.name,' / ',courses.name)")   
                        // )->get();
                    }),
                Forms\Components\Select::make('unit_id')->label('Units')
                    ->disablePlaceholderSelection(fn(string $context): bool => $context == 'edit')
                    ->options(
                        function (Builder $query, Unit $units, Forms\Get $get, $state, $livewire, ?Checkpoint $record) {
                            if ($record) {
                                $assigned = CheckpointAssignedTo::where('checkpoint_id', $record->id)->first();
                                if ($assigned) {
                                    return $units->where('program_id', $assigned->program_id)->get()->pluck('name', 'id')->toArray();
                                }
                            }

                            return $units->where('program_id', $get('program_id'))->get()->pluck('name', 'id')->toArray();
                        }
                    )

                    ->preload()
                    ->reactive()
                    ->searchable()->required(),

                Select::make('test_id')
                    ->relationship('test', 'name')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Hidden::make('type')->default('0'),
                    ])
                    ->required()
                    ->preload()
                    ->searchable(),

                Forms\Components\Select::make('lesson_id')->label('Lessons')
                    ->options(fn(\Livewire\Component $livewire, Lesson $lessons, Forms\Get $get): array => $lessons->where('unit_id', $get('unit_id'))->get()->pluck('name', 'id')->toArray())
                    ->preload()
                    ->multiple()
                    ->reactive()
                    ->searchable()->required(),

                Select::make('bank_id')
                    ->relationship('bank', 'name')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique()
                            ->maxLength(255),
                    ])
                    ->required()
                    ->preload()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('test.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bank.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('checkpointAssignedTo.lesson.name')
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
                Tables\Actions\EditAction::make()

                ,
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
            'index' => Pages\ListCheckpoints::route('/'),
            'create' => Pages\CreateCheckpoint::route('/create'),
            'edit' => Pages\EditCheckpoint::route('/{record}/edit'),
        ];
    }
}
