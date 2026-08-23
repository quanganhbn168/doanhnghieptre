<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class EventRegistrationController extends Controller
{
    public function store(Request $request, string $event): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'guest_count' => ['nullable', 'integer', 'min:0', 'max:5'],
        ]);

        DB::transaction(function () use ($request, $event, $data): void {
            $eventRecord = DB::table('events')
                ->where('slug', $event)
                ->where('status', 'published')
                ->where('visibility', 'public')
                ->lockForUpdate()
                ->first();

            abort_unless($eventRecord, 404);

            if (($eventRecord->registration_opens_at && now()->lt($eventRecord->registration_opens_at))
                || ($eventRecord->registration_closes_at && now()->gt($eventRecord->registration_closes_at))) {
                throw ValidationException::withMessages([
                    'event' => 'Thời gian đăng ký cho sự kiện này đã kết thúc hoặc chưa mở.',
                ]);
            }

            $memberId = null;
            if ($request->user() && Schema::hasTable('members')) {
                $memberId = DB::table('members')->where('user_id', $request->user()->id)->value('id');
            }

            $guestCount = (int) ($data['guest_count'] ?? 0);
            $currentSlots = (int) DB::table('event_registrations')
                ->where('event_id', $eventRecord->id)
                ->where('status', 'registered')
                ->sum(DB::raw('guest_count + 1'));
            $existingSlots = $memberId
                ? (int) DB::table('event_registrations')
                    ->where('event_id', $eventRecord->id)
                    ->where('member_id', $memberId)
                    ->where('status', 'registered')
                    ->sum(DB::raw('guest_count + 1'))
                : 0;

            if ($eventRecord->capacity && ($currentSlots - $existingSlots + $guestCount + 1) > $eventRecord->capacity) {
                throw ValidationException::withMessages([
                    'event' => 'Sự kiện đã đủ số lượng đăng ký.',
                ]);
            }

            $registration = [
                'registered_by_user_id' => $request->user()?->id,
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
                'guest_count' => $guestCount,
                'status' => 'registered',
                'attendance_status' => 'pending',
                'payment_status' => 'not_required',
                'registered_at' => now(),
                'updated_at' => now(),
            ];

            if ($memberId) {
                $memberRegistration = DB::table('event_registrations')
                    ->where('event_id', $eventRecord->id)
                    ->where('member_id', $memberId)
                    ->first(['id']);

                if ($memberRegistration) {
                    DB::table('event_registrations')->where('id', $memberRegistration->id)->update($registration);
                } else {
                    DB::table('event_registrations')->insert($registration + [
                        'event_id' => $eventRecord->id,
                        'member_id' => $memberId,
                        'created_at' => now(),
                    ]);
                }

                return;
            }

            DB::table('event_registrations')->insert($registration + [
                'event_id' => $eventRecord->id,
                'member_id' => null,
                'created_at' => now(),
            ]);
        });

        return redirect()->to(route('home').'#su-kien')
            ->with('success', 'Đăng ký sự kiện thành công. Ban tổ chức sẽ liên hệ xác nhận.');
    }
}
