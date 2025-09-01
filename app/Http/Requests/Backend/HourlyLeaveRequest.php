<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class HourlyLeaveRequest extends FormRequest
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
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|exists:holiday_type,id',
            'status' => 'required|in:pending,approved,rejected',
            'reason' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Personel seçilmedi.',
            'user_id.exists' => 'Personel bulunamadı.',
            'date.required' => 'Tarih seçilmedi.',
            'start_time.required' => 'Başlangıç saati seçilmedi.',
            'start_time.date_format' => 'Başlangıç saati geçerli formatta değil.',
            'end_time.required' => 'Bitiş saati seçilmedi.',
            'end_time.date_format' => 'Bitiş saati geçerli formatta değil.',
            'end_time.after' => 'Bitiş saati başlangıç saatinden sonra olmalıdır.',
            'type.required' => 'İzin tipi seçilmedi.',
            'type.exists' => 'İzin tipi bulunamadı.',
            'status.required' => 'Durum seçilmedi.',
            'status.in' => 'Geçersiz durum seçildi.',
            'reason.nullable' => 'Açıklama girilmedi.',
        ];
    }
}
