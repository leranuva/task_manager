<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        return $this->user()->can('manageMembers', $project);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:admin,editor,viewer'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'El rol debe ser: admin, editor o viewer.',
        ];
    }
}
