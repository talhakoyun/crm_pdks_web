<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\LoginRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    public function forgotPassword()
    {
        return view('authentication/forgotPassword');
    }

    public function signIn()
    {
        return view('authentication/signin');
    }

    public function signUp()
    {
        return view('authentication/signUp');
    }

    public function access(LoginRequest $request)
    {
        $rememberme = $request->rememberme;
        $credentials = $request->only('email', 'password');
        if (Auth::guard('user')->attempt($credentials, $rememberme)) {
            if (Auth::guard('user')->user()->is_active == 0) {
                Auth::guard('user')->logout();

                return redirect()->route('signin')->withError('Hesabınız pasif durumda');
            }

            // Role ID 5, 6, 7 olan kullanıcıların panel erişimini engelle
            if (in_array(Auth::guard('user')->user()->role_id, [5, 6, 7])) {
                Auth::guard('user')->logout();

                return redirect()->route('signin')->withError('Bu hesap ile panele giriş yapamazsınız!');
            }

            $exits = User::find(Auth::guard('user')->user()->id);
            $exits->last_login =  Carbon::now();
            // $exits->ip =  $request->ip();
            $exits->save();

            return redirect()->route('backend.index');
        }
        return redirect()->route('signin')->withError('Hesap bilgileri yanlış!');
    }

    public function logout()
    {
        Session::flush();
        Auth::guard('user')->logout();
        return redirect()->route('signin');
    }
}
