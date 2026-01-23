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
            //
            'admin_id' => 'required|exists:users,id',
            'user_id' => 'sometimes|exists:users,id',
            'service_type_id' => 'sometimes|exists:service_types,id',
            'request_date' => 'required|date',
            'status_id'  => 'required|exists:statuses,id',
            'details' => 'required|array',
            'details.*.device_id' =>  'required|exists:devices,id',
            'details.*.complaint' => 'required|string',
            'details.*.complaint_images' => 'sometimes|array',
            'details.*.complaint_images.*' => 'sometimes|file|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ];
    }
}
