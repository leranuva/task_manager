<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreFileAttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                File::types(['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'])
                    ->max(10240), // 10MB máximo
            ],
            'attachable_type' => ['required', 'string', 'in:App\Models\Task,App\Models\Project,App\Models\Comment'],
            'attachable_id' => ['required', 'integer'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Debes seleccionar un archivo.',
            'file.types' => 'El tipo de archivo no está permitido. Tipos permitidos: imágenes, PDF, documentos Office, texto, comprimidos.',
            'file.max' => 'El archivo no puede ser mayor a 10MB.',
        ];
    }
}
