<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonResource\Pages;
use App\Filament\Resources\LessonResource\RelationManagers;
use App\Models\Lesson;
use App\Models\Program;
use App\Models\Unit;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;
    protected static ?string $navigationGroup = 'Lessons';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('program_id')->label('Programs')
                    ->relationship('unit.program', 'name')

                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context == 'create')
                    ->dehydrated(false)
                    ->options(function (Builder $query, Forms\Get $get) {
                        return Program::join('courses', 'programs.course_id', 'courses.id')
                            ->join('stages', 'programs.stage_id', 'stages.id')
                            ->select(DB::raw("CONCAT(programs.name, ' / ', courses.name, ' / ', stages.name  ) AS full_name"), 'programs.id')
                            ->pluck('full_name', 'programs.id');

                    })
                    ->searchable()

                    ->reactive(),
                Forms\Components\Select::make('unit_id')->label('Units')
                    // ->options(fn(\Livewire\Component $livewire, Unit $units, Forms\Get $get, $record): ?array => $get('program_id') ? $units->where('program_id', $get('program_id'))->get()->pluck('name', 'id')->toArray() : Unit::where('id', $record->id)->get()->pluck('name', 'id')->toArray())
                    ->options(fn(\Livewire\Component $livewire, Unit $units, Forms\Get $get): array => $units->where('program_id', $get('program_id'))->get()->pluck('name', 'id')->toArray())
                    ->searchable()
                    ->relationship('unit', 'name')

                    ->preload()
                    ->reactive()
                    ->required(),
                Forms\Components\Select::make('warmup_id')
                    ->relationship('warmup', 'name')
                    ->preload()
                    ->searchable()->required(),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(65535)
                ,
                Forms\Components\TextInput::make('mob_lesson_name')
                    ->maxLength(65535)
                ,
                Forms\Components\TextInput::make('number')
                    ->required()
                    ->maxLength(2)
                    ->minValue(1)
                    ->integer()
                    ->numeric()
                    ->rule(static function (Forms\Get $get, Forms\Components\Component $component, $record): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component, $record) {
                            $existingNumber = Lesson::where('unit_id', $get('unit_id'))->where('number', $get('number'))->where('id', '!=', $get('id'))->first();

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
                Tables\Columns\TextColumn::make('name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.name')
                    ->numeric()
                    ->sortable(),

                // Tables\Columns\TextColumn::make('warmup.name')
                //     ->numeric()

                //     ->sortable(),

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
            RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}
