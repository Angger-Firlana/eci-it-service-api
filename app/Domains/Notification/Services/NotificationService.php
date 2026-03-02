<?php

namespace App\Domains\Notification\Services;

use App\Domains\Notification\Actions\CreateNotification;
use App\Domains\Notification\Actions\CreateNotificationForServiceRequest;
use App\Domains\Notification\Actions\ListNotifications;
use App\Domains\Notification\Actions\MarkNotificationRead;
use App\Models\Notification;
use App\Models\ServiceRequest;
use App\Models\Status;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class NotificationService
{
    public function __construct(
        protected ListNotifications $listNotifications,
        protected CreateNotification $createNotification,
        protected CreateNotificationForServiceRequest $createNotificationForServiceRequest,
        protected MarkNotificationRead $markNotificationRead
    ) {
    }

    public function getNotifications(Request $request): Collection
    {
        return $this->listNotifications->execute($request);
    }

    public function createNotification(array $data): Notification
    {
        return $this->createNotification->execute($data);
    }

    public function createNotificationForServiceRequest(ServiceRequest $serviceRequest, Status $status): void
    {
        $this->createNotificationForServiceRequest->execute($serviceRequest, $status);
    }

    public function markAsRead(int $id): ?Notification
    {
        return $this->markNotificationRead->execute($id);
    }
}
