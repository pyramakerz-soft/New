<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Filament\Resources\VideoResource\RelationManagers;
use App\Models\Unit;
use App\Models\Video;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;
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
                Forms\Components\FileUpload::make('image')
                ->dehydrated(fn($state) => filled($state))
                ->required(fn(string $context): bool => $context == 'create')
                ->dehydrated(true)
                ->preserveFilenames()
                ->rules(['mimes:jpg,jpeg,png', 'max:10000'])
            ,
                TextInput::make('url')->label('YouTube URL')->nullable(),
                FileUpload::make('file_path')->label('Video File')->nullable(),
                Select::make('category_id')->relationship('category', 'name')->required(),
                Select::make('file_type')->label('Type')
                ->options([
                    'word' => 'Word',
                    'pdf' => 'PDF',
                    'ppt' => 'PPT',
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
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }
}
