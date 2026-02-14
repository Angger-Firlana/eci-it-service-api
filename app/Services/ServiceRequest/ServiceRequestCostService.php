<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceCost;
use App\Models\ServiceRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceRequestCostService
{
    public function addCost(int $serviceRequestId, array $data): ServiceCost
    {

        $data['service_request_id'] = $serviceRequestId;

        if(isset($data['image'])){
            $data['image_path'] =  $this->storeCostAttachment($serviceRequestId, $data['image']);
        }

        $serviceCost = ServiceCost::create($data);

        return $serviceCost->load('cost_type');
    }

    public function updateCost($serviceRequestId,int $costId, array $data): ServiceCost
    {
        $cost = ServiceCost::findOrFail($costId);
        if($serviceRequestId != $cost->service_request_id){
            throw new \Exception('Service request id not match');
        }

        if(isset($data['image'])){
            $data['image_path'] =  $this->storeCostAttachment($serviceRequestId, $data['image']);
        }

        $cost->update([
            'cost_type_id' => $data['cost_type_id'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'image_path' => $data['image_path'] ?? null,
        ]);

        return $cost->load('cost_type');
    }

    public function removeCost($serviceRequestId, int $costId): void
    {
        $cost = ServiceCost::findOrFail($costId);
        if($serviceRequestId !== $cost->service_request_id){
            throw new \Exception('Service request id not match');
        }
        $cost->delete();
    }

    public function getCostsByRequest(int $serviceRequestId)
    {
        return ServiceCost::where('service_request_id', $serviceRequestId)
            ->with('cost_type')
            ->get();
    }

    public function getAttachment(int $serviceRequestId, int $costId)
    {
        $cost = ServiceCost::findOrFail($costId);
        if ($serviceRequestId != $cost->service_request_id) {
            throw new \Exception('Service request id not match');
        }

        $path = $cost->image_path;
        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'Attachment not found');
        }

        return response()->file(public_path('images/' . $path));
    }

    private function storeCostAttachment(int $serviceRequestId, UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Ymd_His');
        $random = Str::lower(Str::random(6));
        $filename = "sr{$serviceRequestId}_receipt_{$timestamp}_{$random}.{$extension}";

        $file->move(public_path('images/'), $filename);

        return $filename;
    }
}
