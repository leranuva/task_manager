<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status_id' => [
                'sometimes',
                'required',
                'exists:task_statuses,id',
                Rule::exists('task_statuses', 'id')->where('project_id', $projectId),
            ],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority' => [
                'sometimes',
                'required',
                'string',
                Rule::in([Task::PRIORITY_LOW, Task::PRIORITY_NORMAL, Task::PRIORITY_HIGH, Task::PRIORITY_URGENT]),
            ],
            'due_date' => ['nullable', 'date'],
            'position' => ['nullable', 'integer', 'min:0'],
            'version' => ['nullable', 'integer'], // Timestamp para conflict resolution
        ];
    }
}
