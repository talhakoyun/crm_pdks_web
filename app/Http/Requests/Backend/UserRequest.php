<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|min:2|max:255',
            'surname' => 'required|string|min:2|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email',
            'tc' => 'required|string|min:11|max:11',
            'gender' => 'required|integer',
            'birth_date' => 'required|date',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'required|boolean',
        ];

        // Düzenleme durumunda password zorunlu değil
        if ($this->isMethod('post') && !$this->id) {
            // Yeni kayıt - password zorunlu
            $rules['password'] = 'required|string|min:8';
            $rules['email'] = 'unique:users,email';
        } elseif ($this->filled('password')) {
            // Düzenleme ve password doldurulmuşsa - validation yap
            $rules['password'] = 'string|min:8';
        }

        // Eğer role_id 5, 6 veya 7 (Personel rolleri) ise diğer alanları da zorunlu yap
        $roleId = $this->input('role_id');
        if (in_array($roleId, [5, 6, 7])) {
            $rules['title'] = 'required|string|min:3|max:255';
            $rules['shift_definition_id'] = 'required|exists:shift_definitions,id';
            $rules['branch_ids'] = 'required|array|min:1';
            $rules['branch_ids.*'] = 'exists:branches,id';
            $rules['start_work_date'] = 'required|date';
            $rules['leave_work_date'] = 'nullable|date';
            
            // Departman yetkilisi (role_id = 6) için departman seçimi zorunlu değil
            if ($roleId != 6) {
                $rules['department_id'] = 'required|exists:departments,id';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'İsim alanı zorunludur.',
            'name.string' => 'İsim alanı metin olmalıdır.',
            'name.min' => 'İsim alanı en az 2 karakter olmalıdır.',
            'name.max' => 'İsim alanı en fazla 255 karakter olmalıdır.',
            'surname.required' => 'Soyisim alanı zorunludur.',
            'surname.string' => 'Soyisim alanı metin olmalıdır.',
            'surname.min' => 'Soyisim alanı en az 2 karakter olmalıdır.',
            'surname.max' => 'Soyisim alanı en fazla 255 karakter olmalıdır.',
            'title.required' => 'Ünvan alanı zorunludur.',
            'title.string' => 'Ünvan alanı metin olmalıdır.',
            'title.min' => 'Ünvan alanı en az 3 karakter olmalıdır.',
            'title.max' => 'Ünvan alanı en fazla 255 karakter olmalıdır.',
            'email.required' => 'E-posta alanı zorunludur.',
            'email.email' => 'E-posta alanı geçerli bir e-posta adresi olmalıdır.',
            'email.unique' => 'Bu e-posta adresi zaten kullanılıyor.',
            'password.required' => 'Şifre alanı zorunludur.',
            'password.string' => 'Şifre alanı metin olmalıdır.',
            'password.min' => 'Şifre alanı en az 8 karakter olmalıdır.',
            'phone.required' => 'Telefon alanı zorunludur.',
            'phone.string' => 'Telefon alanı metin olmalıdır.',
            'phone.max' => 'Telefon alanı en fazla 255 karakter olmalıdır.',
            'role_id.required' => 'Rol alanı zorunludur.',
            'role_id.exists' => 'Geçersiz rol seçildi.',
            'is_active.required' => 'Aktiflik durumu zorunludur.',
            'is_active.boolean' => 'Aktiflik durumu geçerli bir boolean değer olmalıdır.',
            'shift_definition_id.required' => 'Vardiya alanı zorunludur.',
            'shift_definition_id.exists' => 'Geçersiz vardiya seçildi.',
            'department_id.required' => 'Departman alanı zorunludur.',
            'department_id.exists' => 'Geçersiz departman seçildi.',
            'branch_ids.required' => 'En az bir şube seçilmelidir.',
            'branch_ids.array' => 'Şube seçimi geçerli bir liste olmalıdır.',
            'branch_ids.min' => 'En az bir şube seçilmelidir.',
            'branch_ids.*.exists' => 'Seçilen şubelerden biri geçersizdir.',
        ];
    }
}
