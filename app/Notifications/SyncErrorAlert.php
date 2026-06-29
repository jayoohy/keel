<?php

namespace App\Notifications;

use App\Models\BankConnection;
use App\Notifications\Concerns\RespectsNotificationPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SyncErrorAlert extends Notification
{
    use Queueable, RespectsNotificationPreferences;

    public function __construct(public BankConnection $bankConnection, public string $errorMessage) {}

    public function via(object $notifiable): array
    {
        return $this->enabledChannels($notifiable, 'sync_errors');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('We had trouble syncing your bank account')
            ->line("Syncing \"{$this->bankConnection->institution_name}\" failed: {$this->errorMessage}");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'bank_connection_id' => $this->bankConnection->id,
            'institution_name' => $this->bankConnection->institution_name,
            'message' => "Syncing \"{$this->bankConnection->institution_name}\" failed: {$this->errorMessage}",
        ];
    }
}
