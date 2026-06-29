<?php

namespace App\Notifications;

use App\Models\Allocation;
use App\Models\Rule;
use App\Notifications\Concerns\RespectsNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RuleExecuted extends Notification
{
    use Queueable, RespectsNotificationPreferences;

    public function __construct(public Rule $rule, public Allocation $allocation) {}

    public function via(object $notifiable): array
    {
        return $this->enabledChannels($notifiable, 'rule_executions');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Automation fired: {$this->rule->name}")
            ->line($this->message());
    }

    public function toArray(object $notifiable): array
    {
        return [
            'rule_id' => $this->rule->id,
            'allocation_id' => $this->allocation->id,
            'message' => $this->message(),
        ];
    }

    private function message(): string
    {
        return sprintf(
            'Rule "%s" allocated %s to %s.',
            $this->rule->name,
            number_format((float) $this->allocation->amount, 2),
            $this->rule->goal->name,
        );
    }
}
