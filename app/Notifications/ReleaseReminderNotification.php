<?php
namespace App\Notifications;

use App\Models\FormRelease;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReleaseReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly FormRelease $release) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $link = route('release.show', $this->release->public_token);

        return (new MailMessage)
            ->subject("Reminder: {$this->release->form->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("This is a reminder to complete the form: **{$this->release->form->title}**.")
            ->line("The deadline is **{$this->release->end_at->format('d M Y H:i')}**.")
            ->action('Fill Out Form', $link)
            ->line('Please submit before the deadline.');
    }
}
