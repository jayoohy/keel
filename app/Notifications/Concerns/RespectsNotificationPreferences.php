<?php

namespace App\Notifications\Concerns;

use App\Models\User;

trait RespectsNotificationPreferences
{
    /**
     * Returns the channels to notify through, or an empty array if the user
     * has opted out of this notification type (see Settings\PreferencesController).
     */
    protected function enabledChannels(User $notifiable, string $preferenceKey): array
    {
        $preferences = $notifiable->notification_preferences ?? [];

        if (($preferences[$preferenceKey] ?? true) === false) {
            return [];
        }

        return ['database', 'mail'];
    }
}
