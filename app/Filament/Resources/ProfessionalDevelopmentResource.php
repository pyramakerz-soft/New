<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfessionalDevelopmentResource\Pages;
use App\Models\Video;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProfessionalDevelopmentResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static ?string $navigationGroup = 'Categories';
    
    protected static ?string $navigationLabel = 'Professional Development';
    
    protected static ?string $modelLabel = 'Professional Development';
    
    protected static ?string $pluralModelLabel = 'Professional Development';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->required(),
                
                Select::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'name')
                    ->required()
                    ->options(function (Builder $query, Forms\Get $get) {
                        return Unit::join('programs', 'units.program_id', 'programs.id')
                            ->join('stages', 'programs.stage_id', 'stages.id')
                            ->select(DB::raw("CONCAT( units.name, ' / ', stages.name  ) AS full_name"), 'units.id')
                            ->pluck('full_name', 'units.id');
                    })
                    ->searchable(),

                TextInput::make('url')->label('YouTube URL')->nullable(),
                FileUpload::make('file_path')
                    ->label('Video File')
                    ->directory('videos')
                    ->disk('public')
                    ->nullable(),
                
                Forms\Components\Hidden::make('category_id')
                    ->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('unit.name')->label('Unit')->searchable()->sortable(),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('category_id', 1);
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
