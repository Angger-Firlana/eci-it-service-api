<?php

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'admin_id' => 'required|exists:users,id',
            'user_id' => 'sometimes|exists:users,id',
            'request_date' => 'required|date',
            'status_id'  => 'required|exists:statuses,id',
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
        // Debug: Log incoming data
        \Log::info('Request data:', $this->all());
        \Log::info('Files:', $this->allFiles());
        
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
