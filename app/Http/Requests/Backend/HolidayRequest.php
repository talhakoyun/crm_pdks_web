<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class HolidayRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'type' => 'required|exists:holiday_type,id',
            'status' => 'required|in:pending,approved,rejected',
            'note' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Personel seçilmedi.',
            'user_id.exists' => 'Personel bulunamadı.',
            'start_date.required' => 'Başlangıç tarihi seçilmedi.',
            'end_date.required' => 'Bitiş tarihi seçilmedi.',
            'type.required' => 'İzin tipi seçilmedi.',
            'type.exists' => 'İzin tipi bulunamadı.',
            'status.required' => 'Durum seçilmedi.',
            'status.in' => 'Geçersiz durum seçildi.',
            'note.nullable' => 'Not girilmedi.',
        ];
    }
}
