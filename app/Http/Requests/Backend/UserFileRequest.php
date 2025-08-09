<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class UserFileRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'file_type_id' => 'required|exists:file_types,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'filename' => 'nullable|string|max:255',
            'original_filename' => 'nullable|string|max:255',
            'file_path' => 'nullable|string|max:255',
            'file_extension' => 'nullable|string|max:20',
            'file_size' => 'nullable|integer|min:0',
        ];

        // Yeni kayıt ise dosya alanları zorunlu
        if (!$this->route('unique')) {
            $rules['filename'] = 'required|string|max:255';
            $rules['original_filename'] = 'required|string|max:255';
            $rules['file_path'] = 'required|string|max:255';
            $rules['file_extension'] = 'required|string|max:20';
            $rules['file_size'] = 'required|integer|min:1';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Personel seçilmedi.',
            'user_id.exists' => 'Seçilen personel geçerli değil.',
            'file_type_id.required' => 'Dosya tipi seçilmedi.',
            'file_type_id.exists' => 'Seçilen dosya tipi geçerli değil.',
            'title.string' => 'Başlık metin olmalıdır.',
            'title.max' => 'Başlık en fazla 255 karakter olmalıdır.',
            'description.string' => 'Açıklama metin olmalıdır.',
            'description.max' => 'Açıklama en fazla 1000 karakter olmalıdır.',
            'filename.required' => 'Dosya adı gereklidir.',
            'filename.string' => 'Dosya adı metin olmalıdır.',
            'filename.max' => 'Dosya adı en fazla 255 karakter olmalıdır.',
            'original_filename.required' => 'Orijinal dosya adı gereklidir.',
            'original_filename.string' => 'Orijinal dosya adı metin olmalıdır.',
            'original_filename.max' => 'Orijinal dosya adı en fazla 255 karakter olmalıdır.',
            'file_path.required' => 'Dosya yolu gereklidir.',
            'file_path.string' => 'Dosya yolu metin olmalıdır.',
            'file_path.max' => 'Dosya yolu en fazla 255 karakter olmalıdır.',
            'file_extension.required' => 'Dosya uzantısı gereklidir.',
            'file_extension.string' => 'Dosya uzantısı metin olmalıdır.',
            'file_extension.max' => 'Dosya uzantısı en fazla 20 karakter olmalıdır.',
            'file_size.required' => 'Dosya boyutu gereklidir.',
            'file_size.integer' => 'Dosya boyutu sayı olmalıdır.',
            'file_size.min' => 'Dosya boyutu 0\'dan büyük olmalıdır.',
        ];
    }
}
