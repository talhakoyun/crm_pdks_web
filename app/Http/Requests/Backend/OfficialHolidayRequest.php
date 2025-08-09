<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class OfficialHolidayRequest extends FormRequest
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
            'start_date' => 'required|string',
            'end_date' => 'required|string',
            'type_id' => 'required|exists:official_types,id',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tatil günü başlığı girilmesi zorunludur.',
            'title.min' => 'Tatil günü başlığı en az 3 karakter olmalıdır.',
            'title.max' => 'Tatil günü başlığı en fazla 255 karakter olmalıdır.',
            'start_date.required' => 'Başlangıç tarihi girilmesi zorunludur.',
            'start_date.string' => 'Başlangıç tarihi geçerli bir format olmalıdır.',
            'end_date.required' => 'Bitiş tarihi girilmesi zorunludur.',
            'end_date.string' => 'Bitiş tarihi geçerli bir format olmalıdır.',
            'type_id.required' => 'Tatil türü seçilmesi zorunludur.',
            'type_id.exists' => 'Seçilen tatil türü geçerli değil.',
            'description.max' => 'Açıklama en fazla 1000 karakter olmalıdır.',
        ];
    }
}
