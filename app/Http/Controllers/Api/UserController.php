<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\User\PasswordRequest;
use App\Http\Requests\Api\User\ProfileRequest;
use App\Http\Resources\Profile\ProfileResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->model = new User();
        $this->modelName = 'Kullanıcı';
        $this->relationships = ['branch', 'zone', 'shift', 'followType'];
    }

    public function profile()
    {
        $user = Auth::user();

        $currentToken = JWTAuth::getToken()->get();
        $tokenData = [
            'access_token' => $currentToken,
            'refresh_token' => $currentToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_expires_in' => config('jwt.refresh_ttl') * 60
        ];

        $userResource = new ProfileResource($user, $tokenData);
        return ApiResponse::success(
            [$userResource],
            "Kullanıcı bilgileriniz başarıyla listelendi."
        );
    }

    public function profileUpdate(ProfileRequest $request)
    {
        $user = User::find(Auth::user()->id);
        $user->update($request->all());

        $currentToken = JWTAuth::getToken()->get();
        $tokenData = [
            'access_token' => $currentToken,
            'refresh_token' => $currentToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_expires_in' => config('jwt.refresh_ttl') * 60
        ];

        $userResource = new ProfileResource($user, $tokenData);
        return ApiResponse::success(
            [$userResource],
            "Kullanıcı bilgileriniz başarıyla güncellendi."
        );
    }

    public function passwordUpdate(PasswordRequest $request)
    {
        $user = User::find(Auth::user()->id);
        $user->password = Hash::make($request->password);
        $user->save();
        return ApiResponse::success(
            [],
            "Şifreniz başarıyla güncellendi."
        );
    }
}
