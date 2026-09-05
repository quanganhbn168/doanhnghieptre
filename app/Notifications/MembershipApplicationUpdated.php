<?php

namespace App\Notifications;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class MembershipApplicationUpdated extends Notification implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public function __construct(public int $businessId)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $business = Business::query()->findOrFail($this->businessId);

        return (new MailMessage)->subject('Cập nhật hồ sơ gia nhập Hội - '.$business->application_code)
            ->greeting('Kính gửi người đại diện doanh nghiệp,')
            ->line('Hồ sơ: '.$business->application_code.' — '.$business->name)
            ->line('Trạng thái: '.Business::STATUS_LABELS[$business->status])
            ->action('Theo dõi hồ sơ', URL::temporarySignedRoute('membership.track', now()->addDays(30), ['business' => $business->id]))
            ->line('Liên kết dành riêng cho người đại diện, có hiệu lực trong 30 ngày. Vui lòng không chia sẻ liên kết này.');
    }
}
