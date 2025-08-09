<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Api\ApiException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use App\Http\Responses\ApiResponse;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        // OAuth exception kaldırıldı çünkü artık JWT kullanıyoruz

    ];

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });

        // Özel API exceptionlarını render etmek için
        $this->renderable(function (ApiException $e) {
            return $e->render();
        });

        // Standard Laravel exceptionlarını API formatına çevirmek için
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return $this->handleApiException($e);
            }
        });

        // Web istekleri için özel hata sayfalarını göstermek için
        /*$this->renderable(function (Throwable $e, Request $request) {
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return $this->handleWebException($e);
            }
        });*/
    }

    /**
     * API hatalarını işler
     *
     * @param Throwable $e
     * @return JsonResponse
     */
    private function handleApiException(Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return ApiResponse::validationError($e->errors());
        }

        if ($e instanceof AuthenticationException) {
            return ApiResponse::unauthorized();
        }

        if ($e instanceof TokenExpiredException) {
            return ApiResponse::unauthorized('Token süresi doldu');
        }

        if ($e instanceof TokenInvalidException) {
            return ApiResponse::unauthorized('Geçersiz token');
        }

        if ($e instanceof JWTException) {
            return ApiResponse::unauthorized('Token bulunamadı');
        }

        if ($e instanceof NotFoundHttpException) {
            return ApiResponse::notFound();
        }

        // Diğer tüm hataları genel sunucu hatası olarak işle
        $message = config('app.debug') ? $e->getMessage() : 'Sunucu Hatası';
        return ApiResponse::serverError($message);
    }

    /**
     * Web istekleri için hata sayfalarını işler
     *
     * @param Throwable $e
     * @return \Illuminate\Http\Response
     */
    private function handleWebException(Throwable $e)
    {
        $statusCode = 500;

        // HTTP exception'ları için durum kodunu al
        if ($e instanceof MaintenanceModeException) {
            return response()->view('maintenance', [], 503);
        } elseif ($e instanceof HttpExceptionInterface) {
            $statusCode = $e->getStatusCode();
        }

        // Tek bir hata sayfası kullan
        return response()->view('error', ['exception' => $e, 'statusCode' => $statusCode], $statusCode);
    }
}
