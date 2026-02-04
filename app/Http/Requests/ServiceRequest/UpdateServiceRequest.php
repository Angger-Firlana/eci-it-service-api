<?php

namespace App\Http\Requests\ServiceRequest;

use App\Models\EntityType;
use App\Models\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
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
            //
            'admin_id' => 'sometimes|exists:users,id',
            'user_id' => 'sometimes|exists:users,id',
            'request_date' => 'sometimes|date',
            'estimated_date' => 'sometimes|date',
            'status_id'  => 'sometimes|exists:statuses,id',
            'status_code'  => [
                'sometimes',
                'string',
                Rule::exists('statuses', 'code')->where('entity_type_id', $serviceRequestEntityTypeId),
            ],
            'details' => 'sometimes|array',
            'details.*.id' => 'sometimes|exists:service_request_details,id',
            'details.*.service_type_id' => 'sometimes|exists:service_types,id',
            'details.*.device_id' =>  'sometimes|exists:devices,id',
            'details.*.device_type_id' =>  'sometimes|exists:device_types,id',
            'details.*.brand' => 'sometimes|string',
            'details.*.model' => 'sometimes|string',
            'details.*.serial_number' => 'sometimes|string',
            'details.*.complaint' => 'sometimes|string',
            'details.*.complaint_images' => 'sometimes|array',
            'details.*.complaint_images.*' => 'sometimes|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'log_notes' => 'sometimes|string'
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
    }
}
