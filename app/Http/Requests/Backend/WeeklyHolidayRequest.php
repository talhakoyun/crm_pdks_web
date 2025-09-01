<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class WeeklyHolidayRequest extends FormRequest
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
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'holiday_days' => 'required|array|min:1|max:7',
            'holiday_days.*' => 'integer|between:1,7|distinct',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.required' => 'En az bir kullanıcı seçilmelidir.',
            'user_ids.array' => 'Kullanıcı listesi geçerli formatta değil.',
            'user_ids.min' => 'En az bir kullanıcı seçilmelidir.',
            'user_ids.*.exists' => 'Seçilen kullanıcılardan biri sistemde bulunamadı.',

            'holiday_days.required' => 'En az bir tatil günü seçilmelidir.',
            'holiday_days.array' => 'Tatil günleri listesi geçerli formatta değil.',
            'holiday_days.min' => 'En az bir tatil günü seçilmelidir.',
            'holiday_days.max' => 'En fazla 7 tatil günü seçilebilir.',
            'holiday_days.*.integer' => 'Tatil günü değeri geçerli değil.',
            'holiday_days.*.between' => 'Tatil günü 1-7 arasında olmalıdır.',
            'holiday_days.*.distinct' => 'Aynı tatil günü birden fazla seçilemez.',

            'is_active.boolean' => 'Durum değeri geçerli değil.',
        ];
    }
}
