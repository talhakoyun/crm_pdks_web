<?php

namespace App\Http\Controllers\Backend;

use App\Models\Department;
use App\Models\Branch;
use App\Http\Requests\Backend\DepartmentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DepartmentController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'Departman';
        $this->page = 'department';
        $this->upload = 'department';
        $this->model = new Department();
        $this->request = new DepartmentRequest();
        $this->relation = ['user','company', 'branch'];
        $this->view = (object)array(
            'breadcrumb' => array(
                'Departmanlar' => route('backend.department_list'),
            ),
        );
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $isAdmin = $request->attributes->get('is_admin', false);
            $isSuperAdmin = $request->attributes->get('is_super_admin', false);
            $isCompanyOwner = $request->attributes->get('is_company_owner', false);
            $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
            $isBranchAdmin = $request->attributes->get('is_branch_admin', false);
            $isDepartmentAdmin = $request->attributes->get('is_department_admin', false);

            $companyId = $request->attributes->get('company_id');
            $branchId = $request->attributes->get('branch_id');
            $departmentId = $request->attributes->get('department_id');

            // Yetki seviyesine göre kullanıcı filtreleme
            $usersQuery = User::where('role_id', 6);

            if (!$isSuperAdmin && !$isAdmin) {
                // Normal kullanıcılar için company_id filtresi
                if ($companyId) {
                    $usersQuery->where('company_id', $companyId);
                }

                if (!$isCompanyOwner && !$isCompanyAdmin && $branchId) {
                    $usersQuery->where('branch_id', $branchId);

                    if (!$isBranchAdmin && $departmentId) {
                        $usersQuery->where('department_id', $departmentId);
                    }
                }
            }

            view()->share('users', $usersQuery->get());

            $branchesQuery = Branch::query();

            if (!$isSuperAdmin && !$isAdmin) {
                if ($companyId) {
                    $branchesQuery->where('company_id', $companyId);
                }

                if (!$isCompanyOwner && !$isCompanyAdmin && $branchId) {
                    $branchesQuery->where('id', $branchId);
                }
            }
            $branches = $branchesQuery->get();
            view()->share('branches', $branches);
            return $next($request);
        });
        parent::__construct();
    }

    public function datatableHook($obj)
    {
        return $obj->editColumn('branch_id', function ($item) {
            return $item->branch?->title ?? '-';
        })->editColumn('manager_id', function ($item) {
            return $item->user?->name . ' ' . $item->user?->surname ?? '-';
        })->editColumn('company_id', function ($item) {
            return $item->company?->name ?? '-';
        });
    }

    /**
     * Departmanları listelerken rol bazlı filtreleme yapalım
     */
    public function list(Request $request)
    {
        // Departmanlar için listQuery tanımlayalım
        $isAdmin = $request->attributes->get('is_admin', false);
        $isSuperAdmin = $request->attributes->get('is_super_admin', false);
        $isCompanyOwner = $request->attributes->get('is_company_owner', false);
        $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
        $isBranchAdmin = $request->attributes->get('is_branch_admin', false);
        $isDepartmentAdmin = $request->attributes->get('is_department_admin', false);

        $companyId = $request->attributes->get('company_id');
        $branchId = $request->attributes->get('branch_id');
        $departmentId = $request->attributes->get('department_id');
        $loggedInUserId = Auth::id();

        $this->listQuery = $this->model::query()->with(['branch', 'company', 'user']);

        // Süper Admin ve Admin haricinde şirket filtrelemesi uygula
        if (!$isSuperAdmin && !$isAdmin) {
            $this->listQuery->where('company_id', $companyId);

            // Şirket sahibi ve yetkilisi tüm şubelere erişebilir
            if (!$isCompanyOwner && !$isCompanyAdmin && $branchId) {
                $this->listQuery->where('branch_id', $branchId);

                // Departman yetkilisi sadece kendi departmanını görebilir
                if (!$isBranchAdmin && $isDepartmentAdmin && $departmentId) {
                    $this->listQuery->where('id', $departmentId);
                }
            }

            // Departman yetkilisi için created_by üzerinden erişim kontrolü (yedek)
            if ($isDepartmentAdmin && !in_array('department_id', $this->model->getFillable())) {
                // Departman yetkilisi kendi oluşturduğu departmanları görebilir
                $this->listQuery->where(function($query) use ($loggedInUserId) {
                    $query->where('manager_id', $loggedInUserId)
                          ->orWhere('created_by', $loggedInUserId);
                });
            }
        }

        return parent::list($request);
    }

    /**
     * Departman kaydı için özel kayıt işlemleri
     */
    public function saveHook(Request $request)
    {
        $params = $request->all();
        
        // Company_id kontrolü - eğer yoksa branch'tan al
        if (!isset($params['company_id']) && isset($params['branch_id'])) {
            $branch = Branch::find($params['branch_id']);
            if ($branch) {
                $params['company_id'] = $branch->company_id;
            }
        }

        return $params;
    }

    /**
     * Departman kaydı sonrası işlemler
     */
    public function saveBack($obj)
    {
        $request = request();

        // Eğer yönetici seçildiyse, o yöneticinin department_id'sini güncelle
        if ($request->has('manager_id') && $request->manager_id) {
            $manager = User::find($request->manager_id);
            if ($manager) {
                $manager->update(['department_id' => $obj->id]);
            }
        }

        return redirect()->route("backend." . $this->page . "_list")->with('success', 'Departman başarılı şekilde kaydedildi');
    }
}
