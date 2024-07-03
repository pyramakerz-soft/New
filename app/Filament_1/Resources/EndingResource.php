<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EndingResource\Pages;
use App\Filament\Resources\EndingResource\RelationManagers;
use App\Models\Ending;
use App\Models\Program;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class EndingResource extends Resource
{
    protected static ?string $model = Ending::class;
    protected static ?string $navigationGroup = 'Programs';
    protected static ?int $navigationSort = 12;
    protected static ?string $navigationIcon = 'heroicon-o-backward';

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
                    ->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
                            $existingProgram = Ending::where('program_id', $value)->exists();

                            if ($existingProgram) {
                                $program = ucwords($get('program_id'));
                                $fail("The program  already exists for the chosen test.");
                            }
                        };
                    })
                    ->preload()
                    ->label("Program")
                    ->searchable(),
                Forms\Components\Select::make('test_id')
                    ->relationship('test', 'name')
                    ->preload()->required()
                    ->searchable(),
            ])

        ;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('program.course.name')
                    ->numeric()
                    ->label("Program")
                    ->sortable(),
                Tables\Columns\TextColumn::make('test.name')
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
            'index' => Pages\ListEndings::route('/'),
            'create' => Pages\CreateEnding::route('/create'),
            'edit' => Pages\EditEnding::route('/{record}/edit'),
        ];
    }
}
