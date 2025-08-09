<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\ShiftFollow;

use App\Http\Requests\Api\ApiRequest;

class ShiftFollowZoneRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'positions' => 'required|array',
            'positions.latitude' => 'required|numeric',
            'positions.longitude' => 'required|numeric',
            'device_id' => 'required|string',
            'device_model' => 'nullable|string',
            'is_offline' => 'boolean',
            'zone' => 'nullable|exists:zones,id',
            'note' => 'nullable|string|max:500',
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
            'positions.required' => 'Konum bilgisi gereklidir.',
            'positions.array' => 'Konum bilgisi geçersiz formatta.',
            'positions.latitude.required' => 'Enlem bilgisi gereklidir.',
            'positions.latitude.numeric' => 'Enlem bilgisi sayısal olmalıdır.',
            'positions.longitude.required' => 'Boylam bilgisi gereklidir.',
            'positions.longitude.numeric' => 'Boylam bilgisi sayısal olmalıdır.',
            'device_id.required' => 'Cihaz ID bilgisi gereklidir.',
            'device_id.string' => 'Cihaz ID bilgisi geçersiz.',
            'device_model.string' => 'Cihaz model bilgisi geçersiz.',
            'is_offline.boolean' => 'Çevrimdışı durumu geçersiz.',
            'zone.exists' => 'Geçersiz bölge seçimi.',
            'note.max' => 'Not en fazla 500 karakter olabilir.',
        ];
    }
}
