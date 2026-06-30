<?php

namespace App\Http\Requests\SetEmailIt;

use Illuminate\Foundation\Http\FormRequest;

class StoreSetEmailItRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id', 'unique:set_email_it,user_id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
