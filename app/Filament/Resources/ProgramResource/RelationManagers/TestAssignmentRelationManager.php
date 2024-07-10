<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use App\Models\Game;
use App\Models\StudentTest;
use App\Models\Test;
use App\Models\TestTypes;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;


class TestAssignmentRelationManager extends RelationManager
{
    protected static string $relationship = 'tests';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options(TestTypes::all()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('degree')
                    ->required()
                    ->numeric()
                    ->maxLength(2),
                Forms\Components\TextInput::make('duration')
                    ->required()
                    ->numeric()
                    ->maxLength(255),
                Forms\Components\TextInput::make('mistake_count')
                    ->required()
                    ->numeric()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        '0' => 'inactive',
                        '1' => 'active',
                    ])
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('difficulty_level')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                        'D' => 'D',
                        'E' => 'E',
                    ])
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->dehydrated(fn($state) => filled($state))
                    ->nullable()
                    ->dehydrated(true)
                    ->preserveFilenames()
                    ->rules(['mimes:jpg,jpeg,png']),
                Forms\Components\Select::make('lesson_id')
                    ->relationship("lesson", "name")
                    ->searchable()
                    ->preload()
                    ->required(),
                // Forms\Components\Select::make('user_id')
                //     // ->options(User::whereHass('role', 2)->get()->pluck('name', 'id'))
                //     ->options(function () use ($form) {
                //         // Get the current stage from the livewire owner record
                //         $currentStage = $form->getLivewire()->ownerRecord->stage_id;

                //         // Fetch users where the stage in userDetails matches the program stage and role is 2
                //         return User::with('details')
                //             ->whereHas('details', function (Builder $query) use ($currentStage) {
                //             $query->where('stage_id', $currentStage);
                //         })
                //             ->where('role', 2)
                //             ->get()
                //             ->pluck('name', 'id');
                //     })

                //     ->searchable()
                //     ->preload()
                //     ->required()
                //     ->multiple()
                //     ->label('User'),
                Forms\Components\Select::make('game_id')
                    ->options(function (Builder $query, Forms\Get $get) {
                        return Game::join('game_types', 'games.game_type_id', 'game_types.id')
                            ->join('lessons', 'games.lesson_id', 'lessons.id')
                            ->join('game_letters', 'games.id', 'game_letters.game_id')
                            ->select(DB::raw("CONCAT('Type : ', game_types.name, ' / Lesson : ', lessons.name, ' / Main Letter : ', games.main_letter, ' / Game Letters : ', GROUP_CONCAT(game_letters.letter ORDER BY game_letters.letter SEPARATOR ', ')) AS name"), 'games.id as id')
                            ->groupBy('games.id', 'game_types.name', 'lessons.name', 'games.main_letter')
                            ->pluck('name', 'id')
                            ->toArray();
                    })->preload()
                    ->searchable()->label('Game'),
                Forms\Components\Select::make('teacher_id')
                    ->options(User::where('role', 1)->get()->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Select Teacher'),

                Forms\Components\DatePicker::make('start_date')
                    ->native(false)
                    ->displayFormat('d/m/Y')


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('testAssignments')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('users')
                    ->label('Assigned Users')
                    ->getStateUsing(function (Model $record) {
                        $userIds = is_array($record->user_id) ? $record->user_id : explode(',', $record->user_id);
                        return User::whereIn('id', $userIds)->pluck('name')->join(', ');
                    }),

                Tables\Columns\TextColumn::make('lesson.name'),
                Tables\Columns\TextColumn::make('game.inst'),
                Tables\Columns\TextColumn::make('stage.name'),
                // Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('mistake_count'),
                Tables\Columns\TextColumn::make('difficulty_level'),
                Tables\Columns\TextColumn::make('degree'),
                Tables\Columns\TextColumn::make('duration'),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->authorize(true)

                    ->using(function (array $data, $livewire): Model {
                        $test = new Test();
                        $test->name = $data['name'];
                        $test->type = $data['type'];
                        $test->degree = $data['degree'];
                        $test->program_id = $livewire->ownerRecord->id;
                        $test->stage_id = $livewire->ownerRecord->stage_id;
                        $test->duration = $data['duration'];
                        $test->mistake_count = $data['mistake_count'];
                        $test->status = $data['status'];
                        $test->difficulty_level = $data['difficulty_level'];
                        $test->image = $data['image'] ?? null;
                        $test->lesson_id = $data['lesson_id'];
                        $test->game_id = $data['game_id'];
                        $test->save();

                        // }
                        // return $test;
                        // if (!is_array($data['user_id'])) {
                        //     $data['user_id'] = explode(',', $data['user_id']);
                        // }
                        $student_test = new StudentTest();
                        $student_test->student_id = User::where('email', 'dummy@hidden.com')->first()->id;
                        $student_test->test_id = $test->id;
                        $student_test->lesson_id = $data['lesson_id'];
                        $student_test->program_id = $livewire->ownerRecord->id;
                        $student_test->teacher_id = $data['teacher_id'];
                        $student_test->status = 0;
                        $student_test->start_date = $data['start_date'];
                        $student_test->due_date =Carbon::parse($data['start_date'])->addYears(10);
                        $student_test->image = $data['image'] ?? null;
                        $student_test->save();

                        return $test;

                    }),
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
