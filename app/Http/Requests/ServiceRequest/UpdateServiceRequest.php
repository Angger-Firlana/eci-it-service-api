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
            'request_date' => 'sometimes|date',
            'estimated_date' => 'sometimes|date',
            'status_code'  => 'sometimes|exists:statuses,code',
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
}
