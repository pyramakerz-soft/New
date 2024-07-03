<?php

namespace App\Filament\Teacher\Resources\UserResource\RelationManagers;

use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ProgramRelationManager extends RelationManager
{
    protected static string $relationship = 'userCourses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_id')
            ->columns([
                Tables\Columns\TextColumn::make('program.name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
