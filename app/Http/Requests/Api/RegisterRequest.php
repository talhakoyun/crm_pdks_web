<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:255|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ad alanı zorunludur.',
            'name.string' => 'Ad alanı metin olmalıdır.',
            'name.max' => 'Ad alanı en fazla 255 karakter olmalıdır.',
            'surname.required' => 'Soyad alanı zorunludur.',
            'surname.string' => 'Soyad alanı metin olmalıdır.',
            'surname.max' => 'Soyad alanı en fazla 255 karakter olmalıdır.',
            'email.required' => 'Email alanı zorunludur.',
            'email.email' => 'Geçerli bir email adresi giriniz.',
            'email.unique' => 'Bu email adresi zaten kullanılıyor.',
            'phone.required' => 'Telefon alanı zorunludur.',
            'phone.unique' => 'Bu telefon numarası zaten kullanılıyor.',
            'password.required' => 'Şifre alanı zorunludur.',
            'password.string' => 'Şifre metin olmalıdır.',
            'password.min' => 'Şifre en az 8 karakter olmalıdır.',
            'password_confirmation.required' => 'Şifre tekrarı alanı zorunludur.',
            'password_confirmation.string' => 'Şifre tekrarı metin olmalıdır.',
            'password_confirmation.min' => 'Şifre tekrarı en az 8 karakter olmalıdır.',
            'password_confirmation.confirmed' => 'Şifreler eşleşmiyor.',

        ];
    }
}
