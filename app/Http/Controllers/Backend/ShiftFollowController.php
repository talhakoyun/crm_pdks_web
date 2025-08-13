<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\ShiftFollowRequest;
use App\Models\Branch;
use App\Models\ShiftDefinition;
use App\Models\ShiftFollow;
use App\Models\ShiftFollowType;
use App\Models\User;
use App\Models\UserBranches;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftFollowController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'Vardiya Takibi';
        $this->page = 'shift_follow';
        $this->upload = 'shift_follow';
        $this->model = new ShiftFollow();
        $this->request = new ShiftFollowRequest();
        $this->relation = ['user', 'followType', 'company', 'branch', 'enterBranch'];
        $this->view = (object)array(
            'breadcrumb' => array(
                'Vardiya Takibi' => route('backend.shift_follow_list'),
            ),
        );

        // Middleware ile rol bazlı erişim kontrolleri
        $this->middleware(function ($request, $next) {
            $role = Auth::user()->role_id;
            $isAdmin = $role == 2;
            $isSuperAdmin = $role == 1;
            $isCompanyOwner = $role == 3;
            $isCompanyAdmin = $role == 4;
            $isBranchAdmin = $role == 5;
            $isDepartmentAdmin = $role == 6;

            $companyId = $request->attributes->get('company_id');
            $branchId = $request->attributes->get('branch_id');
            $departmentId = $request->attributes->get('department_id');
            // Kullanıcıların rol bazlı filtrelenmesi
            $usersQuery = User::where('role_id', 7)->active();

            if (!$isSuperAdmin && !$isAdmin) {
                $usersQuery->where('company_id', $companyId);

                if (!$isCompanyOwner && !$isCompanyAdmin) {
                    if ($branchId) {
                        $usersQuery->where('branch_id', $branchId);

                        if (!$isBranchAdmin && $departmentId) {
                            $usersQuery->where('department_id', $departmentId);
                        }
                    }
                }
            }

            view()->share('users', $usersQuery->get());
            view()->share('followTypes', ShiftFollowType::all());
            view()->share('shifts', ShiftDefinition::all());

            return $next($request);
        });

        parent::__construct();
    }

    /**
     * Vardiya takip listesini rol bazlı filtreleme
     */
    public function list(Request $request)
    {
        // Vardiya takiplerini rol bazlı filtreleme için özel sorgu oluşturalım
        $role = Auth::user()->role_id;
        $isAdmin = $role == 2;
        $isSuperAdmin = $role == 1;
        $isCompanyOwner = $role == 3;
        $isCompanyAdmin = $role == 4;
        $isBranchAdmin = $role == 5;
        $isDepartmentAdmin = $role == 6;

        $companyId = $request->attributes->get('company_id');
        $branchId = $request->attributes->get('branch_id');
        $departmentId = $request->attributes->get('department_id');
        $userId = $request->attributes->get('user_id');
        $loggedInUserId = Auth::id();

        $this->listQuery = $this->model::query()->with($this->relation);

        // Süper Admin ve Admin haricinde şirket filtrelemesi uygula
        if (!$isSuperAdmin && !$isAdmin) {
            $this->listQuery->where('company_id', $companyId);

            // Şirket sahibi ve yetkilisi tüm şubelere erişebilir
            if (!$isCompanyOwner && !$isCompanyAdmin && $branchId) {
                $this->listQuery->where('branch_id', $branchId);

                // Şube yetkilisi tüm departmanlara erişebilir
                if (!$isBranchAdmin && $departmentId) {
                    $this->listQuery->where('department_id', $departmentId);

                    // Departman yetkilisi tüm kullanıcılara erişebilir
                    if (!$isDepartmentAdmin && $userId) {
                        $this->listQuery->where('user_id', $userId);
                    }
                }
            }

            // Departman yetkilisi için created_by üzerinden erişim kontrolü
            if ($isDepartmentAdmin) {
                // Departman yetkilisi sadece kendi kaydettiklerini veya departmanındaki
                // kullanıcıların oluşturduğu kayıtları görebilir
                $departmentUserIds = User::where('department_id', $departmentId)->pluck('id')->toArray();

                $this->listQuery->where(function($query) use ($loggedInUserId, $departmentUserIds) {
                    $query->whereIn('created_by', $departmentUserIds)
                          ->orWhere('created_by', $loggedInUserId);
                });
            }
        }

        // Tarih filtresi varsa uygula
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();

            $this->listQuery->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        return parent::list($request);
    }

    /**
     * Personel seçildiğinde şubelerini getiren AJAX endpoint
     */
    public function getUserBranches(Request $request)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json(['branches' => []]);
        }

        // Kullanıcının izin verilen şubelerini al
        $userBranches = UserBranches::where('user_id', $userId)
            ->with('branch')
            ->get()
            ->pluck('branch')
            ->filter()
            ->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'title' => $branch->title
                ];
            });

        return response()->json(['branches' => $userBranches]);
    }

    /**
     * Kayıt işlemi için özel hook
     */
    public function saveHook(Request $request)
    {
        $params = $request->all();

        // Company_id kontrolü
        $isAdmin = $request->attributes->get('is_admin', false);
        if (!$isAdmin) {
            $params['company_id'] = $request->attributes->get('company_id');
        }

        // Created_by alanını ekle
        $params['created_by'] = Auth::id();

        return $params;
    }

    /**
     * Datatable için sütunları özelleştirelim
     */
    public function datatableHook($obj)
    {
        return $obj->editColumn('user_id', function ($item) {
            return $item->user?->name . ' ' . $item->user?->surname;
        })->editColumn('shift_follow_type_id', function ($item) {
            return $item->followType?->title;
        })->editColumn('shift_id', function ($item) {
            if ($item->shift) {
                return $item->shift->title . ' (' . $item->shift->start_time . ' - ' . $item->shift->end_time . ')';
            }
            return '-';
        })->editColumn('transaction_date', function ($item) {
            return Carbon::parse($item->transaction_date)->format('d.m.Y H:i:s');
        })->addColumn('company_name', function ($item) {
            return $item->company?->name;
        })->addColumn('branch_name', function ($item) {
            return $item->branch?->title;
        })->addColumn('enter_branch_name', function ($item) {
            return $item->enterBranch?->title;
        })->addColumn('department_name', function ($item) {
            return $item->department?->title;
        });
    }

    // Bu stil ve script'i sayfa layout'unuza eklemelisiniz
    private function addCustomStyles()
    {
        return '
        <style>
            .avatar-circle {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
            }

            .datatable-row {
                transition: all 0.2s;
            }

            .datatable-row:hover {
                background-color: rgba(0,0,0,0.03);
            }

            table.dataTable thead th {
                position: relative;
                background-image: none !important;
            }

            table.dataTable thead th.sorting:after,
            table.dataTable thead th.sorting_asc:after,
            table.dataTable thead th.sorting_desc:after {
                position: absolute;
                right: 8px;
                display: inline-block;
                font-size: 1rem;
            }

            table.dataTable thead th.sorting:after {
                content: "";
                opacity: 0.5;
                font-family: "Material Design Icons";
            }

            table.dataTable thead th.sorting_asc:after {
                content: "";
                font-family: "Material Design Icons";
            }

            table.dataTable thead th.sorting_desc:after {
                content: "";
                font-family: "Material Design Icons";
            }
        </style>
        ';
    }
}
