<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\User\PasswordRequest;
use App\Http\Requests\Api\User\ProfileRequest;
use App\Http\Resources\Profile\ProfileResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        $userResource = new ProfileResource($user);
        return ApiResponse::success(
            [$userResource],
            "Kullanıcı bilgileriniz başarıyla listelendi."
        );
    }

    public function profileUpdate(ProfileRequest $request)
    {
        $user = User::find(Auth::user()->id);
        $user->update($request->all());
        $userResource = new ProfileResource($user);
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
