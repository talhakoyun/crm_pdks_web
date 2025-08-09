<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class Permission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($this->isRoleInvalid($user)) {
            Session::flush();
            Auth::logout();
            return redirect()->route('signin')->withError('Yetkiniz bulunmamaktadır!');
        }
        if (!$this->hasPermission($user)) {
            return redirect()->route('backend.index')->withError('Bu sayfaya erişim yetkiniz bulunmamaktadır!');
        }

        view()->share('onuser', $user);

        return $next($request);
    }

    protected function isRoleInvalid($user)
    {
        $role = $user->role;
        return is_null($role) || empty(json_decode($role->permissions, true));
    }

    protected function hasPermission($user)
    {
        $rolePermissions = json_decode($user->role->permissions ?? '[]', true);
        return in_array(Route::currentRouteName(), $rolePermissions);
    }
}
