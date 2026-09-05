<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class MembershipAccountInvitation extends Notification implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $token = Password::broker()->createToken($notifiable);

        return (new MailMessage)->subject('Tài khoản hội viên Hội Doanh nhân trẻ Bắc Ninh')
            ->greeting('Kính gửi '.$notifiable->name.',')
            ->line('Doanh nghiệp đã được Trưởng ban Hội viên chuẩn y. Tài khoản đăng nhập của anh/chị là: '.$notifiable->email)
            ->action('Thiết lập mật khẩu', route('password.reset', ['token' => $token, 'email' => $notifiable->email]))
            ->line('Liên kết dùng một lần, có hiệu lực '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60).' phút. Nếu hết hạn, sử dụng chức năng Quên mật khẩu tại trang đăng nhập.');
    }
}
