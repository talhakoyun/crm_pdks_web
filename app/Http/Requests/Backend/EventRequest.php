<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'quota' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ];

        // Düzenleme durumunda status alanı zorunlu
        if ($this->isMethod('post') && $this->route('unique')) {
            // Düzenleme - status zorunlu
            $rules['status'] = 'required|in:active,passive,completed';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Başlık alanı zorunludur.',
            'title.string' => 'Başlık alanı metin olmalıdır.',
            'title.max' => 'Başlık alanı en fazla 255 karakter olmalıdır.',
            'location.required' => 'Konum alanı zorunludur.',
            'location.string' => 'Konum alanı metin olmalıdır.',
            'location.max' => 'Konum alanı en fazla 255 karakter olmalıdır.',
            'quota.required' => 'Kontenjan alanı zorunludur.',
            'quota.integer' => 'Kontenjan alanı sayı olmalıdır.',
            'start_date.required' => 'Başlangıç tarihi alanı zorunludur.',
            'start_date.date' => 'Başlangıç tarihi alanı tarih olmalıdır.',
            'end_date.required' => 'Bitiş tarihi alanı zorunludur.',
            'end_date.date' => 'Bitiş tarihi alanı tarih olmalıdır.',
            'status.required' => 'Durum alanı zorunludur.',
            'status.in' => 'Durum alanı geçersiz bir değerdir.',
        ];
    }
}
