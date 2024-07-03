<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        unset($data['role']);
        $user = User::find($record->id);
        $user->school_id = $data['school_id'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (isset($data['password']) && $data['password'] != null) {

            $user->password = $data['password'];
        }
        $user->save();
        // $record = static::getModel()::create($data);

        return $record;
    }
}
