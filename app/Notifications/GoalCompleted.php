<?php

namespace App\Notifications;

use App\Models\Goal;
use App\Notifications\Concerns\RespectsNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoalCompleted extends Notification
{
    use Queueable, RespectsNotificationPreferences;

    public function __construct(public Goal $goal) {}

    public function via(object $notifiable): array
    {
        return $this->enabledChannels($notifiable, 'goal_completion');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Goal reached: {$this->goal->name}")
            ->line("Congratulations! You've reached your goal \"{$this->goal->name}\".");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'goal_id' => $this->goal->id,
            'goal_name' => $this->goal->name,
            'message' => "You've reached your goal \"{$this->goal->name}\"!",
        ];
    }
}
