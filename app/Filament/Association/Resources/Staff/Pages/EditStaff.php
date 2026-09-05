<?php

namespace App\Filament\Association\Resources\Staff\Pages;

use App\Filament\Association\Resources\Staff\StaffResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['staff_roles'] = $this->record->roles->pluck('name')->all();
        $data['password'] = null;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        abort_unless(StaffResource::canEdit($record), 403);

        return DB::transaction(function () use ($record, $data): Model {
            $roles = array_values(array_intersect($data['staff_roles'], array_keys(StaffResource::ROLES)));
            unset($data['staff_roles']);
            $record->update($data);
            $record->syncRoles($roles);

            return $record;
        });
    }
}
