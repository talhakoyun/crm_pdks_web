<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class ShiftDefinitionRequest extends FormRequest
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
            'start_time' => 'required|date_format:H:i|',
            'end_time' => 'required|date_format:H:i',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Shift adı girilmedi.',
            'title.min' => 'Shift adı en az 3 karakter olmalıdır.',
            'title.max' => 'Shift adı en fazla 255 karakter olmalıdır.',
            'start_time.required' => 'Başlangıç saati girilmedi.',
            'start_time.date_format' => 'Başlangıç saati geçersiz formatta.',
            'end_time.required' => 'Bitiş saati girilmedi.',
            'end_time.date_format' => 'Bitiş saati geçersiz formatta.',
        ];
    }
}
