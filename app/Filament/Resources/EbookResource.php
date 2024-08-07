<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EbookResource\Pages;
use App\Filament\Resources\EbookResource\RelationManagers;
use App\Models\Ebook;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class EbookResource extends Resource
{
    protected static ?string $model = Ebook::class;
    protected static ?string $navigationGroup = 'Categories';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->required(),
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
                TextInput::make('url')->label(' URL')->nullable(),

                Select::make('category_id')->relationship('category', 'name')->required(),
                // Forms\Components\Select::make('file_type')->label("Type")
                //     ->options([
                //         'word' => 'Word',
                //         'pdf' => 'PDF',
                //         'ppt' => 'PPT',
                //     ])
                //     ->searchable(),
                Select::make('is_downloadable')->label('Downloadable')
                    ->options([
                        '1' => 'True',
                        '0' => 'False',
                    ])
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('category.name')->label('Category'),
                BooleanColumn::make('is_downloadable')
                    ->label('Downloadable')
                    ->sortable()
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
            'index' => Pages\ListEbooks::route('/'),
            'create' => Pages\CreateEbook::route('/create'),
            'edit' => Pages\EditEbook::route('/{record}/edit'),
        ];
    }
}
