<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
        $companyId = $this->route('unique') ?? request()->route('unique'); // Route'dan ID'yi al

        return [
            'title' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:companies,email' . ($companyId ? ',' . $companyId : ''),
            'phone_number' => 'required|min:15|max:15',
            'address' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Şirket adı zorunludur.',
            'title.string' => 'Şirket adı metin olmalıdır.',
            'title.min' => 'Şirket adı en az 2 karakter olmalıdır.',
            'title.max' => 'Şirket adı en fazla 255 karakter olmalıdır.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçersiz e-posta adresi.',
            'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
            'phone_number.required' => 'Telefon numarası zorunludur.',
            'phone_number.min' => 'Telefon numarası en az 15 karakter olmalıdır.',
            'phone_number.max' => 'Telefon numarası en fazla 15 karakter olmalıdır.',
            'address.required' => 'Adres zorunludur.',
            'address.string' => 'Adres metin olmalıdır.',
            'address.max' => 'Adres en fazla 255 karakter olmalıdır.',
            'user_id.required' => 'Yönetici seçimi zorunludur.',
            'user_id.exists' => 'Seçilen yönetici geçersizdir.',
            'image.image' => 'Resim bir görsel olmalıdır.',
            'image.mimes' => 'Resim formatı geçersiz.',
            'image.max' => 'Resim en fazla 2MB olmalıdır.',
        ];
    }
}
