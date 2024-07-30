<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherReportResource\Pages;
use App\Filament\Resources\TeacherReportResource\Widgets\CompReportStats;
use App\Filament\Resources\TeacherResource\RelationManagers;
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

class TeacherReportResource extends Resource
{
    // protected static ?string $navigationGroup = 'Report';
    protected static ?string $navigationGroup = 'Report';
     
    public static ?string $label = 'Teachers';


    // public static $label = 'customer';
    protected static ?string $model = User::class;

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
    public static function getWidgets(): array
    {
        return [
            CompReportStats::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherReports::route('/'),
            'create' => Pages\CreateTeacherReport::route('/create'),
            'edit' => Pages\EditTeacherReport::route('/{record}/edit'),
            
        ];
    }
}
