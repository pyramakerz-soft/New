<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PptResource\Pages;
use App\Filament\Resources\PptResource\RelationManagers;
use App\Models\Ppt;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class PptResource extends Resource
{
    protected static ?string $model = Ppt::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('unit_id')->label('Unit')
                ->relationship('unit', 'name')
                ->required()
                // ->options(Unit::all()->pluck('name', 'id'))

                ->options(function (Builder $query, Forms\Get $get) {
                    return Unit::join('programs', 'units.program_id', 'programs.id')
                        ->join('stages', 'programs.stage_id', 'stages.id')
                        ->select(DB::raw("CONCAT( units.name, ' / ', stages.name  ) AS full_name"), 'units.id')
                        ->pluck('full_name', 'units.id');

                })
                ->searchable(),
                Forms\Components\TextInput::make('file_link')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unit_id')
                ->name('Unit.name')
                    
                    ->sortable(),
                Tables\Columns\TextColumn::make('file_link')
                    ->searchable(),
               
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
            'index' => Pages\ListPpts::route('/'),
            'create' => Pages\CreatePpt::route('/create'),
            'edit' => Pages\EditPpt::route('/{record}/edit'),
        ];
    }
}
