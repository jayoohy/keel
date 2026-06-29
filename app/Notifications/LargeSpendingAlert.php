<?php

namespace App\Notifications;

use App\Models\Transaction;
use App\Notifications\Concerns\RespectsNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LargeSpendingAlert extends Notification
{
    use Queueable, RespectsNotificationPreferences;

    public function __construct(public Transaction $transaction) {}

    public function via(object $notifiable): array
    {
        return $this->enabledChannels($notifiable, 'large_spending_alerts');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Unusual transaction detected')
            ->line($this->message());
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'amount' => (string) $this->transaction->amount,
            'message' => $this->message(),
        ];
    }

    private function message(): string
    {
        return sprintf(
            'A %s of %s on %s is significantly higher than your recent average.',
            $this->transaction->type,
            number_format((float) $this->transaction->amount, 2),
            $this->transaction->transacted_at->format('M j, Y'),
        );
    }
}
