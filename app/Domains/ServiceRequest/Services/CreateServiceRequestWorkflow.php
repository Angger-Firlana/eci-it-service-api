<?php

namespace App\Domains\ServiceRequest\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Status;

use App\Domains\ServiceRequest\Actions\WriteAuditLogs;

use App\Domains\ServiceRequest\Actions\CreateMainServiceRequest;
use App\Domains\ServiceRequest\Actions\CreateServiceRequestDetails;

use App\Domains\ServiceRequest\Support\EnsureDeviceIsNotActiveInOtherRequest;
use App\Domains\ServiceRequest\Support\LoadRelations;

use App\Domains\ServiceRequest\Enums\ServiceRequestStatusCode;

use App\Domains\ContactAdmin\Services\ContactAdminService;

class CreateServiceRequestWorkflow{
    protected EnsureDeviceIsNotActiveInOtherRequest $ensureDeviceIsNotActiveInOtherRequest;
    protected WriteAuditLogs $writeAuditLogs;
    protected CreateServiceRequestDetails $createServiceRequestDetails;
    protected CreateMainServiceRequest $createMainServiceRequest;
    protected LoadRelations $loadRelations;
    protected ContactAdminService $contactAdminService;

    public function __construct(
        EnsureDeviceIsNotActiveInOtherRequest $ensureDeviceIsNotActiveInOtherRequest,
        WriteAuditLogs $writeAuditLogs,
        CreateServiceRequestDetails $createServiceRequestDetails,
        CreateMainServiceRequest $createMainServiceRequest,
        LoadRelations $loadRelations,
        ContactAdminService $contactAdminService
    ) {
        $this->ensureDeviceIsNotActiveInOtherRequest = $ensureDeviceIsNotActiveInOtherRequest;
        $this->writeAuditLogs = $writeAuditLogs;
        $this->createServiceRequestDetails = $createServiceRequestDetails;
        $this->createMainServiceRequest = $createMainServiceRequest;
        $this->loadRelations = $loadRelations;
        $this->contactAdminService = $contactAdminService;
    }

    public function execute($data){
        $this->ensureDeviceIsNotActiveInOtherRequest->execute($data['details'] ?? []);

        return DB::transaction(function () use ($data) {
            $serviceRequest = $this->createMainServiceRequest->execute($data);
            $this->createServiceRequestDetails->execute($serviceRequest, $data['details'] ?? []);
            
            $status = Status::where('code', ServiceRequestStatusCode::REVIEW_IN_WORKSHOP)->first();
            $this->writeAuditLogs->execute($serviceRequest, $status, 'CREATE_REQUEST', 'Request dibuat');

            $actor = Auth::user();

            $this->contactAdminService->sendAdminNotification($serviceRequest->id, $actor->name, $actor->email);

            return $this->loadRelations->execute($serviceRequest);
        });
    }
}
