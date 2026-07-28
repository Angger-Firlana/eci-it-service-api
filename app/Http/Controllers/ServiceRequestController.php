<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\APIResponse;
use App\Http\Requests\ServiceRequest\StoreServiceRequest;
use App\Domains\ServiceRequest\Services\UpdateServiceRequestWorkflow;
use App\Domains\ServiceRequest\Services\CreateServiceRequestWorkflow;
use App\Domains\ServiceRequest\Actions\GetAllowedTransitions;
use App\Domains\ServiceRequest\Actions\GetServiceRequest;
use App\Domains\ServiceRequest\Actions\DeleteServiceRequest;
use App\Http\Requests\ServiceRequest\UpdateServiceRequest;
use App\Http\Requests\ServiceRequest\UpdateServiceRequestNote;
use App\Http\Requests\ServiceRequest\UpdateServiceRequestSolution;
use App\Models\ServiceRequestDetail;

class ServiceRequestController extends Controller
{
    protected DeleteServiceRequest $deleteServiceRequest;
    protected UpdateServiceRequestWorkflow $updateServiceRequestWorkFlow;
    protected CreateServiceRequestWorkflow $createServiceRequestWorkflow;
    protected GetServiceRequest $getServiceRequest;
    protected GetAllowedTransitions $getAllowedTransitions;

    public function __construct(
        CreateServiceRequestWorkflow $createServiceRequestWorkflow,
        UpdateServiceRequestWorkflow $updateServiceRequestWorkFlow,
        GetServiceRequest $getServiceRequest,
        GetAllowedTransitions $getAllowedTransitions,
        DeleteServiceRequest $deleteServiceRequest
    ){
        $this->getServiceRequest = $getServiceRequest;
        $this->updateServiceRequestWorkFlow = $updateServiceRequestWorkFlow;
        $this->createServiceRequestWorkflow = $createServiceRequestWorkflow;
        $this->getAllowedTransitions = $getAllowedTransitions;
        $this->deleteServiceRequest = $deleteServiceRequest;
    }

    public function index(Request $request){
        $paginator = $this->getServiceRequest->getAllServiceRequest($request);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);
        return APIResponse::success($data, 200, "Success", $meta);
    }

    public function summary(Request $request){
        $paginator = $this->getServiceRequest->getAllServiceRequestSummary($request);
        $data = $paginator->items();
        $meta = APIResponse::formatPagination($paginator);
        return APIResponse::success($data, 200, "Success", $meta);
    }

    public function show($id){
        $serviceRequests = $this->getServiceRequest->getServiceRequestById((int) $id);
        return APIResponse::success($serviceRequests);
    }

    public function store(StoreServiceRequest $request){
        $serviceRequests = $this->createServiceRequestWorkflow->execute($request->validated());
        return APIResponse::success($serviceRequests);
    }

    public function update(UpdateServiceRequest $request, $id){
        $dataDto = new \App\Domains\ServiceRequest\DTOs\UpdateServiceRequestData(
            $request->input('details', []),
            $request->input('status_id', null),
            $request->input('operator_id', null),
            $request->input('log_notes', null)
        );
        $serviceRequests = $this->updateServiceRequestWorkFlow->execute($id, $dataDto);
        return APIResponse::success($serviceRequests);
    }

    public function destroy($id){
        $serviceRequests = $this->deleteServiceRequest->execute((int) $id);
        return APIResponse::success($serviceRequests);
    }

    public function updateNote(UpdateServiceRequestNote $request, $id) {
        $detail = ServiceRequestDetail::where('service_request_id', $id)
            ->findOrFail($request->input('detail_id'));
        $detail->update(['complaint' => $request->input('note')]);
        return APIResponse::success($detail, 200, 'Keterangan berhasil diupdate');
    }

    public function updateSolution(UpdateServiceRequestSolution $request, $id) {
        $detail = ServiceRequestDetail::where('service_request_id', $id)
            ->findOrFail($request->input('detail_id'));
        $detail->update(['solution' => $request->input('solution')]);
        return APIResponse::success($detail, 200, 'Solusi berhasil diupdate');
    }

    public function allowedTransitions($id) {
        $transitions = $this->getAllowedTransitions->execute((int) $id);
        return APIResponse::success($transitions);
    }

    public function stats() {
        $stats = $this->getServiceRequest->getStats();
        return APIResponse::success($stats);
    }
}
