<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class PasswordChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => 'required|min:6|max:12',
            'confirm_password' => 'required|min:6|max:12|same:password',
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Şifre boş geçilemez',
            'password.min' => 'Şifre minimum 6 karakter olmalıdır.',
            'password.max' => 'Şifre maksimum 12 karakter olmalıdır.',
            'confirm_password.required' => 'Şifre tekrarı boş geçilemez',
            'confirm_password.min' => 'Şifre tekrarı minimum 6 karakter olmalıdır.',
            'confirm_password.max' => 'Şifre tekrarı maksimum 12 karakter olmalıdır.',
            'confirm_password.same' => 'Şifre tekrarı şifre ile aynı olmalıdır.',
        ];
    }
}
