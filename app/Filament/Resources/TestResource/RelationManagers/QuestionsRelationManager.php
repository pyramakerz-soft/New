<?php

namespace App\Filament\Resources\TestResource\RelationManagers;

use App\Models\RevisionQuestionsBank;
// use App\Models\RevisionQuestionsBank;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Closure;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionsRelationManager extends RelationManager
{
    public $field_names = [];
    protected static string $relationship = 'questions';

    public function form(Form $form): Form
    {
        return $form
        
            ->schema([
                // Section::make('Choose an existing Question from questions bank')->schema([
                //     Select::make('bank_id')
                // ->relationship('bank', 'name')
                // ->createOptionForm([
                //     Forms\Components\TextInput::make('name')
                //         ->required()
                //         ->maxLength(255),
                // ])
                // ->reactive()
                // ->required(),
                // Select::make('questions')
                // ->label('Question')
                // ->options(function(callable $get)
                // {
                //     $questions =  RevisionQuestionsBank::where('bank_id','=',$get('bank_id'))->get();

                //     return $questions->pluck('question','id')->toArray();
                // })
                // ->searchable()
                // ->preload(),
                // ViewField::make('submit')->view('submit')
                // ])->collapsed(),
                

                Section::make('Add New Question')->schema([
                Forms\Components\Select::make('test_id')
                ->relationship('test', 'name')
                ->searchable()
                ->preload()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                        Forms\Components\Hidden::make('type')->default('0'),
                ])
                ->required(),
                Forms\Components\Select::make('bank_id')
                ->relationship('bank', 'name')
                ->searchable()
                ->preload()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    
                ])
                ->required(),
                Forms\Components\Select::make('type')
                ->searchable()
                ->options([
                    '0' => 'Complete',
                    '1' => 'Choices',
                    '2' => 'True/False',
                ])
                                // ->afterStateUpdated(
                //     fn (Set $set, ?string $state) => $state ? $set('question', null) : $set('question', 'hidden')
                    
                // ),
                ->reactive(),
                Forms\Components\TextInput::make('number')
                    ->numeric()
                    
                    ->rule(static function (Forms\Get $get,Forms\Components\Component $component): Closure {
                        return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
                            $existingNumber = Question::where('test_id',$get('test_id'))->where('number',$get('number'))->where('id','!=',$get('id'))->first();

                            if ($existingNumber) {
                                $number = ucwords($get('number'));
                                $fail("The number \"{$value}\" already exists for the chosen test.");
                            }
                        };
                    })
                    ->required(), // ignore current record when editing,
                

                Forms\Components\TextInput::make('time')
                    ->numeric(),
                Forms\Components\TextInput::make('difficulty')
                    ->numeric(),

                
                Forms\Components\Textarea::make('question')
                    ->maxLength(65535)
                    
                    ->columnSpanFull(),


                    // Split
                    Forms\Components\Repeater::make('choices')
                    ->relationship('choices')
                    ->schema([
                        Forms\Components\TextInput::make('choice')
                        ->afterStateUpdated(function (?string $state, \Livewire\Component $livewire) {
                            array_push($livewire->field_names, $state);
                        })
                        ->required(),

                    ])->addActionLabel('Add choice')
                    ->label('Choices')
                    ->hidden(fn (Forms\Get $get): string => $get('type') == '1' ? false  : true  )
                    ->required(),
                    Forms\Components\Select::make('answer')
    ->options(fn (\Livewire\Component $livewire): array => $livewire->field_names)
    ->reactive()
    ->searchable()
    ->hidden(fn (Forms\Get $get): string => $get('type') == '1' ? false  : true  )
    ->label('Select Choice'),
                    
                Forms\Components\Textarea::make('answer')
                    ->maxLength(65535)
                    ->hidden(fn (Forms\Get $get): string => $get('type') == '1'|'2' ? true  : false  )
                    ->columnSpanFull(),
                
                
                Forms\Components\Textarea::make('first_part')
                    ->maxLength(65535)
                    ->hidden(fn (Forms\Get $get): string => $get('type') == '0' ? false  : true  )
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('second_part')
                    ->maxLength(65535)
                    ->hidden(fn (Forms\Get $get): string => $get('type') == '0' ? false  : true  )
                    ->columnSpanFull(),
                Forms\Components\Select::make('true_flag')
                ->options([
                    '1' => 'True',
                    '0' => 'False',
                ])->label('True or False')
                ->searchable()
                ->hidden(fn (Forms\Get $get): string => $get('type') == '2' ? false  : true  ),
                ])->collapsed()->columns(3)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->columns([
                Tables\Columns\TextColumn::make('question'),
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
