<?php

namespace App\Domains\ServiceRequest\Services;

use App\Domains\ServiceRequest\Actions\UpdateServiceRequestOperator;
use App\Domains\ServiceRequest\Actions\UpdateServiceRequestStatus;
use App\Domains\ServiceRequest\Actions\MarkDeviceAsBadAsset;
use App\Domains\Notification\Actions\CreateNotificationForServiceRequest;
use App\Domains\Notification\Actions\QueueStatusChangeNotificationEmail;
use App\Domains\ServiceRequest\Support\EnsureDeviceIsNotActiveInOtherRequest;
use App\Domains\ServiceRequest\Support\LoadRelations;
use App\Domains\ServiceRequest\Actions\WriteAuditLogs;
use App\Domains\ServiceRequest\DTOs\UpdateServiceRequestData;
use App\Domains\ServiceRequest\Actions\UpdateServiceRequestDetails;
use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;
use App\Models\Status;
use App\Models\Role;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\DB;

class UpdateServiceRequestWorkflow
{
    protected UpdateServiceRequestStatus $updateServiceRequestStatus;
    protected UpdateServiceRequestOperator $updateServiceRequestOperator;
    protected EnsureDeviceIsNotActiveInOtherRequest $ensureDeviceIsNotActiveInOtherRequest;
    protected LoadRelations $loadRelations;
    protected WriteAuditLogs $writeAuditLogs;
    protected UpdateServiceRequestDetails $updateServiceRequestDetails;
    protected MarkDeviceAsBadAsset $markDeviceAsBadAsset;
    protected CreateNotificationForServiceRequest $createNotificationForServiceRequest;
    protected QueueStatusChangeNotificationEmail $queueStatusChangeNotificationEmail;

    public function __construct(
        UpdateServiceRequestStatus $updateServiceRequestStatus,
        UpdateServiceRequestOperator $updateServiceRequestOperator,
        EnsureDeviceIsNotActiveInOtherRequest $ensureDeviceIsNotActiveInOtherRequest,
        LoadRelations $loadRelations,
        WriteAuditLogs $writeAuditLogs,
        UpdateServiceRequestDetails $updateServiceRequestDetails,
        MarkDeviceAsBadAsset $markDeviceAsBadAsset,
        CreateNotificationForServiceRequest $createNotificationForServiceRequest,
        QueueStatusChangeNotificationEmail $queueStatusChangeNotificationEmail,
    ) {
        $this->updateServiceRequestStatus = $updateServiceRequestStatus;
        $this->updateServiceRequestOperator = $updateServiceRequestOperator;
        $this->ensureDeviceIsNotActiveInOtherRequest = $ensureDeviceIsNotActiveInOtherRequest;
        $this->loadRelations = $loadRelations;
        $this->writeAuditLogs = $writeAuditLogs;
        $this->updateServiceRequestDetails = $updateServiceRequestDetails;
        $this->markDeviceAsBadAsset = $markDeviceAsBadAsset;
        $this->createNotificationForServiceRequest = $createNotificationForServiceRequest;
        $this->queueStatusChangeNotificationEmail = $queueStatusChangeNotificationEmail;
    }

    public function execute($id, UpdateServiceRequestData $data): ServiceRequest
    {
        return DB::transaction(function () use ($id, $data) {
            $serviceRequest = ServiceRequest::findOrFail($id);
            $details = $data->details ?? [];
            $newStatusId = $data->statusId ?? $serviceRequest->status_id;
            $operatorId = $data->operatorId;
            $logNotes = $data->logNotes;

            if (!empty($details)) {
                $this->ensureDeviceIsNotActiveInOtherRequest->execute($details, $serviceRequest->id);
                $this->updateServiceRequestDetails->execute($serviceRequest, $details);
            }

            if (auth()->user()->roles->contains('id', Role::OPERATOR)) {
                $resolvedOperatorId = $operatorId ?? auth()->user()->id;
                $this->updateServiceRequestOperator->execute($serviceRequest, $resolvedOperatorId);
            }

            $this->updateServiceRequestStatus->execute($serviceRequest, $newStatusId, $logNotes);
            $newStatus = Status::findOrFail($newStatusId);

            if ($newStatus->code === ServiceRequestStatusCode::BAD_ASSET->value) {
                $this->markDeviceAsBadAsset->execute($serviceRequest);
            }

            if (in_array($newStatus->code, [
                ServiceRequestStatusCode::COMPLETED->value,
                ServiceRequestStatusCode::BAD_ASSET->value,
                ServiceRequestStatusCode::CANCELLED->value,
            ], true)) {
                $this->createNotificationForServiceRequest->execute($serviceRequest, $newStatus);
            }

            $this->queueStatusChangeNotificationEmail->execute(
                $serviceRequest,
                $newStatus->code,
                $logNotes
            );

            return $this->loadRelations->execute($serviceRequest);
        });
    }
}
