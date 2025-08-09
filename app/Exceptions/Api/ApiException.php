<?php

declare(strict_types=1);

namespace App\Exceptions\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

abstract class ApiException extends Exception
{
    /**
     * Exception'ın HTTP durum kodu.
     *
     * @var int
     */
    protected int $statusCode = SymfonyResponse::HTTP_BAD_REQUEST;

    /**
     * Ek hata detayları.
     *
     * @var mixed
     */
    protected mixed $errors = null;

    /**
     * Exception'ı JsonResponse olarak render eder.
     *
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        switch ($this->statusCode) {
            case SymfonyResponse::HTTP_NOT_FOUND:
                return ApiResponse::notFound($this->getMessage(), $this->errors);
            case SymfonyResponse::HTTP_UNAUTHORIZED:
                return ApiResponse::unauthorized($this->getMessage(), $this->errors);
            case SymfonyResponse::HTTP_FORBIDDEN:
                return ApiResponse::forbidden($this->getMessage(), $this->errors);
            case SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY:
                return ApiResponse::validationError($this->errors, $this->getMessage());
            case SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR:
                return ApiResponse::serverError($this->getMessage(), $this->errors);
            default:
                return ApiResponse::error($this->getMessage(), $this->statusCode, $this->errors);
        }
    }

    /**
     * Ek hata detaylarını ayarlar.
     *
     * @param mixed $errors
     * @return self
     */
    public function withErrors(mixed $errors): self
    {
        $this->errors = $errors;
        return $this;
    }
}
