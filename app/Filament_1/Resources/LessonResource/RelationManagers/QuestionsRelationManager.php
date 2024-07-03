<?php

namespace App\Filament\Resources\LessonResource\RelationManagers;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\RevisionQuestionsBank;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use stdClass;

class QuestionsRelationManager extends RelationManager
{
    public $field_names = [];
    protected static string $relationship = 'questions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Add Mathematics Question')->schema([
                    // Forms\Components\Select::make('test_id')
                    //     ->relationship('test', 'name')
                    //     ->searchable()
                    //     ->preload()
                    //     ->afterStateUpdated(function (?string $state, $set) {
                    //         // dd($state);
                    //         $pages = Question::where('test_id', $state)->orderBy('id', 'desc')->first();
                    //         $set('number', $pages ? $pages->number + 1 : 1);
                    //     })
                    //     ->createOptionForm([
                    //         Forms\Components\TextInput::make('name')
                    //             ->required()
                    //             ->maxLength(255),
                    //         Forms\Components\Hidden::make('type')->default('0'),
                    //     ])
                    //     ->required(),
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
                    Hidden::make('lesson_id')->default(static function ($livewire) {
                        return $livewire->ownerRecord->id;
                    }),
                    Forms\Components\TextInput::make('number')->label('Sort')
                        ->numeric()
                        ->hidden()
                        ->reactive()
                        ->rule(static function (Forms\Get $get, Forms\Components\Component $component, $state): Closure {
                            return static function (string $attribute, $value, Closure $fail) use ($get, $component, $state) {
                                $existingNumber = Question::where('test_id', $get('test_id'))->where('number', $get('number'))->where('id', '!=', $get('id'))->first();

                                if ($existingNumber) {
                                    $number = ucwords($get('number'));
                                    $fail("The number \"{$value}\" already exists for the chosen test.");
                                }
                            };
                        })
                        ->minValue(1)
                        ->maxValue(10)
                        ->columnSpan(1)
                        ->required(), // ignore current record when editing,


                    Forms\Components\TextInput::make('time')
                        ->numeric()->required()
                        ->minValue(1)
                        ->columnSpan(1)
                        ->maxValue(60),
                    Forms\Components\TextInput::make('difficulty')
                        ->numeric()
                        ->minValue(1)
                        ->columnSpan(1)
                        ->maxValue(10)->required(),
                    Section::make('Question Details')
                        ->schema([
                            Forms\Components\Textarea::make('question')->placeholder('Question title/ ex. How many more to make 5 ? ')
                                ->maxLength(64)

                                ->columnSpanFull()->required(),
                                Forms\Components\Select::make('section_in_book')->label('Section')
                                ->required()
                                ->searchable()
                                ->options([
                                    'Beginning' => 'Beginning',
                                    'Guided Practice' => 'Guided Practice',
                                    'Independent Practice' => 'Independent Practice',
                                    'Checkpoint' => 'Checkpoint',
                                    'Assessment' => 'Assessment',

                                ])->columnSpanFull()
                                ->reactive(),
                            Forms\Components\Select::make('qtype')->label('Question Type')
                                ->required()
                                ->searchable()
                                ->options([
                                    'How Many more?' => 'How Many more?',
                                    'Count' => 'Count',
                                    'Sum' => 'Sum',

                                ])
                                ->reactive(),
                            Forms\Components\Select::make('type')
                                ->required()
                                ->searchable()
                                ->options([
                                    'Color' => 'Color',
                                    'MCQ' => 'MCQ',
                                    'Drag & Drop' => 'Drag & Drop',
                                    'Matching' => 'Matching',

                                ])
                                ->reactive(),
                            Forms\Components\Select::make('sec_type')
                                ->required()
                                ->searchable()
                                ->options([
                                    'Rods' => 'Rods',
                                    'Beads' => 'Beads',
                                    'Strip Board' => 'Strip Board',
                                    'Chart' => 'Chart',

                                ])
                                ->reactive(),

                        ])->columns(3),

                    TagsInput::make('choices')
                        ->afterStateUpdated(function (?array $state, \Livewire\Component $livewire, Forms\Get $get) {

                            $livewire->field_names = $state;


                        })
                        ->placeholder('Enter choices each on a new line')->columnSpanFull(),


                    Section::make('Question Fields')
                        ->statePath('control')
                        ->schema([
                            TextInput::make('control')->required()->integer(),
                            TextInput::make('operator'),
                            TextInput::make('action')->required()->integer(),
                            Forms\Components\Select::make('answer')
                                ->options(fn(\Livewire\Component $livewire, Forms\Get $get): array => $livewire->field_names)
                                ->reactive()
                                ->searchable()
                                ->required()
                                ->columnSpan(2)->live(),
                            // TextInput::make('answer')->required()->columnSpan(2),
                            Toggle::make('show_num')->onIcon('heroicon-m-eye')
                                ->offIcon('heroicon-m-eye-slash')->label('Show number')->required(),
                            TextInput::make('hint')->required()->columnSpan(3)
                        ])->columns(3),

                    // Split
                    // Forms\Components\Repeater::make('choices')
                    //     ->schema([
                    //         Forms\Components\TextInput::make('choice')
                    //         ->live(onBlur: true)
                    //             ->afterStateUpdated(function (?string $state, \Livewire\Component $livewire, Forms\Get $get) {
                    //             $livewire->field_names[$state] = $state;

                    //             })->columns(2)




                    //             // ->disabled(fn(Get $get): bool => filled($get('choice')))
                    //             ->required(),

                    //     ])->minItems(2)->reactive()
                    //     ->maxItems(5)
                    //     ->deleteAction(
                    //         fn (\Filament\Forms\Components\Actions\Action $action) => $action->after(function (callable $get,\Livewire\Component $livewire,array $state,Forms\Set $set){

                    //             $result = array();
                    //             if(sizeof($state) == 0)
                    //                 $livewire->field_names = array();
                    //             $i =0;
                    //             foreach($state as $key => $value) {
                    //                 foreach($value as $v){
                    //                     array_push($result,$v);
                    //                 }

                    //             }
                    //             $livewire->field_names = $result;
                    //             $set('answer','');


                    //         } ),
                    //     )
                    //     ->addActionLabel('Add choice')
                    //     ->label('Choices')


                    //     ->hidden(fn(Forms\Get $get): string => $get('type') == '1' ? false : true)
                    //     ->required(),







                    // Forms\Components\Textarea::make('answer')
                    //     ->maxLength(65535)
                    //     ->hidden(fn(Forms\Get $get): string => $get('type') == '0' ? false : true)
                    //     ->columnSpanFull(),


                    // Forms\Components\Textarea::make('first_part')
                    //     ->maxLength(65535)
                    //     ->hidden(fn(Forms\Get $get): string => $get('type') == '0' ? false : true)
                    //     ->columnSpanFull(),
                    // Forms\Components\Textarea::make('second_part')
                    //     ->maxLength(65535)
                    //     ->hidden(fn(Forms\Get $get): string => $get('type') == '0' ? false : true)
                    //     ->columnSpanFull(),
                    // Forms\Components\Select::make('true_flag')
                    //     ->options([
                    //         '1' => 'True',
                    //         '0' => 'False',
                    //     ])->label('True or False')
                    //     ->searchable()
                    //     ->hidden(fn(Forms\Get $get): string => $get('type') == '2' ? false : true),
                ])->collapsed()->columns(2),


                // Section::make('Add Phonics Question')->schema([
                //     Forms\Components\Select::make('test_id')
                //         ->relationship('test', 'name')
                //         ->searchable()
                //         ->preload()
                //         ->afterStateUpdated(function (?string $state, $set) {
                //             // dd($state);
                //             $pages = Question::where('test_id', $state)->orderBy('id', 'desc')->first();
                //             $set('number', $pages ? $pages->number + 1 : 1);
                //         })
                //         ->createOptionForm([
                //             Forms\Components\TextInput::make('name')
                //                 ->required()
                //                 ->maxLength(255),
                //             Forms\Components\Hidden::make('type')->default('0'),
                //         ])
                //         ->required(),
                //     Forms\Components\Select::make('bank_id')
                //         ->relationship('bank', 'name')
                //         ->searchable()
                //         ->preload()
                //         ->createOptionForm([
                //             Forms\Components\TextInput::make('name')
                //                 ->required()
                //                 ->maxLength(255),

                //         ])
                //         ->required(),
                //     Forms\Components\TextInput::make('number')->label('Sort')
                //         ->numeric()
                //         ->hidden()
                //         ->reactive()
                //         ->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
                //             return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
                //                 $existingNumber = Question::where('test_id', $get('test_id'))->where('number', $get('number'))->where('id', '!=', $get('id'))->first();

                //                 if ($existingNumber) {
                //                     $number = ucwords($get('number'));
                //                     $fail("The number \"{$value}\" already exists for the chosen test.");
                //                 }
                //             };
                //         })
                //         ->minValue(1)
                //         ->maxValue(10)
                //         ->columnSpan(1)
                //         ->required(), // ignore current record when editing,


                //     Forms\Components\TextInput::make('time')
                //         ->numeric()->required()
                //         ->minValue(1)
                //         ->columnSpan(1)
                //         ->maxValue(60),
                //     Forms\Components\TextInput::make('difficulty')
                //         ->numeric()
                //         ->minValue(1)
                //         ->columnSpan(1)
                //         ->maxValue(10)->required(),
                //     Section::make('Question Details')
                //         ->schema([
                //             Forms\Components\Textarea::make('question')->placeholder('Question title/ ex. How many more to make 5 ? ')
                //                 ->maxLength(32)

                //                 ->columnSpanFull()->required(),

                //             Forms\Components\Select::make('qtype')->label('Question Type')
                //                 ->required()
                //                 ->searchable()
                //                 ->options([
                //                     'How Many more?' => 'How Many more?',
                //                     'Count' => 'Count',
                //                     'Sum' => 'Sum',

                //                 ])
                //                 ->reactive(),
                //             Forms\Components\Select::make('type')
                //                 ->required()
                //                 ->searchable()
                //                 ->options([
                //                     'Color' => 'Color',
                //                     'MCQ' => 'MCQ',
                //                     'Drag & Drop' => 'Drag & Drop',
                //                     'Matching' => 'Matching',

                //                 ])
                //                 ->reactive(),
                //             Forms\Components\Select::make('sec_type')
                //                 ->required()
                //                 ->searchable()
                //                 ->options([
                //                     'Rods' => 'Rods',
                //                     'Beads' => 'Beads',
                //                     'Strip Board' => 'Strip Board',
                //                     'Chart' => 'Chart',

                //                 ])
                //                 ->reactive(),

                //         ])->columns(3),

                //     TagsInput::make('choices')
                //         ->afterStateUpdated(function (?array $state, \Livewire\Component $livewire, Forms\Get $get) {

                //             $livewire->field_names = $state;


                //         })
                //         ->placeholder('Enter choices each on a new line')->columnSpanFull(),


                //     Section::make('Question Fields')
                //         ->statePath('control')
                //         ->schema([
                //             TextInput::make('control')->required()->integer(),
                //             TextInput::make('operator'),
                //             TextInput::make('action')->required()->integer(),
                //             Forms\Components\Select::make('answer')
                //                 ->options(fn(\Livewire\Component $livewire, Forms\Get $get): array => $livewire->field_names)
                //                 ->reactive()
                //                 ->searchable()
                //                 ->required()
                //                 ->columnSpan(2)->live(),
                //             // TextInput::make('answer')->required()->columnSpan(2),
                //             Toggle::make('show_num')->onIcon('heroicon-m-eye')
                //                 ->offIcon('heroicon-m-eye-slash')->label('Show number')->required(),
                //             TextInput::make('hint')->required()->columnSpan(3)
                //         ])->columns(3),
                //         ])->collapsed()->columns(2)





                    // Split
                    // Forms\Components\Repeater::make('choices')
                    //     ->schema([
                    //         Forms\Components\TextInput::make('choice')
                    //         ->live(onBlur: true)
                    //             ->afterStateUpdated(function (?string $state, \Livewire\Component $livewire, Forms\Get $get) {
                    //             $livewire->field_names[$state] = $state;

                    //             })->columns(2)




                    //             // ->disabled(fn(Get $get): bool => filled($get('choice')))
                    //             ->required(),

                    //     ])->minItems(2)->reactive()
                    //     ->maxItems(5)
                    //     ->deleteAction(
                    //         fn (\Filament\Forms\Components\Actions\Action $action) => $action->after(function (callable $get,\Livewire\Component $livewire,array $state,Forms\Set $set){

                    //             $result = array();
                    //             if(sizeof($state) == 0)
                    //                 $livewire->field_names = array();
                    //             $i =0;
                    //             foreach($state as $key => $value) {
                    //                 foreach($value as $v){
                    //                     array_push($result,$v);
                    //                 }

                    //             }
                    //             $livewire->field_names = $result;
                    //             $set('answer','');


                    //         } ),
                    //     )
                    //     ->addActionLabel('Add choice')
                    //     ->label('Choices')


                    //     ->hidden(fn(Forms\Get $get): string => $get('type') == '1' ? false : true)
                    //     ->required(),







                    // Forms\Components\Textarea::make('answer')
                    //     ->maxLength(65535)
                    //     ->hidden(fn(Forms\Get $get): string => $get('type') == '0' ? false : true)
                    //     ->columnSpanFull(),


                    // Forms\Components\Textarea::make('first_part')
                    //     ->maxLength(65535)
                    //     ->hidden(fn(Forms\Get $get): string => $get('type') == '0' ? false : true)
                    //     ->columnSpanFull(),
                    // Forms\Components\Textarea::make('second_part')
                    //     ->maxLength(65535)
                    //     ->hidden(fn(Forms\Get $get): string => $get('type') == '0' ? false : true)
                    //     ->columnSpanFull(),
                    // Forms\Components\Select::make('true_flag')
                    //     ->options([
                    //         '1' => 'True',
                    //         '0' => 'False',
                    //     ])->label('True or False')
                    //     ->searchable()
                    //     ->hidden(fn(Forms\Get $get): string => $get('type') == '2' ? false : true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('questions')
            ->columns([
                TextColumn::make('index')->state(
                    static function (HasTable $livewire, stdClass $rowLoop): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->getTableRecordsPerPage() * (
                                $livewire->getTablePage() - 1
                            ))
                        );
                    }
                ),
                Tables\Columns\TextColumn::make('section_in_book')->label('Section')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('question')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make("qtype")->label('Question Type'),
                Tables\Columns\TextColumn::make("type")->label('
                Solve by'),
                Tables\Columns\TextColumn::make("sec_type")->label('Object Type'),
                Tables\Columns\TextColumn::make("control.hint")->label('Hint'),

                // Tables\Columns\TextColumn::make('number')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('difficulty')
                    ->numeric()
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $index = $data['control']['answer'];
                        $num = json_decode($data['choices'][$index], true);
                        $data['answer'] = $num;

                        return $data;
                    })
                    //     $record = new Question();
                    //     $record->test_id = $data['test_id'];
                    //     $record->bank_id = $data['bank_id'];
                    //     $record->time = $data['time'];
                    //     $record->difficulty = $data['difficulty'];
                    //     $record->question = $data['question'];
                    //     $record->qtype = $data['qtype'];
                    //     $record->type = $data['type'];
                    //     $record->sec_type = $data['sec_type'];
                    //     $record->choices = $data['choices'];
                    //     $record->control = $data['control'];
                    //     $record->lesson_id = $data['lesson_id'];
                    //     $record->save();

                    //     $question = Question::find($record->id);

                    //     $number = Question::where('test_id', $question->test_id)->orderBy('id', 'desc')->count();
                    //     $question->number = $number;
                    //     $question->save();

                    //     $index = $data['control']['answer'];
                    //     $num = json_decode($data['choices'][$index], true);

                    //     $question->control['answer'] = $num;
                    //     $question->save();

                    //     $revision = new RevisionQuestionsBank();
                    //     $revision->bank_id = $record->bank_id;
                    //     $revision->question_id = $record->id;
                    //     $revision->number = $number;
                    //     $revision->save();

                    //     return $question->toArray();
                    // })
                    ,
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
