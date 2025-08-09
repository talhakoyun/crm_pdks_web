<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\ShiftFollow;

use App\Http\Requests\Api\BaseStoreRequest;

class ShiftFollowStoreRequest extends BaseStoreRequest
{
    /**
     * Kayıt için validasyon kurallarını tanımlar.
     *
     * @return array
     */
    protected function storeRules(): array
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'zone_id' => 'required|exists:zones,id',
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shift_definitions,id',
            'transaction_date' => 'required|date_format:Y-m-d H:i:s',
            'shift_follow_type_id' => 'required|exists:shift_follow_types,id',
            'positions' => 'nullable|array',
            'positions.latitude' => 'required_with:positions|numeric',
            'positions.longitude' => 'required_with:positions|numeric',
            'is_offline' => 'boolean',
            'device_id' => 'nullable|string',
            'device_model' => 'nullable|string',
            'note' => 'nullable|string|max:500'
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
            'branch_id.required' => 'Şube bilgisi gereklidir.',
            'branch_id.exists' => 'Geçersiz şube seçimi.',
            'zone_id.required' => 'Bölge bilgisi gereklidir.',
            'zone_id.exists' => 'Geçersiz bölge seçimi.',
            'user_id.required' => 'Kullanıcı bilgisi gereklidir.',
            'user_id.exists' => 'Geçersiz kullanıcı seçimi.',
            'shift_id.required' => 'Vardiya bilgisi gereklidir.',
            'shift_id.exists' => 'Geçersiz vardiya seçimi.',
            'transaction_date.required' => 'İşlem tarihi gereklidir.',
            'transaction_date.date_format' => 'Geçersiz tarih formatı. (Y-m-d H:i:s)',
            'shift_follow_type_id.required' => 'İşlem tipi gereklidir.',
            'shift_follow_type_id.exists' => 'Geçersiz işlem tipi.',
            'positions.array' => 'Konum bilgisi geçersiz formatta.',
            'positions.latitude.required_with' => 'Enlem bilgisi gereklidir.',
            'positions.latitude.numeric' => 'Enlem bilgisi sayısal olmalıdır.',
            'positions.longitude.required_with' => 'Boylam bilgisi gereklidir.',
            'positions.longitude.numeric' => 'Boylam bilgisi sayısal olmalıdır.',
            'is_offline.boolean' => 'Çevrimdışı durumu geçersiz.',
            'device_id.string' => 'Cihaz ID bilgisi geçersiz.',
            'device_model.string' => 'Cihaz model bilgisi geçersiz.',
            'note.max' => 'Not en fazla 500 karakter olabilir.',
        ];
    }
}
