<?php

declare(strict_types=1);

namespace App\Exceptions\Api;

use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ValidationException extends ApiException
{
    /**
     * Exception'ın HTTP durum kodu.
     *
     * @var int
     */
    protected int $statusCode = SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY;

    /**
     * Constructor.
     *
     * @param string $message
     * @param Validator|null $validator
     */
    public function __construct(string $message = 'Doğrulama Hatası', ?Validator $validator = null)
    {
        parent::__construct($message);

        if ($validator !== null) {
            $this->errors = $validator->errors();
        }
    }
}
