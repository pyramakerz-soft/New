<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentResource extends Resource
{
    // protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Report';
    protected static ?string $model = User::class;
    public static ?string $label = 'Students';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('details.stage.name')->label('Stage'),
                TextColumn::make('school.name')->label('School'),
            ])
            ->filters([
                SelectFilter::make('stage_id')
                    ->label('Stage')
                    ->relationship('details.stage','name')
                    // ->options(
                    //     \App\Models\Stage::all()->pluck('name', 'id')->toArray()
                    // )
                    ,
                    

                SelectFilter::make('school_id')
                    ->label('School')
                    ->relationship('details.school','name')
                    // ->options(
                    //     \App\Models\School::all()->pluck('name', 'id')->toArray()
                    // )
                    ,
                SelectFilter::make('group_id')
                    ->label('Group')
                    ->relationship('groups.group','sec_name')
                    // ->options(
                    //     \App\Models\School::all()->pluck('name', 'id')->toArray()
                    // )
                    ,
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
