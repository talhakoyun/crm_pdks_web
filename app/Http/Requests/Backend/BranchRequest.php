<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
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
            'title' => 'required|string|min:3|max:255',
            'phone_number' => 'required|min:15|max:15',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'required|boolean',
            'positions' => 'nullable|string', // JSON formatındaki konum bilgisi
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Şube adı girilmedi.',
            'title.min' => 'Şube adı en az 3 karakter olmalıdır.',
            'title.max' => 'Şube adı en fazla 255 karakter olmalıdır.',
            'phone_number.required' => 'Telefon numarası girilmedi.',
            'phone_number.min' => 'Telefon numarası en az 15 karakter olmalıdır.',
            'phone_number.max' => 'Telefon numarası en fazla 15 karakter olmalıdır.',
            'address.max' => 'Adres en fazla 500 karakter olmalıdır.',
            'description.max' => 'Açıklama en fazla 1000 karakter olmalıdır.',
            'is_active.required' => 'Durum seçilmedi.',
            'is_active.boolean' => 'Durum değeri geçerli değil.',
        ];
    }
}
