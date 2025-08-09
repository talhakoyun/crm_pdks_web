<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\ShiftFollow;

use App\Http\Requests\Api\ApiRequest;

class ShiftFollowWeeklyRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
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
            'start_date.date_format' => 'Geçersiz başlangıç tarihi formatı. Y-m-d formatında olmalı (örn: 2023-05-20).',
            'end_date.date_format' => 'Geçersiz bitiş tarihi formatı. Y-m-d formatında olmalı (örn: 2023-05-26).',
            'end_date.after_or_equal' => 'Bitiş tarihi, başlangıç tarihinden önce olamaz.',
            'user_id.exists' => 'Belirtilen kullanıcı bulunamadı.',
        ];
    }
}
