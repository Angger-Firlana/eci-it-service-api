<?php

namespace App\Domains\Notification\Actions;

use App\Models\Notification;

class MarkNotificationRead
{
    public function execute(int $id): ?Notification
    {
        $notification = Notification::find($id);
        if ($notification && $notification->user_id === auth()->id()) {
            $notification->read_at = now();
            $notification->save();
        }

        return $notification;
    }
}
