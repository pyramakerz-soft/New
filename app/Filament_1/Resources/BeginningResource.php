<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BeginningResource\Pages;
use App\Filament\Resources\BeginningResource\RelationManagers;
use App\Models\Beginning;
use App\Models\Program;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Label;

class BeginningResource extends Resource
{
    protected static ?string $model = Beginning::class;
    protected static ?string $navigationGroup = 'Programs';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationIcon = 'heroicon-o-forward';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('program_id')

                    ->options(function (Builder $query, Forms\Get $get) {
                        return Program::join('courses', 'programs.course_id', 'courses.id')
                            ->join('stages', 'programs.stage_id', 'stages.id')
                            ->select(DB::raw("CONCAT(programs.name, ' / ', courses.name, ' / ', stages.name  ) AS full_name"), 'programs.id')
                            ->pluck('full_name', 'programs.id');
                        //     return Program::join('courses','programs.course_id','courses.id')->select(DB::raw("CONCAT(programs.name,' / ',courses.name)")   
                        // )->get();
                    })
                    ->rule(static function (Forms\Get $get, Forms\Components\Component $component, $record): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component, $record) {
                            if ($record)
                                $existingProgram = Beginning::where('program_id', $value)->where('id', '!=', $record->id)->exists();
                            else
                                $existingProgram = Beginning::where('program_id', $value)->exists();

                            if ($existingProgram) {
                                $program = ucwords($get('program_id'));
                                $fail("The program  already exists for the chosen test.");
                            }
                        };
                    })
                    ->label("Program")
                    ->searchable()
                    ->preload()
                    ->required(),
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
                    ->preserveFilenames()->required()
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                ,
                Forms\Components\FileUpload::make('test')
                    ->preserveFilenames()->required()
                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                ,

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('program.course.name')->label("Program")

                    ->sortable(),
                Tables\Columns\TextColumn::make('test.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('video_author')
                    ->sortable(),
                Tables\Columns\TextColumn::make('video_message')
                    ->sortable(),
                Tables\Columns\IconColumn::make('doc')
                    ->label('documentation')
                    ->boolean()
                    ->toggleable()
                    ->default(1)
                    ->action(fn(Beginning $record) => file_exists(public_path('/storage') . '/' . $record['doc']) ? response()->download(public_path('/storage') . '/' . $record['doc']) : null)
                    ->icon(fn(Beginning $record) =>
                        (file_exists(public_path('/storage') . '/' . $record['doc'])) ? 'heroicon-o-document-text' : 'heroicon-o-x-circle'),
                Tables\Columns\IconColumn::make('test')
                    ->label('Test documentation')
                    ->boolean()
                    ->toggleable()
                    ->default(1)
                    ->action(fn(Beginning $record) => file_exists(public_path('/storage') . '/' . $record['test']) ? response()->download(public_path('/storage') . '/' . $record['test']) : null)
                    ->icon(fn(Beginning $record) =>
                        (file_exists(public_path('/storage') . '/' . $record['test'])) ? 'heroicon-o-document-text' : 'heroicon-o-x-circle'),

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
            'index' => Pages\ListBeginnings::route('/'),
            'create' => Pages\CreateBeginning::route('/create'),
            'view' => Pages\ViewBeginning::route('/{record}'),
            'edit' => Pages\EditBeginning::route('/{record}/edit'),
        ];
    }
}
