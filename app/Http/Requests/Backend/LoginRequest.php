<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix',
            'password' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Lütfen e-posta adresinizi giriniz.',
            'email.email' => 'Hatalı e-posta adresi formatı.',
            'email.regex' => 'Hatalı e-posta adresi formatı.',
            'password.required' => 'Lütfen şifrenizi giriniz.'
        ];
    }
}
