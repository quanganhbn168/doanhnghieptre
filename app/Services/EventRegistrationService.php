<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EventRegistrationService
{
    public function updateAttendance(EventRegistration $registration, User $actor, string $action): void
    {
        abort_unless($actor->canManageAssociation(), 403);
        DB::transaction(function () use ($registration, $action): void {
            Event::query()->lockForUpdate()->findOrFail($registration->event_id);
            $record = EventRegistration::query()->lockForUpdate()->findOrFail($registration->id);
            if ($record->status !== 'registered' || ! in_array($action, ['check_in', 'cancel'], true)) {
                throw ValidationException::withMessages(['status' => 'Đăng ký này đã bị hủy. Vui lòng tải lại danh sách.']);
            }
            $record->update($action === 'cancel'
                ? ['status' => 'cancelled']
                : ['attendance_status' => 'attended', 'checked_in_at' => $record->checked_in_at ?? now()]);
        });
    }
}
