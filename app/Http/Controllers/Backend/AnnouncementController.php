<?php

namespace App\Http\Controllers\Backend;

use App\Models\Announcement;
use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use Carbon\Carbon;
use App\Http\Requests\Backend\AnnouncementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends BaseController
{
    public function __construct()
    {
        $this->model = new Announcement();
        $this->title = 'Duyuru';
        $this->page = 'announcements';
        $this->upload = 'announcements';
        $this->request = AnnouncementRequest::class;

        // Form verilerini hazırla
        $user = Auth::user();
        if ($user) {
            $isSuperAdmin = $user->role_id === 1;
            $isAdmin = $user->role_id === 2;
            $isCompanyOwner = $user->role_id === 3;
            $isCompanyAdmin = $user->role_id === 4;
            $isBranchAdmin = $user->role_id === 5;
            $isDepartmentAdmin = $user->role_id === 6;

            // Rolleri yetkiye göre filtrele
            $rolesQuery = Role::query();

            if ($isSuperAdmin) {
                // Süper admin tüm rolleri görebilir
            } elseif ($isAdmin) {
                // Admin, süper admin (1) hariç tüm rolleri görebilir
                $rolesQuery->where('id', '>', 1);
            } elseif ($isCompanyOwner) {
                // Şirket sahibi, kendinden düşük rolleri görebilir (3'ten büyük)
                $rolesQuery->where('id', '>', 3);
            } elseif ($isCompanyAdmin) {
                // Şirket yöneticisi sadece şube yetkilisi ve personel rollerini görebilir
                $rolesQuery->whereIn('id', [5, 6, 7]);
            } elseif ($isBranchAdmin) {
                // Şube yöneticisi sadece departman yetkilisi ve personel rolünü görebilir
                $rolesQuery->whereIn('id', [6, 7]);
            } elseif ($isDepartmentAdmin) {
                // Departman yöneticisi sadece personel rolünü görebilir
                $rolesQuery->where('id', 7);
            }

            view()->share('roleValues', $rolesQuery->get());

            // Şubeleri filtrele
            $branchesQuery = Branch::query();
            if (!$isSuperAdmin && !$isAdmin) {
                $branchesQuery->where('company_id', $user->company_id);

                if ($isBranchAdmin) {
                    $branchesQuery->where('id', $user->branch_id);
                }
            }
            view()->share('branches', $branchesQuery->get());

            // Departmanları filtrele
            $departmentsQuery = Department::query();
            if (!$isSuperAdmin && !$isAdmin) {
                $departmentsQuery->where('company_id', $user->company_id);

                if ($isBranchAdmin) {
                    $departmentsQuery->where('branch_id', $user->branch_id);
                } elseif ($isDepartmentAdmin) {
                    $departmentsQuery->where('id', $user->department_id);
                }
            }
            view()->share('departments', $departmentsQuery->get());
        }

        parent::__construct();
    }

    public function saveHook(Request $request)
    {
        $params = $request->all();

        // Tarihleri Carbon formatına çevir
        if (isset($params['start_date'])) {
            $params['start_date'] = Carbon::parse($params['start_date']);
        }
        if (isset($params['end_date'])) {
            $params['end_date'] = Carbon::parse($params['end_date']);
        }

        // Gönderim tipine göre kullanıcıları belirle
        $users = [];
        switch ($params['send_type']) {
            case 'all':
                $users = User::where('company_id', Auth::user()->company_id)->pluck('id')->toArray();
                break;

            case 'role':
                if ($params['role_user_type'] === 'all') {
                    $users = User::whereIn('role_id', $params['roles'])
                        ->where('company_id', Auth::user()->company_id)
                        ->pluck('id')
                        ->toArray();
                } else {
                    $users = $params['role_users'] ?? [];
                }
                break;
        }

        $params['users'] = $users;
        return $params;
    }

    /**
     * DataTables için verileri hazırlar
     */
    public function datatableHook($query)
    {
        return $query
            ->addColumn('send_type_text', function ($item) {
                switch ($item->send_type) {
                    case 'all':
                        return 'Tüm Kullanıcılar';
                    case 'role':
                        return 'Rol Bazlı';
                    default:
                        return '-';
                }
            })
            ->editColumn('title', function ($item) {
                return '<strong>' . $item->title . '</strong>';
            })
            ->editColumn('start_date', function ($item) {
                return $item->start_date ? Carbon::parse($item->start_date)->format('d.m.Y H:i') : '-';
            })
            ->editColumn('end_date', function ($item) {
                return $item->end_date ? Carbon::parse($item->end_date)->format('d.m.Y H:i') : '-';
            })
            ->editColumn('status', function ($item) {
                return $item->status ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Pasif</span>';
            })
            ->rawColumns(['title', 'status']);
    }

    /**
     * Seçilen kriterlere göre kullanıcıları getirir
     */
    public function getUsers(Request $request)
    {
        $type = $request->post('type');
        $ids = $request->post('ids', []);

        $query = User::where('company_id', Auth::user()->company_id);

        if ($type === 'role') {
            $query->whereIn('role_id', $ids);
        }

        return response()->json(
            $query->select('id', 'name', 'surname')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name . ' ' . $user->surname
                    ];
                })
        );
    }

    public function delete(Request $request)
    {
        $item = $this->model::find($request->post('id'));

        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Kayıt bulunamadı']);
        }

        try {
            $item->delete();
            return response()->json(['status' => true, 'message' => 'Kayıt başarıyla silindi']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Kayıt silinirken bir hata oluştu: ' . $e->getMessage()]);
        }
    }

    /**
     * Form sayfasını gösterir
     */
    public function form(Request $request, $unique = null)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role_id === 1;
        $isAdmin = $user->role_id === 2;
        $isCompanyOwner = $user->role_id === 3;
        $isCompanyAdmin = $user->role_id === 4;
        $isBranchAdmin = $user->role_id === 5;
        $isDepartmentAdmin = $user->role_id === 6;

        // Rolleri yetkiye göre filtrele
        $rolesQuery = Role::query();

        if ($isSuperAdmin) {
            // Süper admin tüm rolleri görebilir
        } elseif ($isAdmin) {
            // Admin, süper admin (1) hariç tüm rolleri görebilir
            $rolesQuery->where('id', '>', 1);
        } elseif ($isCompanyOwner) {
            // Şirket sahibi, kendinden düşük rolleri görebilir (3'ten büyük)
            $rolesQuery->where('id', '>', 3);
        } elseif ($isCompanyAdmin) {
            // Şirket yöneticisi sadece şube yetkilisi ve personel rollerini görebilir
            $rolesQuery->whereIn('id', [5, 6, 7]);
        } elseif ($isBranchAdmin) {
            // Şube yöneticisi sadece departman yetkilisi ve personel rolünü görebilir
            $rolesQuery->whereIn('id', [6, 7]);
        } elseif ($isDepartmentAdmin) {
            // Departman yöneticisi sadece personel rolünü görebilir
            $rolesQuery->where('id', 7);
        }

        view()->share('roleValues', $rolesQuery->get());

        // Şubeleri filtrele
        $branchesQuery = Branch::query();
        if (!$isSuperAdmin && !$isAdmin) {
            $branchesQuery->where('company_id', $user->company_id);

            if ($isBranchAdmin) {
                $branchesQuery->where('id', $user->branch_id);
            }
        }
        view()->share('branches', $branchesQuery->get());

        // Departmanları filtrele
        $departmentsQuery = Department::query();
        if (!$isSuperAdmin && !$isAdmin) {
            $departmentsQuery->where('company_id', $user->company_id);

            if ($isBranchAdmin) {
                $departmentsQuery->where('branch_id', $user->branch_id);
            } elseif ($isDepartmentAdmin) {
                $departmentsQuery->where('id', $user->department_id);
            }
        }
        view()->share('departments', $departmentsQuery->get());

        return parent::form($request, $unique);
    }
}
