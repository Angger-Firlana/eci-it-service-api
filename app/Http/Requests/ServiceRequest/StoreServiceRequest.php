<?php

namespace App\Http\Requests\ServiceRequest;

use App\Models\EntityType;
use App\Models\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $serviceRequestEntityTypeId = EntityType::where('code', 'SERVICE_REQUEST')->value('id');

        return [
            'admin_id' => 'required|exists:users,id',
            'user_id' => 'sometimes|exists:users,id',
            'request_date' => 'required|date',
            'status_id'  => 'required_without:status_code|exists:statuses,id',
            'status_code'  => [
                'required_without:status_id',
                'string',
                Rule::exists('statuses', 'code')->where('entity_type_id', $serviceRequestEntityTypeId),
            ],
            'details' => 'required|array',
            'details.*.service_type_id' => 'required|exists:service_types,id',
            'details.*.device_id' => 'sometimes|exists:devices,id',
            'details.*.device_type_id' =>  'required_without:details.*.device_id|exists:device_types,id',
            'details.*.brand' => 'required_without:details.*.device_id|string',
            'details.*.model' => 'required_without:details.*.device_id|string',
            'details.*.serial_number' => 'required_without:details.*.device_id|string',
            'details.*.complaint' => 'required|string',
            'details.*.complaint_images' => 'sometimes|array',
            'details.*.complaint_images.*' => 'sometimes|file|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->filled('status_code') && !$this->filled('status_id')) {
            $statusId = Status::query()
                ->where('code', $this->input('status_code'))
                ->whereHas('entity_type', function ($query) {
                    $query->where('code', 'SERVICE_REQUEST');
                })
                ->value('id');

            if ($statusId) {
                $this->merge(['status_id' => $statusId]);
            }
        }

        // Ensure complaint_images is properly formatted
        $details = $this->input('details', []);
        
        foreach ($details as $key => $detail) {
            if (isset($detail['complaint_images'])) {
                // Handle both array and single file cases
                if (!is_array($detail['complaint_images'])) {
                    $details[$key]['complaint_images'] = [$detail['complaint_images']];
                }
            }
        }
        
        $this->merge(['details' => $details]);
    }
}
