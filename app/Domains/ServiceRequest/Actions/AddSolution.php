<?php

namespace App\Domains\ServiceRequest\Actions;

use App\Domains\ServiceRequest\LoadRelations;
use App\Models\ServiceRequest;

class AddSolution{
    protected LoadRelations $loadRelations;

    public function __construct(LoadRelations $loadRelations)
    {
        $this->loadRelations = $loadRelations;
    }

    public function addSolution(int $id, array $data): ServiceRequest
    {
        $serviceRequest = ServiceRequest::findOrFail($id);
        
        $serviceRequest->serviceRequestDetails()->update([
            'solution' => $data['solution'],
        ]);
        
        return $this->loadRelations->execute($serviceRequest);
    }
}