<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ApiResponse
{
    /**
     * Başarılı API yanıtı oluşturur.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    public static function success(mixed $data = null, string $message = 'İşlem Başarılı', int $statusCode = SymfonyResponse::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Hata API yanıtı oluşturur.
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @return JsonResponse
     */
    public static function error(string $message, int $statusCode = SymfonyResponse::HTTP_BAD_REQUEST, mixed $errors = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * 404 Not Found yanıtı oluşturur.
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Kayıt Bulunamadı', mixed $errors = null): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_NOT_FOUND, $errors);
    }

    /**
     * 422 Validation Error yanıtı oluşturur.
     *
     * @param mixed $errors
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError(mixed $errors, string $message = 'Doğrulama Hatası'): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * 401 Unauthorized yanıtı oluşturur.
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'Yetkisiz Erişim', mixed $errors = null): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_UNAUTHORIZED, $errors);
    }

    /**
     * 403 Forbidden yanıtı oluşturur.
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    public static function forbidden(string $message = 'Erişim Reddedildi', mixed $errors = null): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_FORBIDDEN, $errors);
    }

    /**
     * 500 Server Error yanıtı oluşturur.
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    public static function serverError(string $message = 'Sunucu Hatası', mixed $errors = null): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR, $errors);
    }
}
