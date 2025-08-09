<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\ShiftFollow;

use Illuminate\Foundation\Http\FormRequest;

class WeeklyReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'user_id' => 'nullable|integer|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'start_date.required' => 'Başlangıç tarihi gereklidir.',
            'start_date.date_format' => 'Başlangıç tarihi formatı geçersiz. YYYY-MM-DD formatında olmalıdır.',
            'end_date.required' => 'Bitiş tarihi gereklidir.',
            'end_date.date_format' => 'Bitiş tarihi formatı geçersiz. YYYY-MM-DD formatında olmalıdır.',
            'end_date.after_or_equal' => 'Bitiş tarihi, başlangıç tarihinden sonra veya aynı olmalıdır.',
            'user_id.integer' => 'Kullanıcı ID bir tam sayı olmalıdır.',
            'user_id.exists' => 'Belirtilen kullanıcı bulunamadı.',
        ];
    }
}
