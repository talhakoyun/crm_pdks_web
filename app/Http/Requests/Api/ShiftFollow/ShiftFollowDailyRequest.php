<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\ShiftFollow;

use App\Http\Requests\Api\ApiRequest;

class ShiftFollowDailyRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'date' => 'nullable|date_format:Y-m-d',
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
            'date.date_format' => 'Geçersiz tarih formatı. Y-m-d formatında olmalı (örn: 2023-05-20).',
            'user_id.exists' => 'Belirtilen kullanıcı bulunamadı.',
        ];
    }
}
