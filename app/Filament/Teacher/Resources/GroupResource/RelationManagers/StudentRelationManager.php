<?php

namespace App\Filament\Teacher\Resources\GroupResource\RelationManagers;

use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Stage;
use App\Models\StudentTest;
use App\Models\Test;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentRelationManager extends RelationManager
{
    protected static string $relationship = 'group_students';
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 2);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([


                // Forms\Components\Select::make("stage_id")
                //     ->options(Stage::all()->pluck('name', 'id'))
                //     ->preload()
                //     ->searchable()
                //     ->label("Stage"),
                // Forms\Components\Select::make("group_id")
                //     ->options(Group::all()->pluck('name', 'id'))
                //     ->preload()
                //     ->searchable()
                //     ->label("Group"),
                // Hidden::make('stage_id')->default(fn(\Livewire\Component $livewire):string => $livewire->ownerRecord->stage_id),
                Hidden::make('stage_id')->default(fn(\Livewire\Component $livewire): string => $livewire->ownerRecord->stage_id),
                Forms\Components\Select::make("student_id")
                    ->options(
                        static function (Forms\Get $get, Forms\Components\Component $component, \Livewire\Component $livewire) {
                            // return dd(User::where("role", 2)->join('user_details','users.id','user_details.user_id')->where('user_details.stage_id',$livewire->ownerRecord->stage_id)->pluck('users.name','users.id'));
                            //  static function (string $attribute, $value, Closure $fail) use($get,$livewire){
                
                            return User::where("role", 2)->join('user_details', 'users.id', 'user_details.user_id')->where('user_details.stage_id', $livewire->ownerRecord->stage_id)->pluck('users.name', 'users.id');


                            // };
                        }
                    )
                    ->preload()
                    ->searchable()
                    ->unique(ignoreRecord: true)
                    ->label("Student"),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('student_id')
            ->columns([
                Tables\Columns\TextColumn::make('student.name'),
                Tables\Columns\TextColumn::make('stage.name'),
                Tables\Columns\TextColumn::make('group.name'),
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
                    Tables\Actions\BulkAction::make('assign user test')
                        ->form([
                            Select::make('test_id')->options(

                                Test::all()->pluck('name', 'id')
                            ),
                            // Select::make('group_id')->options(
                            //     Group::all()->pluck('name', 'id')
                            // ),
                        ])->action(function (Collection $record, $data) {
                            $rec = $record->collect();
                            foreach ($rec as $r) {
                                $testId = $data['test_id'];
                                $groupId = $r->group_id;
                                $studentId = $r->student_id;

                                $test = new StudentTest();

                                $test->group_id = $groupId;
                                $test->test_id = $testId;
                                $test->student_id = $studentId;
                                $test->save();
                            }
                        })
                ]),
            ]);
    }
}
