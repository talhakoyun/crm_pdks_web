<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class DebitDeviceRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'serial_number' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Zimmet Cihazı Adı zorunludur.',
            'name.string' => 'Zimmet Cihazı Adı metin olmalıdır.',
            'name.max' => 'Zimmet Cihazı Adı en fazla 255 karakter olmalıdır.',
            'serial_number.required' => 'Seri Numarası zorunludur.',
            'serial_number.string' => 'Seri Numarası metin olmalıdır.',
            'serial_number.max' => 'Seri Numarası en fazla 255 karakter olmalıdır.',
            'brand.required' => 'Marka zorunludur.',
            'brand.string' => 'Marka metin olmalıdır.',
            'brand.max' => 'Marka en fazla 255 karakter olmalıdır.',
            'model.required' => 'Model zorunludur.',
            'model.string' => 'Model metin olmalıdır.',
            'model.max' => 'Model en fazla 255 karakter olmalıdır.',
            'description.string' => 'Açıklama metin olmalıdır.',
        ];
    }
}
