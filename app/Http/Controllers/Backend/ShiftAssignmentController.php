<?php

namespace App\Http\Controllers\Backend;

use App\Models\UserShift;
use App\Models\User;
use App\Models\ShiftDefinition;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftAssignmentController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'Vardiya Atamaları';
        $this->page = 'shift_assignment';
        $this->upload = 'shift_assignment';
        $this->model = new UserShift();
        $this->relation = ['user', 'shiftDefinition', 'user.branch', 'user.department'];
        $this->view = (object)array(
            'breadcrumb' => array(
                'Vardiya Atamaları' => route('backend.shift_assignment_list'),
            ),
        );
        parent::__construct();
    }

    /**
     * Liste için özel filtreleme - Vardiya bazlı gruplandırma
     */
    public function list(Request $request)
    {
        $user = Auth::user();

        // Vardiya bazlı gruplandırılmış veri hazırla
        $shiftsQuery = ShiftDefinition::with(['userShifts' => function($q) use ($user) {
            $q->where('is_active', 1);

            // Role_id 1, 2 ve 3 olan kullanıcıları filtrele
            $q->whereHas('user', function($subQ) {
                $subQ->whereNotIn('role_id', [1, 2, 3]);
            });

            // Rol bazlı filtreleme
            if (!in_array($user->role_id, [1, 2])) {
                $q->whereHas('user', function($subQ) use ($user) {
                    $subQ->where('company_id', $user->company_id);

                    if (!in_array($user->role_id, [3, 4]) && $user->branch_id) {
                        $subQ->where('branch_id', $user->branch_id);
                    }
                });
            }
        }, 'userShifts.user', 'userShifts.user.branch', 'userShifts.user.department']);

        // Şirket bazlı filtreleme
        if (!in_array($user->role_id, [1, 2])) {
            $shiftsQuery->where('company_id', $user->company_id);
        }

        $shifts = $shiftsQuery->get();

        // Sadece kullanıcısı olan vardiyaları filtrele
        $shifts = $shifts->filter(function($shift) {
            return $shift->userShifts->count() > 0;
        });

        // DataTables için uygun format
        if ($request->ajax()) {
            $data = [];
            foreach ($shifts as $shift) {
                $data[] = [
                    'id' => $shift->id,
                    'shift_name' => $shift->title,
                    'user_count' => $shift->userShifts->count(),
                    'working_days' => $shift->getWorkingDays(),
                    'weekly_hours' => $shift->getWeeklyWorkingHours(),
                    'schedule' => $shift->getWeeklySchedule(),
                    'users' => $shift->userShifts->map(function($userShift) {
                        return [
                            'id' => $userShift->user->id,
                            'name' => $userShift->user->name . ' ' . $userShift->user->surname,
                            'email' => $userShift->user->email,
                            'branch' => $userShift->user->branch?->title ?? '-',
                            'department' => $userShift->user->department?->title ?? '-'
                        ];
                    })
                ];
            }

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data),
                'data' => $data
            ]);
        }

        return view('backend.shift_assignment.list');
    }

    /**
     * Datatable için sütunları özelleştir
     */
    public function datatableHook($obj)
    {
        return $obj->addColumn('user_name', function ($row) {
            return $row->user?->name . ' ' . $row->user?->surname;
        })->addColumn('user_email', function ($row) {
            return $row->user?->email;
        })->addColumn('branch_name', function ($row) {
            return $row->user->branch?->title ?? '-';
        })->addColumn('department_name', function ($row) {
            return $row->user->department?->title ?? '-';
        })->addColumn('shift_name', function ($row) {
            return $row->shiftDefinition?->title ?? '-';
        })->rawColumns([]);
    }

    /**
     * Vardiya atama formu
     */
    public function form(Request $request, $id = null)
    {
        $user = Auth::user();

        // Kullanıcının yetkisine göre şube ve departmanları filtrele
        $branches = $this->getFilteredBranches($user);
        $departments = $this->getFilteredDepartments($user);

        // Vardiya tanımlarını şirket bazlı filtrele
        $shiftDefinitionsQuery = ShiftDefinition::active();
        if (!in_array($user->role_id, [1, 2])) {
            $shiftDefinitionsQuery->where('company_id', $user->company_id);
        }
        $shiftDefinitions = $shiftDefinitionsQuery->get();

        $editShift = null;
        $selectedUsers = [];

        // Eğer edit_shift parametresi varsa, o vardiyaya atanmış kullanıcıları getir
        if ($request->has('edit_shift')) {
            $shiftId = $request->get('edit_shift');
            $editShift = ShiftDefinition::find($shiftId);

            if ($editShift) {
                // O vardiyaya atanmış kullanıcıları getir
                $userShifts = UserShift::with('user')
                    ->where('shift_definition_id', $shiftId)
                    ->where('is_active', 1)
                    ->whereHas('user', function($q) {
                        $q->whereNotIn('role_id', [1, 2, 3]);
                    })
                    ->get();

                $selectedUsers = $userShifts->map(function($userShift) use ($editShift) {
                    return [
                        'id' => $userShift->user->id,
                        'name' => $userShift->user->name . ' ' . $userShift->user->surname,
                        'email' => $userShift->user->email,
                        'branch' => $userShift->user->branch?->title ?? '-',
                        'department' => $userShift->user->department?->title ?? '-',
                        'current_shift' => $editShift ? $editShift->title : 'Atanmamış'
                    ];
                })->toArray();
            }
        }

        return view('backend.shift_assignment.form', compact(
            'branches',
            'departments',
            'shiftDefinitions',
            'editShift',
            'selectedUsers'
        ));
    }

    /**
     * Kullanıcıları AJAX ile getir
     */
    public function getUsers(Request $request)
    {
        $user = Auth::user();
        $query = User::query()->where('is_active', 1);

        // Role_id 1, 2 ve 3 olanları listeden çıkar (Süper Admin, Admin, Şirket Sahibi)
        $query->whereNotIn('role_id', [1, 2, 3]);

        // Rol bazlı filtreleme
        if (!in_array($user->role_id, [1, 2])) { // Süper admin ve admin değilse
            $query->where('company_id', $user->company_id);

            if (!in_array($user->role_id, [3, 4]) && $user->branch_id) { // Şirket sahibi/yetkilisi değilse
                $query->where('branch_id', $user->branch_id);
            }
        }

        // Şube filtresi
        if ($request->has('branch_id') && $request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        // Departman filtresi
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        // Arama filtresi
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('surname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->with(['branch', 'department', 'userShift.shiftDefinition'])
                      ->select('id', 'name', 'surname', 'email', 'branch_id', 'department_id')
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name . ' ' . $user->surname,
                    'branch' => $user->branch?->title ?? '-',
                    'department' => $user->department?->title ?? '-',
                    'current_shift' => $user->userShift?->shiftDefinition?->title ?? 'Atanmamış'
                ];
            })
        ]);
    }

    /**
     * Vardiya atama kaydet - BaseController save methodunu kullan
     */
    public function assignShift(Request $request)
    {
        return $this->handleBulkAssignment($request);
    }



    /**
     * Vardiya atamasını güncelle
     */
    public function updateAssignment(Request $request, $id)
    {
        $request->validate([
            'shift_definition_id' => 'required|exists:shift_definitions,id'
        ], [
            'shift_definition_id.required' => 'Vardiya seçimi zorunludur.',
            'shift_definition_id.exists' => 'Geçersiz vardiya seçimi.'
        ]);

        try {
            $userShift = UserShift::findOrFail($id);
            $userShift->update([
                'shift_definition_id' => $request->shift_definition_id,
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vardiya ataması başarıyla güncellendi.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vardiya ataması güncellenirken hata oluştu.'
            ], 500);
        }
    }

    /**
     * BaseController save methodunu kullanmak için saveHook
     */
    public function saveHook(Request $request)
    {
        // Vardiya atama işlemi için özel validasyon
        $params = $request->all();

        // Eğer user_ids array'i geliyorsa, özel işlem yap
        if (isset($params['user_ids']) && is_array($params['user_ids'])) {
            return $this->handleBulkAssignment($request);
        }

        return $params;
    }

    /**
     * BaseController save methodunu kullanmak için saveBack
     */
    public function saveBack($obj)
    {
        // Tek vardiya ataması için işlem
        return $obj;
    }

    /**
     * Toplu vardiya atama işlemi
     */
    private function handleBulkAssignment(Request $request)
    {
        $request->validate([
            'shift_definition_id' => 'required|exists:shift_definitions,id',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id'
        ], [
            'shift_definition_id.required' => 'Vardiya seçimi zorunludur.',
            'shift_definition_id.exists' => 'Geçersiz vardiya seçimi.',
            'user_ids.required' => 'En az bir kullanıcı seçmelisiniz.',
            'user_ids.min' => 'En az bir kullanıcı seçmelisiniz.',
            'user_ids.*.exists' => 'Geçersiz kullanıcı seçimi.'
        ]);

        try {
            DB::beginTransaction();

            $assignedCount = 0;
            $updatedCount = 0;

            foreach ($request->user_ids as $userId) {
                // Mevcut vardiya kaydını kontrol et
                $existingShift = UserShift::where('user_id', $userId)->first();

                if ($existingShift) {
                    // Güncelle
                    $existingShift->update([
                        'shift_definition_id' => $request->shift_definition_id,
                        'is_active' => 1,
                        'updated_at' => now()
                    ]);
                    $updatedCount++;
                } else {
                    // Yeni kayıt oluştur
                    UserShift::create([
                        'user_id' => $userId,
                        'shift_definition_id' => $request->shift_definition_id,
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $assignedCount++;
                }
            }

            DB::commit();

            $message = "Vardiya atama başarılı! ";
            if ($assignedCount > 0) {
                $message .= "{$assignedCount} yeni atama, ";
            }
            if ($updatedCount > 0) {
                $message .= "{$updatedCount} güncelleme yapıldı.";
            }

            return response()->json([
                'success' => true,
                'message' => trim($message, ', ')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Vardiya atama sırasında hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * BaseController delete methodunu override et
     */
    public function delete(Request $request)
    {
        try {
            // Eğer shift_id varsa, o vardiyaya atanmış tüm kullanıcıları kaldır
            if ($request->has('shift_id')) {
                $shiftId = $request->input('shift_id');
                $deletedCount = UserShift::where('shift_definition_id', $shiftId)
                    ->where('is_active', 1)
                    ->update(['is_active' => 0]);

                return response()->json([
                    'success' => true,
                    'message' => "{$deletedCount} kullanıcının vardiya ataması başarıyla kaldırıldı."
                ]);
            }

            // Geleneksel ids ile silme (eski method)
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silinecek kayıt bulunamadı.'
                ], 400);
            }

            $deletedCount = 0;
            foreach ($ids as $id) {
                $userShift = UserShift::find($id);
                if ($userShift) {
                    $userShift->update(['is_active' => 0]);
                    $deletedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} vardiya ataması başarıyla kaldırıldı."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vardiya ataması kaldırılırken hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vardiya atamasını kaldır - Eski method (geriye uyumluluk için)
     */
    public function removeAssignment($id)
    {
        try {
            $userShift = UserShift::findOrFail($id);
            $userShift->update(['is_active' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Vardiya ataması başarıyla kaldırıldı.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vardiya ataması kaldırılırken hata oluştu.'
            ], 500);
        }
    }

    /**
     * Kullanıcının yetkisine göre şubeleri filtrele
     */
    private function getFilteredBranches($user)
    {
        if (in_array($user->role_id, [1, 2])) { // Süper admin ve admin
            return Branch::where('is_active', 1)->get();
        } elseif (in_array($user->role_id, [3, 4])) { // Şirket sahibi ve yetkilisi
            return Branch::where('company_id', $user->company_id)
                        ->where('is_active', 1)->get();
        } else { // Diğer roller
            return Branch::where('id', $user->branch_id)
                        ->where('is_active', 1)->get();
        }
    }

    /**
     * Kullanıcının yetkisine göre departmanları filtrele
     */
    private function getFilteredDepartments($user)
    {
        if (in_array($user->role_id, [1, 2])) { // Süper admin ve admin
            return Department::where('is_active', 1)->get();
        } elseif (in_array($user->role_id, [3, 4])) { // Şirket sahibi ve yetkilisi
            return Department::where('company_id', $user->company_id)
                            ->where('is_active', 1)->get();
        } else { // Diğer roller
            return Department::where('branch_id', $user->branch_id)
                            ->where('is_active', 1)->get();
        }
    }
}
