<?php

namespace App\Http\Requests\DeviceModel;

use Illuminate\Foundation\Http\FormRequest;

class PostDeviceModelRequest extends FormRequest
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
            'device_type_id' => 'required|exists:device_types,id',
            'brand' => 'required|string',
            'model' => 'required|string'
        ];
    }
}
