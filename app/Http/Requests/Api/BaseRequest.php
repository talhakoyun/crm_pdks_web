<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

abstract class BaseRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules(): array;

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