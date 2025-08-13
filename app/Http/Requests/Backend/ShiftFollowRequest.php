<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class ShiftFollowRequest extends FormRequest
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
            'shift_follow_type_id' => 'required|exists:shift_follow_types,id',
            'shift_id' => 'required|exists:shift_definitions,id',
            'transaction_date' => 'required|date',
            'note' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Kullanıcı seçilmedi.',
            'user_id.exists' => 'Kullanıcı bulunamadı.',
            'shift_follow_type_id.required' => 'İşlem tipi seçilmedi.',
            'shift_follow_type_id.exists' => 'İşlem tipi bulunamadı.',
            'shift_id.required' => 'Giriş çıkış tarihi seçilmedi.',
            'shift_id.exists' => 'Giriş çıkış tarihi bulunamadı.',
            'transaction_date.required' => 'Tarih seçilmedi.',
            'transaction_date.date' => 'Tarih geçersiz formatta.',
        ];
    }
}
