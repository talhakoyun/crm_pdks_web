<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\ShiftFollow;

use App\Http\Requests\Api\ApiRequest;

class ShiftFollowCheckRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:check_in,check_out',
            'branch_id' => 'required|exists:branches,id',
            'zone_id' => 'required|exists:zones,id',
            'shift_id' => 'required|exists:shift_definitions,id',
            'positions' => 'required|array',
            'positions.latitude' => 'required|numeric',
            'positions.longitude' => 'required|numeric',
            'is_offline' => 'boolean',
            'device_id' => 'required|string',
            'device_model' => 'nullable|string',
            'note' => 'nullable|string|max:500',
            'outside' => 'nullable|boolean',
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
            'type.required' => 'İşlem tipi gereklidir.',
            'type.in' => 'Geçersiz işlem tipi. Sadece check_in veya check_out olabilir.',
            'branch_id.required' => 'Şube bilgisi gereklidir.',
            'branch_id.exists' => 'Geçersiz şube seçimi.',
            'zone_id.required' => 'Bölge bilgisi gereklidir.',
            'zone_id.exists' => 'Geçersiz bölge seçimi.',
            'shift_id.required' => 'Vardiya bilgisi gereklidir.',
            'shift_id.exists' => 'Geçersiz vardiya seçimi.',
            'positions.required' => 'Konum bilgisi gereklidir.',
            'positions.array' => 'Konum bilgisi geçersiz formatta.',
            'positions.latitude.required' => 'Enlem bilgisi gereklidir.',
            'positions.latitude.numeric' => 'Enlem bilgisi sayısal olmalıdır.',
            'positions.longitude.required' => 'Boylam bilgisi gereklidir.',
            'positions.longitude.numeric' => 'Boylam bilgisi sayısal olmalıdır.',
            'is_offline.boolean' => 'Çevrimdışı durumu geçersiz.',
            'device_id.required' => 'Cihaz ID bilgisi gereklidir.',
            'device_id.string' => 'Cihaz ID bilgisi geçersiz.',
            'device_model.string' => 'Cihaz model bilgisi geçersiz.',
            'note.max' => 'Not en fazla 500 karakter olabilir.',
            'outside.boolean' => 'Dışarıda durumu geçersiz.',
        ];
    }
}
