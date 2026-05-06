<?php
namespace App\Notifications;

use App\Models\ReleaseSet;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReleaseReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly ReleaseSet $releaseSet) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $link = route('release.show', $this->releaseSet->public_token);

        return (new MailMessage)
            ->subject("Reminder: {$this->releaseSet->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("This is a reminder to complete your pending forms for: **{$this->releaseSet->name}**.")
            ->line("The deadline is **{$this->releaseSet->end_at->format('d M Y H:i')}**.")
            ->action('Fill Out Forms', $link)
            ->line('Please submit before the deadline.');
    }
}
