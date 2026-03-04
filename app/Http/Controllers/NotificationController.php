<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Domains\Notification\Actions\ListNotifications;
use App\Domains\Notification\Actions\MarkNotificationRead;
use App\Helpers\APIResponse;

class NotificationController extends Controller
{
    protected ListNotifications $listNotifications;
    protected MarkNotificationRead $markNotificationRead;

    public function __construct(
        ListNotifications $listNotifications,
        MarkNotificationRead $markNotificationRead
    )
    {
        $this->listNotifications = $listNotifications;
        $this->markNotificationRead = $markNotificationRead;
    }
    //
    public function index(Request $request){
        $notifications = $this->listNotifications->execute($request);
        return APIResponse::success($notifications, 200, "Success");
    }

    public function markAsRead(Request $request, int $id){
        $notification = $this->markNotificationRead->execute($id);
        return APIResponse::success($notification);
    }
}
