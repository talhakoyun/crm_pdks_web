<?php

namespace App\Http\Requests\Api\User;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'email' => 'nullable|email|unique:users,email,' . $this->user()->id,
            'phone' => 'nullable|unique:users,phone,' . $this->user()->id,
            'gender' => 'nullable|integer|in:1,2',
            'birthday' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Geçerli bir email adresi giriniz.',
            'email.unique' => 'Bu email adresi zaten kullanılıyor.',
            'phone.unique' => 'Bu telefon numarası zaten kullanılıyor.',
            'gender.integer' => 'Cinsiyet bilgisi geçerli bir sayı olmalıdır.',
            'gender.in' => 'Cinsiyet bilgisi geçerli bir değer olmalıdır.',
            'birthday.date' => 'Doğum tarihi geçerli bir tarih olmalıdır.',
        ];
    }
}
