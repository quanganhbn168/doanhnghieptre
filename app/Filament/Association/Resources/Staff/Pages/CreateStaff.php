<?php

namespace App\Filament\Association\Resources\Staff\Pages;

use App\Filament\Association\Resources\Staff\StaffResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        abort_unless(StaffResource::canCreate(), 403);

        return DB::transaction(function () use ($data): Model {
            $roles = array_values(array_intersect($data['staff_roles'], array_keys(StaffResource::ROLES)));
            unset($data['staff_roles']);
            $user = User::query()->create([...$data, 'approval_status' => 'approved']);
            $user->syncRoles($roles);

            return $user;
        });
    }
}
