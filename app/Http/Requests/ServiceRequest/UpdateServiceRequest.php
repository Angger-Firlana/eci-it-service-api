<?php

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            //
            'admin_id' => 'sometimes|exists:users,id',
            'user_id' => 'sometimes|exists:users,id',
            'service_type_id' => 'sometimes|exists:users,id',
            'request_date' => 'sometimes|date',
            'estimated_date' => 'sometimes|date',
            'status_id'  => 'sometimes|exists:statuses,id',
            'details' => 'sometimes|array',
            'details.*.id' => 'sometimes|exists:service_request_details,id',
            'details.*.device_id' =>  'sometimes|exists:devices,id',
            'details.*.complaint' => 'sometimes|string',
            'details.*.complaint_images' => 'sometimes|array',
            'details.*.complaint_images.*' => 'sometimes|file|mimes:jpeg,png,jpg,gif,svg|max:2048',

            // Location and vendor
            'service_location' => 'sometimes|array',
            'service_location.location_type' => 'sometimes|in:internal,external',
            'service_location.vendor_id' => 'sometimes|exists:vendors,id',
            'service_location.is_active' => 'sometimes|boolean',

            'service_costs' => 'sometimes|array',
            'service_costs.*.cost_type_id' => 'sometimes|exists:cost_types,id',
            'service_costs.*.amount' => 'sometimes|numeric',
            'service_costs.*.description' => 'sometimes|string',

            'service_cancellation' => 'sometimes|array',
            'service_cancellation.reason' => 'sometimes|string',
            'service_cancellation.canceled_by' => 'sometimes|exists:users,id',

            

            'log_notes' => 'sometimes|string'
        ];
    }
}
