<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\ShiftFollow;

use Illuminate\Foundation\Http\FormRequest;

class DailyReportRequest extends FormRequest
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
            'date' => 'nullable|date_format:Y-m-d',
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
            'date.date_format' => 'Tarih formatı geçersiz. YYYY-MM-DD formatında olmalıdır.',
            'user_id.integer' => 'Kullanıcı ID bir tam sayı olmalıdır.',
            'user_id.exists' => 'Belirtilen kullanıcı bulunamadı.',
        ];
    }
}
