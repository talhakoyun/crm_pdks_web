<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

class LoginRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'device_id' => 'required|string',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'email.required' => 'E-posta adresi zorunludur',
            'email.email' => 'Geçerli bir e-posta adresi giriniz',
            'password.required' => 'Şifre zorunludur',
            'password.min' => 'Şifre en az 6 karakter olmalıdır',
            'device_id.required' => 'Cihaz kimliği zorunludur',
        ];
    }
}
