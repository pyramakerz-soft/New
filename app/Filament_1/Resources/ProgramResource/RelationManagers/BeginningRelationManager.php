<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class BeginningRelationManager extends RelationManager
{
    protected static string $relationship = 'beginning';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
               
                Forms\Components\Select::make('test_id')
                    ->relationship("test", "name")
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Textarea::make('video_author')
                    ->required()
                    ->maxLength(65535)->required(),

                Forms\Components\FileUpload::make('video')
                    ->required()
                    ->preserveFilenames()->required()
                    ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/mkv', 'video/webm']),

                Forms\Components\TextInput::make('video_message')
                    ->required()
                    ->maxLength(65535)
                ,
                Forms\Components\FileUpload::make('doc')
                    ->preserveFilenames()->required(),
                Forms\Components\FileUpload::make('test')
                    ->preserveFilenames()->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('doc')
            ->columns([
                Tables\Columns\TextColumn::make('program.course.name')->label("Program"),
                Tables\Columns\TextColumn::make('doc'),
                Tables\Columns\TextColumn::make('test.name'),
                Tables\Columns\TextColumn::make('video_author'),
                Tables\Columns\TextColumn::make('video_message'),


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
