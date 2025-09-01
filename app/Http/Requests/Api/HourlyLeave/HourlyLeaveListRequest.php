<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\HourlyLeave;

use App\Http\Requests\Api\BaseRequest;

class HourlyLeaveListRequest extends BaseRequest
{
    /**
     * Liste için validasyon kurallarını tanımlar.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'date' => 'nullable|date',
            'status' => 'nullable|in:pending,approved,rejected',
            'type' => 'nullable|exists:holiday_type,id',
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|string|in:id,date,start_time,end_time,created_at,updated_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
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
            'date.date' => 'Tarih geçerli bir tarih olmalıdır',
            'status.in' => 'Geçersiz durum değeri',
            'type.exists' => 'Geçersiz izin tipi',
            'search.max' => 'Arama terimi en fazla 255 karakter olabilir',
            'sort_by.in' => 'Geçersiz sıralama alanı',
            'sort_order.in' => 'Geçersiz sıralama yönü',
            'per_page.integer' => 'Sayfa başına kayıt sayısı sayı olmalıdır',
            'per_page.min' => 'Sayfa başına kayıt sayısı en az 1 olmalıdır',
            'per_page.max' => 'Sayfa başına kayıt sayısı en fazla 100 olabilir'
        ];
    }
}
