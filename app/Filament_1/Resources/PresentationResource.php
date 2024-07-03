<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresentationResource\Pages;
use App\Filament\Resources\PresentationResource\RelationManagers;
use App\Models\Presentation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Console\View\Components\Alert;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PresentationResource extends Resource
{
    protected static ?string $model = Presentation::class;
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationGroup = 'Lessons';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('lesson_id')
                    ->relationship('lesson', 'name')->required(),

                Forms\Components\FileUpload::make('video')
                    ->required()
                    ->preserveFilenames()
                    ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/mkv', 'video/webm'])
                    ->maxSize(500000)

                ,
                Forms\Components\FileUpload::make('ppt')
                    ->maxSize(5* 1024)

                    ->preserveFilenames()->required(),
                Forms\Components\TextInput::make('etool')->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lesson.name')
                    ->sortable(),
                Tables\Columns\IconColumn::make('ppt')
                    ->label('PPT')
                    ->boolean()
                    ->toggleable()
                    ->default(1)

                    ->action(fn(Presentation $record) => file_exists(public_path('/storage') . '/' . $record['ppt']) ? response()->download(public_path('/storage') . '/' . $record['ppt']) : null)
                    ->icon(fn(Presentation $record) =>
                        (file_exists(public_path('/storage') . '/' . $record['ppt'])) ? 'heroicon-o-document-text' : 'heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('etool')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label("Video"),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPresentations::route('/'),
            'create' => Pages\CreatePresentation::route('/create'),
            'view' => Pages\ViewPresentation::route('/{record}'),
            'edit' => Pages\EditPresentation::route('/{record}/edit'),
        ];
    }
}
