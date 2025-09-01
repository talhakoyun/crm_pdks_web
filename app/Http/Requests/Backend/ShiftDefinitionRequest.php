<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class ShiftDefinitionRequest extends FormRequest
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
            'title' => 'required|string|min:3|max:255',
            // Eski alanlar artık opsiyonel
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
        ];

        // Haftalık çalışma saatleri için validation kuralları
        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($weekdays as $day) {
            $rules[$day . '_start'] = 'nullable|date_format:H:i';
            $rules[$day . '_end'] = 'nullable|date_format:H:i';
        }

        return $rules;
    }

    /**
     * Custom validation after default rules
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasWorkingDay = false;
            $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

            // En az bir gün için çalışma saati girilmiş mi kontrol et
            foreach ($weekdays as $day) {
                $start = $this->input($day . '_start');
                $end = $this->input($day . '_end');

                if ($start && $end) {
                    $hasWorkingDay = true;

                    // Başlangıç saati bitiş saatinden sonra olamaz (aynı gün içinde)
                    if (strtotime($start) >= strtotime($end)) {
                        $dayNames = [
                            'monday' => 'Pazartesi',
                            'tuesday' => 'Salı',
                            'wednesday' => 'Çarşamba',
                            'thursday' => 'Perşembe',
                            'friday' => 'Cuma',
                            'saturday' => 'Cumartesi',
                            'sunday' => 'Pazar'
                        ];

                        $validator->errors()->add(
                            $day . '_end',
                            $dayNames[$day] . ' günü için bitiş saati başlangıç saatinden sonra olmalıdır.'
                        );
                    }
                } else if ($start && !$end) {
                    // Başlangıç saati var ama bitiş saati yok
                    $validator->errors()->add($day . '_end', 'Başlangıç saati girildiğinde bitiş saati de girilmelidir.');
                } else if (!$start && $end) {
                    // Bitiş saati var ama başlangıç saati yok
                    $validator->errors()->add($day . '_start', 'Bitiş saati girildiğinde başlangıç saati de girilmelidir.');
                }
            }

            if (!$hasWorkingDay) {
                $validator->errors()->add('general', 'En az bir gün için çalışma saati girilmelidir.');
            }
        });
    }

    public function messages(): array
    {
        $messages = [
            'title.required' => 'Vardiya adı girilmedi.',
            'title.min' => 'Vardiya adı en az 3 karakter olmalıdır.',
            'title.max' => 'Vardiya adı en fazla 255 karakter olmalıdır.',
            'start_time.date_format' => 'Başlangıç saati geçersiz formatta.',
            'end_time.date_format' => 'Bitiş saati geçersiz formatta.',
        ];

        // Haftalık günler için hata mesajları
        $weekdays = [
            'monday' => 'Pazartesi',
            'tuesday' => 'Salı',
            'wednesday' => 'Çarşamba',
            'thursday' => 'Perşembe',
            'friday' => 'Cuma',
            'saturday' => 'Cumartesi',
            'sunday' => 'Pazar'
        ];

        foreach ($weekdays as $day => $dayName) {
            $messages[$day . '_start.date_format'] = $dayName . ' başlangıç saati geçersiz formatta.';
            $messages[$day . '_end.date_format'] = $dayName . ' bitiş saati geçersiz formatta.';
        }

        return $messages;
    }
}
