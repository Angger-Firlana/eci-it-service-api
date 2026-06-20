<?php

namespace App\Http\Requests\MailSetting;

use Illuminate\Foundation\Http\FormRequest;

class TestMailSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to' => 'required|email',
        ];
    }
}
