<?php

namespace App\Services;

use App\Models\Business;
use App\Notifications\MembershipApplicationUpdated;
use Illuminate\Support\Facades\Notification;

class MembershipNotificationService
{
    public function updated(Business $business): void
    {
        $email = $business->submittedBy?->email ?: $business->application_email;
        if ($email) {
            Notification::route('mail', $email)->notify(new MembershipApplicationUpdated($business->id));
        }
    }
}
