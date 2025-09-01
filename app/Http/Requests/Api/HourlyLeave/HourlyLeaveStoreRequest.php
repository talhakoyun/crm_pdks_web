<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\HourlyLeave;

use App\Http\Requests\Api\BaseStoreRequest;

class HourlyLeaveStoreRequest extends BaseStoreRequest
{
    /**
     * Kayıt için validasyon kurallarını tanımlar.
     *
     * @return array
     */
    protected function storeRules(): array
    {
        return [
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'type' => 'required|exists:holiday_type,id',
            'reason' => 'nullable|string|max:500',
        ];
    }

    /**
     * Validasyon hata mesajlarını tanımlar.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Tarih zorunludur',
            'date.date' => 'Tarih geçerli bir tarih olmalıdır',
            'start_time.required' => 'Başlangıç saati zorunludur',
            'start_time.date_format' => 'Başlangıç saati geçerli formatta olmalıdır (HH:MM)',
            'end_time.required' => 'Bitiş saati zorunludur',
            'end_time.date_format' => 'Bitiş saati geçerli formatta olmalıdır (HH:MM)',
            'end_time.after' => 'Bitiş saati başlangıç saatinden sonra olmalıdır',
            'type.required' => 'İzin tipi zorunludur',
            'type.exists' => 'Geçersiz izin tipi',
            'reason.max' => 'Açıklama en fazla 500 karakter olabilir'
        ];
    }
} 