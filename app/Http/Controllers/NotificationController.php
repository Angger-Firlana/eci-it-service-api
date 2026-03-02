<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Notification\Services\NotificationService;
use App\Helpers\APIResponse;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    //
    public function index(Request $request){
        $notifications = $this->notificationService->getNotifications($request);
        return APIResponse::success($notifications, 200, "Success");
    }

    public function markAsRead(Request $request, int $id){
        $notification = $this->notificationService->markAsRead($id);
        return APIResponse::success($notification);
    }
}
