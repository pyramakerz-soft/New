<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfessionalDevelopmentResource\Pages;
use App\Filament\Resources\ProfessionalDevelopmentResource\RelationManagers;
use App\Models\ProfessionalDevelopment;
use App\Models\Program;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ProfessionalDevelopmentResource extends Resource
{
    protected static ?string $model = ProfessionalDevelopment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')

                ->maxLength(16)
                ->required(),

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

                Forms\Components\FileUpload::make('image')
                ->dehydrated(fn($state) => filled($state))
                ->required(fn(string $context): bool => $context == 'create')
                ->dehydrated(true)
                ->preserveFilenames()
                ->rules(['mimes:jpg,jpeg,png', 'max:10000'])
            ,
            Forms\Components\TextInput::make('video')

            ->maxLength(250)
            ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('unit_id')
                ->name('Unit.name')
                    
                    ->sortable(),
                Tables\Columns\TextColumn::make('video')->searchable()->sortable(),
                // Tables\Columns\TextColumn::make('image')->searchable()->sortable(),

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
            'index' => Pages\ListProfessionalDevelopments::route('/'),
            'create' => Pages\CreateProfessionalDevelopment::route('/create'),
            'edit' => Pages\EditProfessionalDevelopment::route('/{record}/edit'),
        ];
    }
}
