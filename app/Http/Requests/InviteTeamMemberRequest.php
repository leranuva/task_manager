<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $team = $this->route('team');
        return $this->user()->can('manageMembers', $team);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'in:admin,member,viewer'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'El rol debe ser: admin, member o viewer.',
        ];
    }
}
