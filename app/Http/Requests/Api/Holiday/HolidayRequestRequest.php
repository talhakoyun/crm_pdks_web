<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Holiday;

use App\Http\Requests\Api\ApiRequest;

class HolidayRequestRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|exists:holiday_type,id',
            'note' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.required' => 'Başlangıç tarihi zorunludur',
            'start_date.date' => 'Başlangıç tarihi geçerli bir tarih olmalıdır',
            'end_date.required' => 'Bitiş tarihi zorunludur',
            'end_date.date' => 'Bitiş tarihi geçerli bir tarih olmalıdır',
            'end_date.after_or_equal' => 'Bitiş tarihi, başlangıç tarihinden önce olamaz',
            'note.max' => 'Not en fazla 500 karakter olabilir',
            'type.required' => 'İzin tipi zorunludur',
            'type.exists' => 'Geçerli bir izin tipi seçilmedi',
        ];
    }
}
