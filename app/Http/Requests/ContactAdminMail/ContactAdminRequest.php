<?php

namespace App\Http\Requests\ContactAdminMail;

use Illuminate\Foundation\Http\FormRequest;

class ContactAdminRequest extends FormRequest
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
            'name' => 'required|string',
            'email' => 'required|email',
            'message' => 'required|string',
            'attachmentPath' => 'nullable|string',
            'mode' => 'sometimes|in:queue,sync',

            // Optional service request context (supports both snake_case & camelCase payloads)
            'device' => 'nullable|string|max:255',
            'device_model' => 'nullable|string|max:255',
            'deviceModel' => 'nullable|string|max:255',
            'damages' => 'nullable|array',
            'damages.*' => 'string|max:255',
            'service_request_id' => 'nullable|integer',
            'serviceRequestId' => 'nullable|integer',
            'service_request_url' => 'nullable|url',
            'serviceRequestUrl' => 'nullable|url',
        ];
    }
}
