<?php

namespace App\Filament\Association\Resources\Events\Pages;

use App\Filament\Association\Resources\Events\EventResource;
use App\Models\Event;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data): Event {
            $event = Event::query()->lockForUpdate()->findOrFail($record->id);
            $registeredSlots = (int) $event->registrations()->where('status', 'registered')->sum(DB::raw('guest_count + 1'));
            if (filled($data['capacity'] ?? null) && (int) $data['capacity'] < $registeredSlots) {
                throw ValidationException::withMessages(['data.capacity' => 'Sức chứa phải từ '.$registeredSlots.' người, bằng số chỗ đã đăng ký.']);
            }
            $event->update($data);

            return $event;
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
