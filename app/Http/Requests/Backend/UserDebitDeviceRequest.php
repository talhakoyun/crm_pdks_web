<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UserDebitDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'debit_device_id' => 'required|exists:debit_devices,id',
            'start_date' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Kullanıcı seçimi zorunludur.',
            'user_id.exists' => 'Seçilen kullanıcı bulunamadı.',
            'debit_device_id.required' => 'Zimmet cihazı seçimi zorunludur.',
            'debit_device_id.exists' => 'Seçilen zimmet cihazı bulunamadı.',
            'start_date.required' => 'Başlangıç tarihi zorunludur.',
            'start_date.date' => 'Başlangıç tarihi geçerli bir tarih olmalıdır.',
            'notes.string' => 'Notlar metin olmalıdır.',
        ];
    }
}
