<?php

namespace App\Domains\Notification\Actions;

use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class ListNotifications
{
    public function execute(Request $request): Collection
    {
        return Notification::with('service_request')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
