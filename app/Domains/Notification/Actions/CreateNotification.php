<?php

namespace App\Domains\Notification\Actions;

use App\Models\Notification;

class CreateNotification
{
    public function execute(array $data): Notification
    {
        return Notification::create([
            'user_id' => $data['user_id'],
            'service_request_id' => $data['service_request_id'] ?? null,
            'title' => $data['title'],
            'message' => $data['message'],
            'read_at' => null,
        ]);
    }
}
