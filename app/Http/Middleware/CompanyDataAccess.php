<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyDataAccess
{
    /**
     * Role bazlı company verilerine erişim kontrolü.
     *
     * Role_id'ye göre yetki seviyeleri (hiyerarşik sırada):
     * 1 - Süper Admin: Tüm verilere tam erişim
     * 2 - Admin: Tüm verilere tam erişim (süper admin hariç)
     * 3 - Şirket Sahibi: Kendi şirketine ait tüm verilere erişim
     * 4 - Şirket Yetkilisi: Kendi şirketine ait kısıtlı veri erişimi
     * 5 - Şube Yetkilisi: Kendi şubesine ait kısıtlı veri erişimi, şubeye ait tüm departmanlara erişebilir
     * 6 - Departman Yetkilisi: Sadece kendi departmanına ait veriler
     * 7 - Personel: Sadece kendisine ait veriler
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('user')->user();

        if (!$user) {
            return redirect()->route('signin');
        }

        // Role_id değerini integer olarak alalım
        $roleId = (int)$user->role_id;
        $companyId = (int)$user->company_id;
        $branchId = (int)$user->branch_id ?? 0;
        $departmentId = (int)$user->department_id ?? 0;
        $userId = (int)$user->id;

        // Role_id bazlı yetkilendirme
        switch ($roleId) {
            case 1: // Süper Admin
                $request->attributes->add([
                    'is_super_admin' => true,
                    'is_admin' => true,
                    'is_company_owner' => true,
                    'is_company_admin' => true,
                    'is_branch_admin' => true,
                    'is_department_admin' => true,
                    'company_id' => null, // Tüm şirketlere erişim
                    'branch_id' => null, // Tüm şubelere erişim
                    'department_id' => null, // Tüm departmanlara erişim
                    'user_id' => null, // Tüm kullanıcılara erişim
                    'role_id' => $roleId
                ]);
                break;

            case 2: // Admin
                $request->attributes->add([
                    'is_super_admin' => false,
                    'is_admin' => true,
                    'is_company_owner' => true,
                    'is_company_admin' => true,
                    'is_branch_admin' => true,
                    'is_department_admin' => true,
                    'company_id' => null, // Tüm şirketlere erişim
                    'branch_id' => null, // Tüm şubelere erişim
                    'department_id' => null, // Tüm departmanlara erişim
                    'user_id' => null, // Tüm kullanıcılara erişim
                    'role_id' => $roleId
                ]);
                break;

            case 3: // Şirket Sahibi
                $request->attributes->add([
                    'is_super_admin' => false,
                    'is_admin' => false,
                    'is_company_owner' => true,
                    'is_company_admin' => true,
                    'is_branch_admin' => true,
                    'is_department_admin' => true,
                    'company_id' => $companyId, // Sadece kendi şirketine erişim
                    'branch_id' => null, // Şirketindeki tüm şubelere erişim
                    'department_id' => null, // Şirketindeki tüm departmanlara erişim
                    'user_id' => null, // Şirketindeki tüm kullanıcılara erişim
                    'role_id' => $roleId
                ]);
                break;

            case 4: // Şirket Yetkilisi
                $request->attributes->add([
                    'is_super_admin' => false,
                    'is_admin' => false,
                    'is_company_owner' => false,
                    'is_company_admin' => true,
                    'is_branch_admin' => true,
                    'is_department_admin' => true,
                    'company_id' => $companyId, // Sadece kendi şirketine erişim
                    'branch_id' => null, // Şirketindeki tüm şubelere erişim
                    'department_id' => null, // Şirketindeki tüm departmanlara erişim
                    'user_id' => null, // Şirketindeki tüm kullanıcılara erişim
                    'role_id' => $roleId
                ]);
                break;

            case 5: // Şube Yetkilisi
                $request->attributes->add([
                    'is_super_admin' => false,
                    'is_admin' => false,
                    'is_company_owner' => false,
                    'is_company_admin' => false,
                    'is_branch_admin' => true,
                    'is_department_admin' => true,
                    'company_id' => $companyId, // Sadece kendi şirketine erişim
                    'branch_id' => $branchId, // Sadece kendi şubesine erişim
                    'department_id' => null, // Şubesindeki tüm departmanlara erişim
                    'user_id' => null, // Şubesindeki tüm kullanıcılara erişim
                    'role_id' => $roleId
                ]);
                break;

            case 6: // Departman Yetkilisi
                $request->attributes->add([
                    'is_super_admin' => false,
                    'is_admin' => false,
                    'is_company_owner' => false,
                    'is_company_admin' => false,
                    'is_branch_admin' => false,
                    'is_department_admin' => true,
                    'company_id' => $companyId, // Sadece kendi şirketine erişim
                    'branch_id' => $branchId, // Sadece kendi şubesine erişim
                    'department_id' => $departmentId, // Sadece kendi departmanına erişim
                    'user_id' => null, // Kendi departmanındaki tüm kullanıcılara erişim
                    'role_id' => $roleId
                ]);
                break;

            case 7: // Personel
            default:
                $request->attributes->add([
                    'is_super_admin' => false,
                    'is_admin' => false,
                    'is_company_owner' => false,
                    'is_company_admin' => false,
                    'is_branch_admin' => false,
                    'is_department_admin' => false,
                    'company_id' => $companyId, // Sadece kendi şirketine erişim
                    'branch_id' => $branchId, // Sadece kendi şubesine erişim
                    'department_id' => $departmentId, // Sadece kendi departmanına erişim
                    'user_id' => $userId, // Sadece kendisine erişim
                    'role_id' => $roleId
                ]);
                break;
        }

        return $next($request);
    }
}
