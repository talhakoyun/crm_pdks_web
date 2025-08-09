<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Holiday;

use App\Http\Requests\Api\ApiRequest;

class HolidayListRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'status' => 'nullable|in:pending,approved,rejected,all',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|in:id,start_date,end_date,created_at',
            'sort_order' => 'nullable|in:asc,desc',
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
            'date_start.date' => 'Başlangıç tarihi geçerli bir tarih olmalıdır',
            'date_end.date' => 'Bitiş tarihi geçerli bir tarih olmalıdır',
            'date_end.after_or_equal' => 'Bitiş tarihi, başlangıç tarihinden önce olamaz',
            'status.in' => 'Durum değeri geçerli olmalıdır: pending, approved, rejected, all',
            'per_page.integer' => 'Sayfa başına kayıt sayısı tam sayı olmalıdır',
            'per_page.min' => 'Sayfa başına kayıt sayısı en az 1 olmalıdır',
            'per_page.max' => 'Sayfa başına kayıt sayısı en fazla 100 olmalıdır',
            'page.integer' => 'Sayfa numarası tam sayı olmalıdır',
            'page.min' => 'Sayfa numarası en az 1 olmalıdır',
            'sort_by.in' => 'Sıralama alanı geçerli olmalıdır',
            'sort_order.in' => 'Sıralama yönü geçerli olmalıdır: asc, desc'
        ];
    }
}
