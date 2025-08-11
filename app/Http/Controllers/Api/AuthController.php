<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\Profile\ProfileResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Auth servisi.
     *
     * @var AuthService
     */
    private AuthService $authService;

    /**
     * AuthController constructor.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Kullanıcı girişi yapar.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->authenticate(
            $request->only(['email', 'password']),
            $request->device_id
        );

        $tokenData = [
            'access_token' => $result['token'],
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ];
        $user = User::where('email', $request->email)->first();
        $userResource = new ProfileResource($user, $tokenData);

        return ApiResponse::success(
            [
                $userResource,
            ],
            'Giriş başarılı'
        );
    }

    /**
     * Kullanıcı çıkışı yapar.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();
        return ApiResponse::success([], 'Çıkış başarılı');
    }

    /**
     * Token'ı yeniler.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        $token = $this->authService->refreshToken();

        return ApiResponse::success(
            [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ],
            'Token yenilendi'
        );
    }

    /**
     * Mevcut kullanıcı bilgilerini döndürür.
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $user = $this->authService->getAuthenticatedUser();
        return ApiResponse::success([$user], 'Kullanıcı bilgileri');
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->all());
        return ApiResponse::success([$user], 'Kullanıcı kaydı başarılı');
    }
}
