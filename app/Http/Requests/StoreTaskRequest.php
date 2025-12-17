<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
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
        $projectId = $this->route('project')->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status_id' => [
                'required',
                'exists:task_statuses,id',
                Rule::exists('task_statuses', 'id')->where('project_id', $projectId),
            ],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'priority' => [
                'required',
                'string',
                Rule::in([Task::PRIORITY_LOW, Task::PRIORITY_NORMAL, Task::PRIORITY_HIGH, Task::PRIORITY_URGENT]),
            ],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'position' => ['nullable', 'integer', 'min:0'],
            'dependency_ids' => ['nullable', 'array'],
            'dependency_ids.*' => ['exists:tasks,id'],
        ];
    }
}
