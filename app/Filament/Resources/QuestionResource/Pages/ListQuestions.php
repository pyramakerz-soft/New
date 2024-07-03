<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\ProgramResource\Widgets\ProgramChart;
use App\Filament\Resources\QuestionResource;
use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Choice;
use App\Models\Question;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Http\Request;
use app\Livewire\QuestionComponent;
use Str;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    public $field_names = [];
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            // Action::make('Math Question')
            // ->form([


            //     Section::make('Add New Question')->schema([
            //         Select::make('test_id')
            //             ->relationship('test', 'name')
            //             ->searchable()
            //             ->preload()
            //             ->createOptionForm([
            //                 TextInput::make('name')
            //                     ->required()
            //                     ->maxLength(255),
            //                 Hidden::make('type')->default('0'),
            //             ])
            //             ->required(),
            //         Select::make('bank_id')
            //             ->relationship('bank', 'name')
            //             ->searchable()
            //             ->preload()
            //             ->createOptionForm([
            //                 TextInput::make('name')
            //                     ->required()
            //                     ->maxLength(255),

            //             ])
            //             ->required(),
            //         Select::make('type')
            //         ->required()
            //             ->searchable()
            //             ->options([
            //                 '3' => 'Addition Bricks',
            //                 '4' => 'Subtraction Bricks',
            //                 '5' => 'Addition Beads',
            //                 '6' => 'Addition Chart',
            //                 '7' => 'Addition Tables',
                            
            //             ])

            //             ->reactive(),
            //         TextInput::make('number')
            //             ->numeric()

            //             ->rule(static function (Forms\Get $get, Forms\Components\Component $component): Closure {
            //                 return static function (string $attribute, $value, Closure $fail) use ($get, $component) {
            //                     $existingNumber = Question::where('test_id', $get('test_id'))->where('number', $get('number'))->where('id', '!=', $get('id'))->first();

            //                     if ($existingNumber) {
            //                         $number = ucwords($get('number'));
            //                         $fail("The number \"{$value}\" already exists for the chosen test.");
            //                     }
            //                 };
            //             })
            //             ->minValue(1)
            //             ->maxValue(10)
            //             ->required(), // ignore current record when editing,


            //         Forms\Components\TextInput::make('time')
            //             ->numeric()->required()
            //             ->minValue(1)
            //             ->maxValue(60),
            //         Forms\Components\TextInput::make('difficulty')
            //             ->numeric()
            //             ->minValue(1)
            //             ->maxValue(10)->required(),


            //         Forms\Components\Textarea::make('question')
            //             ->maxLength(65535)

            //             ->columnSpanFull()->required(),


            //         // Split
            //         Forms\Components\Repeater::make('choices')
            //             ->schema([
            //                 Forms\Components\TextInput::make('choice')
            //                 ->live(onBlur: true)
            //                     ->afterStateUpdated(function (?string $state, \Livewire\Component $livewire, Forms\Get $get) {
            //                     $livewire->field_names[$state] = $state;
                                
            //                     })->columns(2)
                                
                                    
                                

            //                     // ->disabled(fn(Get $get): bool => filled($get('choice')))
            //                     ->required(),
                                
            //             ])->minItems(2)->reactive()
            //             ->maxItems(5)
            //             ->deleteAction(
            //                 fn (\Filament\Forms\Components\Actions\Action $action) => $action->after(function (callable $get,\Livewire\Component $livewire,array $state,Forms\Set $set){

            //                     $result = array();
            //                     if(sizeof($state) == 0)
            //                         $livewire->field_names = array();
            //                     $i =0;
            //                     foreach($state as $key => $value) {
            //                         foreach($value as $v){
            //                             array_push($result,$v);
            //                         }
                                
            //                     }
            //                     $livewire->field_names = $result;
            //                     $set('answer','');

                                
            //                 } ),
            //             )
            //             ->addActionLabel('Add choice')
            //             ->label('Choices')
                        

            //             ->hidden(fn(Forms\Get $get): string => $get('type') == '1' ? false : true)
            //             ->required(),

                        


            //         Forms\Components\Select::make('answer')
            //             ->options(fn(\Livewire\Component $livewire): array => $livewire->field_names)
            //             ->reactive()
            //             ->required()
            //             ->searchable()
            //             ->hidden(fn(Forms\Get $get): string => $get('type') == '1' ? false : true)
            //             ->label('Select Choice')->live(),


            //         Forms\Components\Textarea::make('answer')
            //             ->maxLength(65535)
            //             ->hidden(fn(Forms\Get $get): string => $get('type') == '0' ? false : true)
            //             ->columnSpanFull(),


            //         Forms\Components\Textarea::make('first_part')
            //             ->maxLength(65535)
            //             ->hidden(fn(Forms\Get $get): string => $get('type') == '0' ? false : true)
            //             ->columnSpanFull(),
            //         Forms\Components\Textarea::make('second_part')
            //             ->maxLength(65535)
            //             ->hidden(fn(Forms\Get $get): string => $get('type') == '0' ? false : true)
            //             ->columnSpanFull(),
            //         Forms\Components\Select::make('true_flag')
            //             ->options([
            //                 '1' => 'True',
            //                 '0' => 'False',
            //             ])->label('True or False')
            //             ->searchable()
            //             ->hidden(fn(Forms\Get $get): string => $get('type') == '2' ? false : true),
            //     ])->columns(2)
            // ])
        ];
    }
    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
}
