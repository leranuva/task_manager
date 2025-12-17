<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveTaskRequest extends FormRequest
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
        $task = $this->route('task');
        $projectId = $task->project_id;

        return [
            'status_id' => [
                'required',
                'exists:task_statuses,id',
                Rule::exists('task_statuses', 'id')->where('project_id', $projectId),
            ],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status_id.required' => 'Debes seleccionar un estado para la tarea.',
            'status_id.exists' => 'El estado seleccionado no existe o no pertenece a este proyecto.',
        ];
    }
}
