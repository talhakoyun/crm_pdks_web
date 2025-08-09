<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    /**
     * Kullanıcının bu isteği yapma yetkisi var mı?
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validasyon kuralları
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'send_type' => 'required|in:role,branch,department',
            'status' => 'boolean'
        ];

        // Gönderim tipine göre validasyon kuralları
        switch ($this->send_type) {
            case 'role':
                $rules['roles'] = 'required|array';
                $rules['roles.*'] = 'exists:roles,id';
                $rules['role_user_type'] = 'required|in:all,specific';
                if ($this->role_user_type === 'specific') {
                    $rules['role_users'] = 'required|array';
                    $rules['role_users.*'] = 'exists:users,id';
                }
                break;

            case 'branch':
                $rules['branches'] = 'required|array';
                $rules['branches.*'] = 'exists:branches,id';
                $rules['branch_user_type'] = 'required|in:all,specific';
                if ($this->branch_user_type === 'specific') {
                    $rules['branch_users'] = 'required|array';
                    $rules['branch_users.*'] = 'exists:users,id';
                }
                break;

            case 'department':
                $rules['departments'] = 'required|array';
                $rules['departments.*'] = 'exists:departments,id';
                $rules['department_user_type'] = 'required|in:all,specific';
                if ($this->department_user_type === 'specific') {
                    $rules['department_users'] = 'required|array';
                    $rules['department_users.*'] = 'exists:users,id';
                }
                break;
        }

        return $rules;
    }

    /**
     * Özel hata mesajları
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Duyuru başlığı zorunludur',
            'title.max' => 'Duyuru başlığı en fazla 255 karakter olabilir',
            'content.required' => 'Duyuru içeriği zorunludur',
            'start_date.required' => 'Başlangıç tarihi zorunludur',
            'start_date.date' => 'Geçerli bir başlangıç tarihi giriniz',
            'end_date.required' => 'Bitiş tarihi zorunludur',
            'end_date.date' => 'Geçerli bir bitiş tarihi giriniz',
            'end_date.after' => 'Bitiş tarihi başlangıç tarihinden sonra olmalıdır',
            'send_type.required' => 'Gönderim tipi seçmelisiniz',
            'send_type.in' => 'Geçersiz gönderim tipi',

            // Rol bazlı validasyon mesajları
            'roles.required' => 'En az bir rol seçmelisiniz',
            'roles.array' => 'Roller dizi formatında olmalıdır',
            'roles.*.exists' => 'Seçilen rol bulunamadı',
            'role_user_type.required' => 'Kullanıcı seçim tipini belirtmelisiniz',
            'role_user_type.in' => 'Geçersiz kullanıcı seçim tipi',
            'role_users.required' => 'En az bir kullanıcı seçmelisiniz',
            'role_users.array' => 'Kullanıcılar dizi formatında olmalıdır',
            'role_users.*.exists' => 'Seçilen kullanıcı bulunamadı',

            // Şube bazlı validasyon mesajları
            'branches.required' => 'En az bir şube seçmelisiniz',
            'branches.array' => 'Şubeler dizi formatında olmalıdır',
            'branches.*.exists' => 'Seçilen şube bulunamadı',
            'branch_user_type.required' => 'Kullanıcı seçim tipini belirtmelisiniz',
            'branch_user_type.in' => 'Geçersiz kullanıcı seçim tipi',
            'branch_users.required' => 'En az bir kullanıcı seçmelisiniz',
            'branch_users.array' => 'Kullanıcılar dizi formatında olmalıdır',
            'branch_users.*.exists' => 'Seçilen kullanıcı bulunamadı',

            // Departman bazlı validasyon mesajları
            'departments.required' => 'En az bir departman seçmelisiniz',
            'departments.array' => 'Departmanlar dizi formatında olmalıdır',
            'departments.*.exists' => 'Seçilen departman bulunamadı',
            'department_user_type.required' => 'Kullanıcı seçim tipini belirtmelisiniz',
            'department_user_type.in' => 'Geçersiz kullanıcı seçim tipi',
            'department_users.required' => 'En az bir kullanıcı seçmelisiniz',
            'department_users.array' => 'Kullanıcılar dizi formatında olmalıdır',
            'department_users.*.exists' => 'Seçilen kullanıcı bulunamadı'
        ];
    }
}
