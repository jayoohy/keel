<?php

namespace App\Notifications;

use App\Models\Allocation;
use App\Models\Goal;
use App\Notifications\Concerns\RespectsNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoalUpdated extends Notification
{
    use Queueable, RespectsNotificationPreferences;

    public function __construct(public Goal $goal, public Allocation $allocation) {}

    public function via(object $notifiable): array
    {
        return $this->enabledChannels($notifiable, 'goal_updates');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Progress update: {$this->goal->name}")
            ->line($this->message());
    }

    public function toArray(object $notifiable): array
    {
        return [
            'goal_id' => $this->goal->id,
            'goal_name' => $this->goal->name,
            'amount' => (string) $this->allocation->amount,
            'message' => $this->message(),
        ];
    }

    private function message(): string
    {
        return sprintf(
            'You allocated %s to %s. Current progress: %s%%.',
            number_format((float) $this->allocation->amount, 2),
            $this->goal->name,
            $this->goal->progressPercentage(),
        );
    }
}
