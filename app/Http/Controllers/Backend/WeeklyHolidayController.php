<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\WeeklyHolidayRequest;
use App\Models\UserWeeklyHoliday;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WeeklyHolidayController extends BaseController
{
    protected $title = "Haftalık Tatil Günleri";
    protected $page = "weekly_holiday";
    protected $model;
    protected $request;

    public function __construct()
    {
        $this->model = new UserWeeklyHoliday();
        $this->request = new WeeklyHolidayRequest();
        parent::__construct();
    }

    /**
     * Form sayfası
     */
    public function form(Request $request, $unique = null)
    {
        $user = Auth::user();

        // Şubeler listesi
        $branches = $this->getBranchesForUser($user);

        // Departmanlar listesi
        $departments = $this->getDepartmentsForUser($user);

        $editHoliday = null;
        $selectedUsers = [];

        if ($unique) {
            $editHoliday = UserWeeklyHoliday::with('user')->find($unique);
            if ($editHoliday) {
                $selectedUsers = [$editHoliday->user];
            }
        }

        return view("backend.{$this->page}.form", compact(
            'branches',
            'departments',
            'editHoliday',
            'selectedUsers'
        ));
    }

    /**
     * Kullanıcıları getir (AJAX)
     */
    public function getUsers(Request $request)
    {
        try {
            $user = Auth::user();
            $query = User::query()->whereNotIn('role_id', [1, 2,3])->with(['branch', 'department']);

            // Rol bazlı filtreleme
            $this->applyRoleBasedFiltering($query, $user);

            // Filtreler
            if ($request->branch_id) {
                $query->where('branch_id', $request->branch_id);
            }

            if ($request->department_id) {
                $query->where('department_id', $request->department_id);
            }

            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $query->active()->get()->map(function($user) {
                // Mevcut tatil günlerini getir
                $weeklyHoliday = UserWeeklyHoliday::where('user_id', $user->id)
                    ->where('is_active', 1)
                    ->first();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'email' => $user->email,
                    'branch' => $user->branch->title ?? 'Tanımsız',
                    'department' => $user->department->title ?? 'Tanımsız',
                    'current_holidays' => $weeklyHoliday ? $weeklyHoliday->holiday_days_string : 'Tanımlı değil'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kullanıcılar yüklenirken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Tatil günlerini ata
     */
    public function assignHolidays(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'holiday_days' => 'required|array|min:1',
            'holiday_days.*' => 'integer|between:1,7'
        ]);

        try {
            DB::beginTransaction();

            $assignedCount = 0;
            $updatedCount = 0;

            foreach ($request->user_ids as $userId) {
                // Mevcut tatil günü kaydını kontrol et
                $existingHoliday = UserWeeklyHoliday::where('user_id', $userId)
                    ->where('is_active', 1)
                    ->first();

                if ($existingHoliday) {
                    // Güncelle
                    $existingHoliday->update([
                        'holiday_days' => $request->holiday_days,
                        'updated_by' => Auth::id()
                    ]);
                    $updatedCount++;
                } else {
                    // Yeni kayıt oluştur
                    UserWeeklyHoliday::create([
                        'user_id' => $userId,
                        'holiday_days' => $request->holiday_days,
                        'is_active' => 1,
                        'created_by' => Auth::id()
                    ]);
                    $assignedCount++;
                }
            }

            DB::commit();

            $message = "Başarılı! ";
            if ($assignedCount > 0) {
                $message .= "{$assignedCount} kullanıcıya tatil günü atandı. ";
            }
            if ($updatedCount > 0) {
                $message .= "{$updatedCount} kullanıcının tatil günü güncellendi.";
            }

            return response()->json([
                'success' => true,
                'message' => trim($message)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Tatil günleri atanırken hata oluştu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Kullanıcı için şubeleri getir
     */
    private function getBranchesForUser($user)
    {
        if ($user->role_id <= 2) { // Super Admin, Admin
            return Branch::all();
        } elseif (in_array($user->role_id, [3, 4])) { // Company Owner, Company Admin
            return Branch::where('company_id', $user->company_id)->get();
        } elseif ($user->role_id == 5) { // Branch Admin
            return Branch::where('id', $user->branch_id)->get();
        }

        return collect();
    }

    /**
     * Kullanıcı için departmanları getir
     */
    private function getDepartmentsForUser($user)
    {
        if ($user->role_id <= 2) { // Super Admin, Admin
            return Department::all();
        } elseif (in_array($user->role_id, [3, 4])) { // Company Owner, Company Admin
            return Department::where('company_id', $user->company_id)->get();
        } elseif ($user->role_id == 5) { // Branch Admin
            return Department::where('branch_id', $user->branch_id)->get();
        } elseif ($user->role_id == 6) { // Department Admin
            return Department::where('id', $user->department_id)->get();
        }

        return collect();
    }

    /**
     * Rol bazlı filtreleme uygula
     */
    private function applyRoleBasedFiltering($query, $user)
    {
        if ($user->role_id <= 2) {
            // Super Admin, Admin - tüm kullanıcıları görebilir
            return;
        } elseif (in_array($user->role_id, [3, 4])) {
            // Company Owner, Company Admin - kendi şirketinin kullanıcıları
            $query->where('company_id', $user->company_id);
        } elseif ($user->role_id == 5) {
            // Branch Admin - kendi şubesinin kullanıcıları
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->role_id == 6) {
            // Department Admin - kendi departmanının kullanıcıları
            $query->where('department_id', $user->department_id);
        } else {
            // Personel - sadece kendini görebilir
            $query->where('id', $user->id);
        }
    }

    /**
     * Datatable hook
     */
    public function datatableHook($obj)
    {
        return $obj->editColumn('holiday_days', function ($item) {
                // Sadece metin döndür, HTML değil
                return $item->holiday_days_string;
            })
            ->editColumn('user_id', function ($item) {
                return $item->user ? ($item->user->name . ' ' . ($item->user->surname ?? '')) : 'Tanımsız';
            })
            ->addColumn('user_email', function ($item) {
                return $item->user ? $item->user->email : 'Tanımsız';
            })
            ->addColumn('branch_name', function ($item) {
                return $item->user && $item->user->branch ? $item->user->branch->title : 'Tanımsız';
            })
            ->addColumn('department_name', function ($item) {
                return $item->user && $item->user->department ? $item->user->department->title : 'Tanımsız';
            })
            ->rawColumns(['is_active']); // holiday_days'i rawColumns'dan çıkardık
    }

    /**
     * Liste query'sini özelleştir
     */
    protected $relation = ['user', 'user.branch', 'user.department'];
}
