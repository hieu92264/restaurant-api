<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');
        $roleId = is_object($role) ? $role->id : $role;

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($roleId)],
            'code' => ['sometimes', 'string', 'max:255', Rule::unique('roles', 'code')->ignore($roleId)],
            'remark' => ['nullable', 'string', 'max:255'],
        ];
    }
}
