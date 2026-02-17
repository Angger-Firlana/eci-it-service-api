<?php 

namespace App\Services\Notification;

use App\Models\Notification;
use Illuminate\Support\Collection;
use App\Models\ServiceRequest;
use App\Models\Status;
use Illuminate\Http\Request;
use App\Enums\ServiceRequestStatusCode;

class NotificationService
{
    public function getNotifications(Request $request): Collection
    {
        $query = Notification::with('service_request')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc');

        return $query->get();
    }

    public function createNotification(array $data): Notification{
        $notification = Notification::create([
            'user_id' => $data['user_id'],
            'service_request_id' => $data['service_request_id'] ?? null,
            'title' => $data['title'],
            'message' => $data['message'],
            'read_at' => null
        ]);

        return $notification;
    }

    public function createNotificationForServiceRequest(ServiceRequest $serviceRequest, Status $status){
        $data = [
                    'service_request_id' => $serviceRequest->id,
                    'status_code' => $status->code,
                    'user_id' => $serviceRequest->user_id,
                ];

        if($status->code === ServiceRequestStatusCode::COMPLETED->value){
            $data['title'] = "Service Request #{$serviceRequest->service_number} Completed";
            $data['message'] = "Your service request #{$serviceRequest->service_number} has been completed. Please check the details for more information.";
        } elseif($status->code === ServiceRequestStatusCode::BAD_ASSET->value){
            $data['title'] = "Service Request #{$serviceRequest->service_number} Marked as Bad Asset";
            $data['message'] = "Your service request #{$serviceRequest->service_number} has been marked as bad asset. Please contact support for more information.";
        } elseif($status->code === ServiceRequestStatusCode::CANCELLED->value){
            $data['title'] = "Service Request #{$serviceRequest->service_number} Cancelled";
            $data['message'] = "Your service request #{$serviceRequest->service_number} has been cancelled. Please contact support for more information.";
        }

        $this->createNotification($data);
    }

    public function markAsRead($id)
    {
        $notification = Notification::find($id);
        if ($notification && $notification->user_id === auth()->id()) {
            $notification->read_at = now();
            $notification->save();
        }

        return $notification;
    }
}
