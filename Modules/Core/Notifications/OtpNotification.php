<?php

namespace Modules\Core\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Mailtrap\Helper\ResponseHelper;
use Mailtrap\MailtrapClient;
use Mailtrap\Mime\MailtrapEmail;
use Symfony\Component\Mime\Address;

class OtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $code,
        public string $type = 'login'
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'AliShaghaghi');
        $title = match($this->type) {
            'login' => 'کد ورود',
            'password_reset' => 'بازیابی رمز عبور',
            '2fa' => 'احراز هویت دو مرحله‌ای',
            default => 'کد تایید',
        };

        return (new MailMessage)
            ->subject("{$appName} - {$title}")
            ->greeting("سلام {$notifiable->name}!")
            ->line("کد تایید شما: **{$this->code}**")
            ->line("این کد تا ۵ دقیقه معتبر است.")
            ->line("اگر شما این درخواست را ارسال نکرده‌اید، این ایمیل را نادیده بگیرید.")
            ->action('ورود به پنل', url('/admin/login'))
            ->line("با تشکر، تیم {$appName}");
    }

    /**
     * Send email via Mailtrap API.
     */
    public static function sendViaMailtrap(object $notifiable, string $code, string $type = 'login'): bool
    {
        try {
            $apiKey = config('services.mailtrap.api_key');
            $fromEmail = config('services.mailtrap.from_email', 'hello@demomailtrap.co');
            $fromName = config('services.mailtrap.from_name', 'AliShaghaghi');
            $appName = config('app.name', 'AliShaghaghi');

            $title = match($type) {
                'login' => 'کد ورود',
                'password_reset' => 'بازیابی رمز عبور',
                '2fa' => 'احراز هویت دو مرحله‌ای',
                default => 'کد تایید',
            };

            $email = (new MailtrapEmail())
                ->from(new Address($fromEmail, $fromName))
                ->to(new Address($notifiable->email, $notifiable->name))
                ->subject("{$appName} - {$title}")
                ->category('OTP Verification')
                ->text("سلام {$notifiable->name}\n\nکد تایید شما: {$code}\n\nاین کد تا ۵ دقیقه معتبر است.\n\nاگر شما این درخواست را ارسال نکرده‌اید، این ایمیل را نادیده بگیرید.\n\nبا تشکر، تیم {$appName}");

            $response = MailtrapClient::initSendingEmails(
                apiKey: $apiKey
            )->send($email);

            $result = ResponseHelper::toArray($response);
            return isset($result['success']) && $result['success'] === true;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mailtrap email failed: ' . $e->getMessage());
            return false;
        }
    }
}