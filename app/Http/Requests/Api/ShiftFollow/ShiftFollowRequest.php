<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\ShiftFollow;

use App\Http\Requests\Api\ApiRequest;

class ShiftFollowRequest extends ApiRequest
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
     * @return array
     */
    public function rules(): array
    {
        // Default kurallar - standart CRUD işlemleri için
        if ($this->routeIs('*.store') || $this->routeIs('*.update')) {
            $rules = [
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

            // Güncelleme işlemi için sometimes parametresi ekle
            if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                foreach ($rules as $field => $rule) {
                    $rules[$field] = 'sometimes|' . $rule;
                }
            }

            return $rules;
        }

        // Check-in/out özel işlemi için
        if ($this->routeIs('*.checkInOut')) {
            return [
                'type' => 'required|in:in,out,zone_entry,zone_exit,break_start,break_end',
                'branch_id' => 'required|exists:branches,id',
                'zone_id' => 'required|exists:zones,id',
                'shift_id' => 'required|exists:shift_definitions,id',
                'positions' => 'nullable|array',
                'positions.latitude' => 'required_with:positions|numeric',
                'positions.longitude' => 'required_with:positions|numeric',
                'device_id' => 'nullable|string',
                'device_model' => 'nullable|string',
                'note' => 'nullable|string|max:500',
                'is_offline' => 'boolean'
            ];
        }

        // Eski kurallar - varsayılan olarak eski validasyon kuralları kalsın
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'user_id' => 'required|exists:users,id',
            'note' => 'nullable|string|max:500',
            'status' => 'nullable|integer|in:0,1,2', // 0: Planlandı, 1: Başladı, 2: Tamamlandı
        ];

        // Vardiya düzenlenirken özel kurallar eklenebilir
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            foreach ($rules as $field => $rule) {
                $rules[$field] = 'sometimes|' . $rule;
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
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
            'department_id.required' => 'Departman bilgisi gereklidir.',
            'department_id.exists' => 'Geçersiz departman seçimi.',
            'shift_id.required' => 'Vardiya bilgisi gereklidir.',
            'shift_id.exists' => 'Geçersiz vardiya seçimi.',
            'shift_follow_type_id.required' => 'İşlem tipi gereklidir.',
            'shift_follow_type_id.exists' => 'Geçersiz işlem tipi.',
            'type.required' => 'İşlem tipi gereklidir.',
            'type.in' => 'Geçersiz işlem tipi.',
            'date.required' => 'Vardiya tarihi gereklidir.',
            'date.date_format' => 'Geçersiz tarih formatı. (Y-m-d)',
            'transaction_date.required' => 'İşlem tarihi gereklidir.',
            'transaction_date.date_format' => 'Geçersiz tarih formatı. (Y-m-d H:i:s)',
            'start_time.required' => 'Başlangıç saati gereklidir.',
            'start_time.date_format' => 'Geçersiz saat formatı. (H:i)',
            'end_time.required' => 'Bitiş saati gereklidir.',
            'end_time.date_format' => 'Geçersiz saat formatı. (H:i)',
            'end_time.after' => 'Bitiş saati, başlangıç saatinden sonra olmalıdır.',
            'user_id.required' => 'Kullanıcı bilgisi gereklidir.',
            'user_id.exists' => 'Geçersiz kullanıcı seçimi.',
            'positions.array' => 'Konum bilgisi geçersiz formatta.',
            'positions.latitude.required_with' => 'Enlem bilgisi gereklidir.',
            'positions.latitude.numeric' => 'Enlem bilgisi sayısal olmalıdır.',
            'positions.longitude.required_with' => 'Boylam bilgisi gereklidir.',
            'positions.longitude.numeric' => 'Boylam bilgisi sayısal olmalıdır.',
            'is_offline.boolean' => 'Çevrimdışı durumu geçersiz.',
            'device_id.string' => 'Cihaz ID bilgisi geçersiz.',
            'device_model.string' => 'Cihaz model bilgisi geçersiz.',
            'note.max' => 'Not en fazla 500 karakter olabilir.',
            'status.in' => 'Geçersiz durum değeri.',
        ];
    }
}
