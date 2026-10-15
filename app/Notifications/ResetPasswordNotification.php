<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    use Queueable;

    public function __construct($token)
    {
        parent::__construct($token);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $email = $notifiable->getEmailForPasswordReset();
        $resetUrl = url('/api/v1/auth/reset-password-page?token=' . $this->token . '&email=' . urlencode($email));
        $deepLinkUrl = 'bader://reset-password?token=' . $this->token . '&email=' . urlencode($email);

        return (new MailMessage)
            ->subject('منصة بادر - إعادة تعيين كلمة المرور')
            ->greeting('مرحباً بك ' . $notifiable->name . '،')
            ->line('تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك في منصة بادر.')
            ->line('يمكنك النقر على الزر أدناه لإعادة تعيين كلمة المرور عبر الصفحة الرسمية أو فتحها مباشرة في التطبيق:')
            ->action('إعادة تعيين كلمة المرور', $resetUrl)
            ->line('رمز التحقق الخاص بك (Token): ' . $this->token)
            ->line('ينتهي صلاحية هذا الرابط بعد 60 دقيقة.')
            ->line('إذا كان تطبيق بادر مثبتاً على هاتفك، يمكنك أيضاً استخدام الرابط المباشر للتطبيق: ' . $deepLinkUrl)
            ->line('إذا لم تطلب إعادة تعيين كلمة المرور، فلا داعي لاتخاذ أي إجراء، حسابك آمن.')
            ->salutation('مع أطيب التحيات، فريق منصة بادر');
    }
}
