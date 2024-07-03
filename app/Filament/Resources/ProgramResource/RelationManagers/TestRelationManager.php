<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Test;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TestRelationManager extends RelationManager
{
    protected static string $relationship = 'test';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('name')
                //     ->required()
                //     ->maxLength(255),
                    
                    Forms\Components\Select::make('test_id')->preload()->reactive()->required()->searchable()
                    ->options(function (Builder $query, Forms\Get $get,$livewire) {
                        // dd($livewire);
                        dd(Test::where('program_id',$livewire->ownerRecord->program_id)->where('stage_id',$livewire->ownerRecord->stage_id)->get());
                        return Test::where('program_id',$livewire->ownerRecord->program_id)->where('stage_id',$livewire->ownerRecord->stage_id)
                            ->pluck('name', 'id');
                        //     return Program::join('courses','programs.course_id','courses.id')->select(DB::raw("CONCAT(programs.name,' / ',courses.name)")   
                        // )->get();
                    })
            ]);
    }

     public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->label('#'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->authorize(true),
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
