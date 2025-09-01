<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RoleController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'Rol';
        $this->page = 'role';
        $this->model = new Role();

        $this->view = (object)array(
            'breadcrumb' => array(
                'Ayarlar' => '#',
                'Roller' => route('backend.role_list'),
            ),
        );

        View::share('routes', Route::all()->groupBy('category_name'));
        parent::__construct();
    }

    public function list(Request $request)
    {
        if ($request->has('datatable')) {
            // Yetki kontrolü
            $user = Auth::user();
            $isSuperAdmin = $user->role_id == 1;
            $isAdmin = $user->role_id == 2;
            $isCompanyOwner = $user->role_id == 3;
            $loggedInRoleId = $user->role_id;

            // Role tablosu için şirket bazlı filtreleme yapmıyoruz
            $select = Role::select();

            // Rol bazlı filtreleme
            if (!$isSuperAdmin) {
                if ($isAdmin || $isCompanyOwner) {
                    // Admin ve Şirket Sahibi, süper admin (1) hariç tüm rolleri görebilir
                    $select->where('id', '>', 1);
                } else {
                    // Diğer kullanıcılar sadece kendinden düşük rolleri görebilir
                    $select->where('id', '>', $loggedInRoleId);
                }
            }

            $obj = DataTables::of($select);

            $obj = $obj
                ->editColumn('created_at', function ($item) {
                    return (!is_null($item->created_at) ? Carbon::parse($item->created_at)->format('d.m.Y H:i') : '-');
                })
                ->editColumn('updated_at', function ($item) {
                    return (!is_null($item->updated_at) ? Carbon::parse($item->updated_at)->format('d.m.Y H:i') : '-');
                })
                ->editColumn('is_active', function ($item) {
                    return $item->is_active == 1 ? '<span class="bg-success-focus text-success-600 border border-success-main px-24 py-4 radius-4 fw-medium text-sm"> Aktif </span>' : '<span class="bg-danger-focus text-danger-600 border border-danger-main px-24 py-4 radius-4 fw-medium text-sm"> Pasif </span>';
                })
                ->rawColumns(['is_active'])
                ->escapeColumns([]);

            // Özelleştirmeler varsa uygulayalım
            if (method_exists($this, 'datatableHook')) {
                $obj = $this->datatableHook($obj);
            }

            $obj = $obj->addIndexColumn()->make(true);

            return $obj;
        }

        return view("backend.$this->page.list");
    }

    public function delete(Request $request)
    {
        $restrictedIds = [1, 2, 3, 4, 5, 6, 7]; // İlk 7 role silinemez

        $roleId = (int) $request->post('id');
        if (in_array($roleId, $restrictedIds)) {
            return response()->json(['status' => false, 'message' => 'Bu roller silinemez.']);
        }

        $role = $this->model::find($roleId);

        if (!is_null($role)) {
            $role->delete();
        } else {
            return response()->json(['status' => false, 'message' => 'Kayıt bulunamadı']);
        }

        return response()->json(['status' => true]);
    }

    public function form(Request $request, $unique = NULL)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role_id == 1;
        $isAdmin = $user->role_id == 2;
        $isCompanyOwner = $user->role_id == 3;
        $loggedInRoleId = $user->role_id;

        if (!is_null($unique)) {
            // Mevcut kayıt
            $item = $this->model::find((int)$unique);

            // Kayıt bulunamadı
            if (is_null($item)) {
                return redirect()->back()->with('error', 'Kayıt bulunamadı');
            }

            // Rol düzenleme yetkisi kontrolü
            if (!$isSuperAdmin) {
                if ($isAdmin || $isCompanyOwner) {
                    // Admin ve Şirket Sahibi, süper admin (1) rolünü düzenleyemez
                    if ($item->id == 1) {
                        return redirect()->back()->with('error', 'Bu rolü düzenleme yetkiniz bulunmamaktadır.');
                    }
                } else {
                    // Diğer kullanıcılar sadece kendinden düşük rolleri düzenleyebilir
                    if ($item->id <= $loggedInRoleId) {
                        return redirect()->back()->with('error', 'Bu rolü düzenleme yetkiniz bulunmamaktadır.');
                    }
                }
            }

            // Form hook varsa uygula
            if (method_exists($this, 'formHook')) {
                $item = $this->formHook($item);
            }
        } else {
            // Yeni kayıt
            $item = new $this->model;
        }

        return view("backend.$this->page.form", compact('item'));
    }

    public function saveHook(Request $request)
    {
        $params = $request->all();
        if (isset($params['permissions']) && !is_null($params['permissions'])) {
            $params['permissions'] = json_encode($params['permissions']);
        }

        return $params;
    }
}
