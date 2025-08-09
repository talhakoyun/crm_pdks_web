<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

abstract class BaseStoreRequest extends ApiRequest
{
    /**
     * Kayıt için genel validasyon kurallarını tanımlar.
     * Alt sınıflar bu metodu override ederek kendi validasyon
     * kurallarını tanımlamalıdır.
     *
     * @return array
     */
    abstract protected function storeRules(): array;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = $this->storeRules();

        // Güncelleme işlemi için sometimes parametresi ekle
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            foreach ($rules as $field => $rule) {
                $rules[$field] = 'sometimes|' . $rule;
            }
        }

        return $rules;
    }

    /**
     * Validasyon hata mesajlarını tanımlar.
     * Alt sınıflar bu metodu override ederek kendi hata
     * mesajlarını tanımlayabilir.
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }
}
