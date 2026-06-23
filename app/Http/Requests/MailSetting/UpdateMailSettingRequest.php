<?php

namespace App\Http\Requests\MailSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMailSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mailer' => 'sometimes|string|max:50',
            'host' => 'required_if:is_active,true,1|nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:255',
            // Empty password keeps the previously stored one.
            'password' => 'nullable|string|max:1000',
            'encryption' => ['nullable', Rule::in(['tls', 'ssl'])],
            'from_address' => 'required_if:is_active,true,1|nullable|email|max:255',
            'from_name' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
