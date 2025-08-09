<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Api\ForbiddenException;
use App\Exceptions\Api\ServerException;
use App\Exceptions\Api\UnauthorizedException;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * İzin verilen rol kimlikleri.
     *
     * @var array
     */
    private array $allowedRoles = [5, 6, 7];

    /**
     * Kullanıcı kimliğini doğrular.
     *
     * @param array $credentials
     * @param string $deviceId
     * @return array
     *
     * @throws UnauthorizedException
     * @throws ForbiddenException
     * @throws ServerException
     */
    public function authenticate(array $credentials, string $deviceId): array
    {
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new UnauthorizedException('Geçersiz kullanıcı adı veya şifre');
            }

            $user = JWTAuth::user();

            // Kullanıcı rolü kontrolü
            if (!$this->hasValidRole($user)) {
                throw new UnauthorizedException('Bu alana erişim yetkiniz yok');
            }

            // Cihaz kimliği kontrolü
            $this->validateDeviceId($user, $deviceId);

            return [
                'token' => $token,
                'user' => $user
            ];
        } catch (UnauthorizedException | ForbiddenException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ServerException('Kimlik doğrulama sırasında bir hata oluştu', $e);
        }
    }

    /**
     * Kullanıcı oturumunu sonlandırır.
     *
     * @return void
     *
     * @throws ServerException
     */
    public function logout(): void
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (Exception $e) {
            throw new ServerException('Çıkış yapılırken bir hata oluştu', $e);
        }
    }

    /**
     * Token'ı yeniler.
     *
     * @return string
     *
     * @throws ServerException
     */
    public function refreshToken(): string
    {
        try {
            return JWTAuth::refresh(JWTAuth::getToken());
        } catch (Exception $e) {
            throw new ServerException('Token yenilenirken bir hata oluştu', $e);
        }
    }

    /**
     * Mevcut kullanıcıyı getirir.
     *
     * @return User
     *
     * @throws UnauthorizedException
     * @throws ServerException
     */
    public function getAuthenticatedUser(): User
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                throw new UnauthorizedException('Kullanıcı bulunamadı');
            }

            return $user;
        } catch (Exception $e) {
            throw new ServerException('Kullanıcı bilgileri alınırken bir hata oluştu', $e);
        }
    }

    /**
     * Kullanıcının geçerli bir rolü olup olmadığını kontrol eder.
     *
     * @param User $user
     * @return bool
     */
    private function hasValidRole(User $user): bool
    {
        return in_array($user->role_id, $this->allowedRoles);
    }

    /**
     * Cihaz kimliğini doğrular ve gerekirse günceller.
     *
     * @param User $user
     * @param string $deviceId
     * @return void
     *
     * @throws ForbiddenException
     */
    private function validateDeviceId(User $user, string $deviceId): void
    {
        if ($user->device_id && $user->device_id != $deviceId) {
            throw new ForbiddenException('Cihazınız sistemimizdeki cihaz ile eşleşmemekte, lütfen yöneticinize danışınız.');
        }

        if (!$user->device_id) {
            $user->update(['device_id' => $deviceId]);
        }
    }

    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['role_id'] = 7;
        $data['is_register'] = 1;
        unset($data['password_confirmation']);
        return User::create($data);
    }
}
