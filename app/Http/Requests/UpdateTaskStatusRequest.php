<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el controller con Policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $taskStatus = $this->route('taskStatus');
        $projectId = $taskStatus->project_id;

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('task_statuses')->where(function ($query) use ($projectId) {
                    return $query->where('project_id', $projectId);
                })->ignore($taskStatus->id),
            ],
            'color' => ['sometimes', 'required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_default' => ['sometimes', 'boolean'],
            'is_final' => ['sometimes', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
