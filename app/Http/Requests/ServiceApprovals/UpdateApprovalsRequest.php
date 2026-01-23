<?php

namespace App\Http\Requests\ServiceApprovals;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApprovalsRequest extends FormRequest
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
            '*.approval_policy_id' => 'sometimes|exists:approval_policies,id',
            '*.approval_policy_step_id' => 'sometimes|exists:approval_policy_steps,id',
            '*.assigned_by' => 'sometimes|exists:users,id',
            '*.approver_id' => 'sometimes|exists:users,id',
            '*.approved_at' => 'sometimes|date',
            '*.status_id' => 'sometimes|exists:statuses,id'
        ];
    }
}
