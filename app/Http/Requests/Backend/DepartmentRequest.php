<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
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
            'branch_id' => 'required|exists:branches,id',
            'title' => 'required|string|min:3|max:255',
            'manager_id' => 'required|exists:users,id',
            'is_active' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Şube seçilmedi.',
            'title.required' => 'Departman adı girilmedi.',
            'title.min' => 'Departman adı en az 3 karakter olmalıdır.',
            'title.max' => 'Departman adı en fazla 255 karakter olmalıdır.',
            'manager_id.required' => 'Yöneticisi seçilmedi.',
            'is_active.required' => 'Durum seçilmedi.',
        ];
    }
}
