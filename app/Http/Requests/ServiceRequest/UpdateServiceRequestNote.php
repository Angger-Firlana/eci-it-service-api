<?php

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequestNote extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => 'required|string',
            'detail_id' => 'required|exists:service_request_details,id',
        ];
    }
}
