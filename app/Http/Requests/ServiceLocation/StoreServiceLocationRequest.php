<?php

namespace App\Http\Requests\ServiceLocation;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceLocationRequest extends FormRequest
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
            'location_type' => 'required|in:internal,external',
            'vendor_id' => 'required_if:location_type,external|exists:vendors,id',
            'is_active' => 'required|boolean',
            'address' => 'required_if:location_type,external|string',
            'phone_number' => 'required_if:location_type,external|string'
        ];
    }
}
