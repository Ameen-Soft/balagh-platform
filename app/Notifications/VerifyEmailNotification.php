<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('منصة بادر - التحقق من البريد الإلكتروني')
            ->greeting('مرحباً بك '.$notifiable->name.' في منصة بادر!')
            ->line('نشكرك على انضمامك إلى منصة بادر الوطنية لإدارة البلاغات ومتابعة المشاريع التنموية.')
            ->line('يرجى الضغط على الزر أدناه لتأكيد صحة بريدك الإلكتروني وتفعيل حسابك بالكامل:')
            ->action('التحقق من بريدي الإلكتروني', $verificationUrl)
            ->line('رابط التحقق هذا صالح لمدة 60 دقيقة فقط.')
            ->line('إذا لم تقم بإنشاء حساب في منصة بادر، فيمكنك تجاهل هذه الرسالة بأمان.')
            ->salutation('مع أطيب التحيات، فريق منصة بادر');
    }

    /**
     * Generate the verification URL for the given notifiable.
     */
    protected function verificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
