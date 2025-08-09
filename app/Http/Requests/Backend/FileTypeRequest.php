<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class FileTypeRequest extends FormRequest
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
            'name' => 'required|string|min:2|max:255',
            'allowed_extensions' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Dosya tipi adı zorunludur.',
            'name.string' => 'Dosya tipi adı metin olmalıdır.',
            'name.min' => 'Dosya tipi adı en az 2 karakter olmalıdır.',
            'name.max' => 'Dosya tipi adı en fazla 255 karakter olmalıdır.',
            'allowed_extensions.string' => 'İzin verilen uzantılar metin olmalıdır.',
            'allowed_extensions.max' => 'İzin verilen uzantılar en fazla 255 karakter olmalıdır.',
            'description.string' => 'Açıklama metin olmalıdır.',
            'description.max' => 'Açıklama en fazla 1000 karakter olmalıdır.',
            'is_active.boolean' => 'Durum değeri geçerli değil.',
        ];
    }
}
