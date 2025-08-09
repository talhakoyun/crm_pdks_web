<?php

namespace App\Http\Controllers\Backend;

use App\Models\UserShiftCustom;
use App\Models\ShiftDefinition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserShiftCustomController extends BaseController
{
    public function __construct()
    {
        $this->title = 'Özel Vardiya Atama';
        $this->page = 'user_shift_custom';
        $this->upload = 'user_shift_custom';
        $this->model = new UserShiftCustom();
        $this->view = (object)array(
            'breadcrumb' => array(
                'Özel Vardiya Atama' => route('backend.user_shift_custom_list'),
            ),
        );

        view()->share('shiftDefinitions', ShiftDefinition::active()->get());

        // Kullanıcının yetkisine göre görüntülenecek kullanıcıları filtreleyelim
        $this->middleware(function ($request, $next) {
            $isAdmin = $request->attributes->get('is_admin', false);
            $isSuperAdmin = $request->attributes->get('is_super_admin', false);
            $isCompanyOwner = $request->attributes->get('is_company_owner', false);
            $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
            $isBranchAdmin = $request->attributes->get('is_branch_admin', false);
            $isDepartmentAdmin = $request->attributes->get('is_department_admin', false);

            $companyId = $request->attributes->get('company_id');
            $branchId = $request->attributes->get('branch_id');
            $departmentId = $request->attributes->get('department_id');

            $usersQuery = User::where('role_id', 7)->active()->with('userShifts.shiftDefinition');

            // Şirket, şube veya departmana göre filtreleme
            if (!$isSuperAdmin && !$isAdmin) {
                $usersQuery->where('company_id', $companyId);

                if (!$isCompanyOwner && !$isCompanyAdmin && $branchId) {
                    $usersQuery->where('branch_id', $branchId);

                    if (!$isBranchAdmin && $departmentId) {
                        $usersQuery->where('department_id', $departmentId);
                    }
                }
            }

            view()->share('users', $usersQuery->get());
            return $next($request);
        });

        parent::__construct();
    }

    public function add(Request $request)
    {
        return view("backend.$this->page.add");
    }

    public function list(Request $request)
    {
        if ($request->has('datatable')) {
            // Kullanıcının yetkisine göre filtreleme yapalım
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

            $select = $this->model::with(['user', 'shiftDefinition']);

            // Şirket, şube veya departmana göre filtreleme
            if (!$isSuperAdmin && !$isAdmin) {
                $select->whereHas('user', function($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                });

                if (!$isCompanyOwner && !$isCompanyAdmin && $branchId) {
                    $select->whereHas('user', function($query) use ($branchId) {
                        $query->where('branch_id', $branchId);
                    });

                    if (!$isBranchAdmin && $departmentId) {
                        $select->whereHas('user', function($query) use ($departmentId) {
                            $query->where('department_id', $departmentId);
                        });
                    }
                }

                // Departman yetkilisi ve user_id alanı yoksa, created_by üzerinden erişim kontrolü
                if ($isDepartmentAdmin) {
                    // Departman yetkilisi sadece kendi kaydettiklerini veya departmanındaki kullanıcıların
                    // oluşturduğu kayıtları görebilir
                    $departmentUserIds = User::where('department_id', $departmentId)->pluck('id')->toArray();

                    $select->where(function($query) use ($loggedInUserId, $departmentUserIds) {
                        $query->whereIn('created_by', $departmentUserIds)
                              ->orWhere('created_by', $loggedInUserId);
                    });
                }
            }

            $obj = datatables()->of($select)
                ->addColumn('user_name', function ($item) {
                    return '<div class="d-flex align-items-center">
                                <div>
                                    <div class="fw-bold">' . $item->user->name . ' ' . $item->user->surname . '</div>
                                    <small class="text-muted">' . ($item->user->department->title ?? '') . '</small>
                                </div>
                            </div>';
                })
                ->addColumn('shift_name', function ($item) {
                    return $item->shiftDefinition->title . ' (' . $item->shiftDefinition->start_time . ' - ' . $item->shiftDefinition->end_time . ')';
                })
                ->editColumn('start_date', function ($item) {
                    return Carbon::parse($item->start_date)->format('d.m.Y');
                })
                ->editColumn('end_date', function ($item) {
                    return Carbon::parse($item->end_date)->format('d.m.Y');
                })
                ->editColumn('is_active', function ($item) {
                    return $item->is_active == 1 ? 'Aktif' : 'Pasif';
                })
                ->addColumn('actions', function ($item) {
                    return '
                    <td class="text-center">
                        <div class="d-flex align-items-center gap-10 justify-content-center">
                            <button type="button" class="remove-item-btn bg-danger-focus bg-hover-danger-200 text-danger-600 fw-medium w-40-px h-40-px d-flex justify-content-center align-items-center rounded-circle" row-delete="' . $item->id . '">
                                <iconify-icon icon="fluent:delete-24-regular" class="menu-icon"></iconify-icon>
                            </button>
                        </div>
                    </td>';
                })
                ->rawColumns(['user_name', 'actions'])
                ->make(true);

            return $obj;
        }

        return view("backend.$this->page.list");
    }

    public function form(Request $request, $unique = NULL)
    {
        if (!is_null($unique)) {
            $item = $this->model::with(['user', 'shiftDefinition'])->find((int)$unique);

            if (is_null($item)) {
                return redirect()->back()->with('error', 'Kayıt bulunamadı');
            }
        } else {
            $item = new $this->model;
        }

        return view("backend.$this->page.form", compact('item'));
    }

    public function saveBulk(Request $request)
    {
        $startDates = $request->input('start_date', []);
        $endDates = $request->input('end_date', []);
        $shiftDefinitionIds = $request->input('shift_definition_id', []);

        $savedCount = 0;
        $savedUsers = [];
        $errors = [];

        // Yetki kontrolleri
        $isAdmin = $request->attributes->get('is_admin', false);
        $isSuperAdmin = $request->attributes->get('is_super_admin', false);
        $isCompanyOwner = $request->attributes->get('is_company_owner', false);
        $isCompanyAdmin = $request->attributes->get('is_company_admin', false);
        $isBranchAdmin = $request->attributes->get('is_branch_admin', false);
        $isDepartmentAdmin = $request->attributes->get('is_department_admin', false);

        $companyId = $request->attributes->get('company_id');
        $branchId = $request->attributes->get('branch_id');
        $departmentId = $request->attributes->get('department_id');

        // Tüm kullanıcılar için kontrol et
        foreach ($startDates as $userId => $startDate) {
            // Yetki kontrolü - kullanıcının kendisi veya altındaki rol seviyesine erişebilir
            if (!$isSuperAdmin && !$isAdmin) {
                $user = User::find($userId);

                // Kullanıcı bulunamadıysa veya yetki dışında ise atla
                if (!$user || $user->company_id != $companyId) {
                    $errors[$userId] = ['permission' => 'Bu kullanıcı için işlem yapma yetkiniz bulunmamaktadır.'];
                    continue;
                }

                if (!$isCompanyOwner && !$isCompanyAdmin && $branchId && $user->branch_id != $branchId) {
                    $errors[$userId] = ['permission' => 'Bu kullanıcı için işlem yapma yetkiniz bulunmamaktadır.'];
                    continue;
                }

                if (!$isBranchAdmin && $departmentId && $user->department_id != $departmentId) {
                    $errors[$userId] = ['permission' => 'Bu kullanıcı için işlem yapma yetkiniz bulunmamaktadır.'];
                    continue;
                }
            }

            // Herhangi bir veri girilmişse kontrol et
            if (!empty($startDate) || !empty($endDates[$userId]) || !empty($shiftDefinitionIds[$userId])) {
                $userErrors = [];

                // Başlangıç tarihi kontrolü
                if (empty($startDate)) {
                    $userErrors['start_date'] = 'Başlangıç tarihi gereklidir.';
                }

                // Bitiş tarihi kontrolü
                if (empty($endDates[$userId])) {
                    $userErrors['end_date'] = 'Bitiş tarihi gereklidir.';
                }

                // Vardiya kontrolü
                if (empty($shiftDefinitionIds[$userId])) {
                    $userErrors['shift_definition_id'] = 'Vardiya seçimi gereklidir.';
                }

                // Tarih sıralaması kontrolü
                if (!empty($startDate) && !empty($endDates[$userId])) {
                    $startDateObj = \Carbon\Carbon::parse($startDate);
                    $endDateObj = \Carbon\Carbon::parse($endDates[$userId]);

                    if ($startDateObj->gt($endDateObj)) {
                        $userErrors['date_order'] = 'Bitiş tarihi başlangıç tarihinden önce olamaz.';
                    }
                }

                // Hata yoksa kaydet
                if (empty($userErrors)) {
                    try {
                        // Tarih aralıklarının çakışmasını kontrol et
                        // Çakışma durumları:
                        // 1. Yeni başlangıç tarihi, mevcut bir aralığın içinde
                        // 2. Yeni bitiş tarihi, mevcut bir aralığın içinde
                        // 3. Yeni aralık, mevcut bir aralığı tamamen kapsıyor
                        $overlappingRecord = $this->model::where('user_id', $userId)
                            ->where(function($query) use ($startDate, $endDates, $userId) {
                                // Yeni başlangıç tarihi, mevcut aralığın içinde
                                $query->where(function($q) use ($startDate) {
                                    $q->where('start_date', '<=', $startDate)
                                      ->where('end_date', '>=', $startDate);
                                })
                                // VEYA yeni bitiş tarihi, mevcut aralığın içinde
                                ->orWhere(function($q) use ($endDates, $userId) {
                                    $q->where('start_date', '<=', $endDates[$userId])
                                      ->where('end_date', '>=', $endDates[$userId]);
                                })
                                // VEYA yeni aralık, mevcut aralığı tamamen kapsıyor
                                ->orWhere(function($q) use ($startDate, $endDates, $userId) {
                                    $q->where('start_date', '>=', $startDate)
                                      ->where('end_date', '<=', $endDates[$userId]);
                                });
                            })
                            ->first();

                        if ($overlappingRecord) {
                            $errors[$userId] = ['duplicate' => 'Bu tarih aralığı (' . $startDate . ' - ' . $endDates[$userId] . ') mevcut bir kayıtla çakışıyor: ' .
                                $overlappingRecord->start_date . ' - ' . $overlappingRecord->end_date];
                            continue;
                        }

                        $this->model::create([
                            'user_id' => $userId,
                            'shift_definition_id' => $shiftDefinitionIds[$userId],
                            'start_date' => $startDate,
                            'end_date' => $endDates[$userId],
                            'is_active' => 1
                        ]);

                        $savedCount++;
                        $savedUsers[] = $userId;
                    } catch (\Exception $e) {
                        $errors[$userId] = ['system' => 'Veritabanı hatası: ' . $e->getMessage()];
                    }
                } else {
                    $errors[$userId] = $userErrors;
                }
            }
        }

        // AJAX yanıtı dön
        if ($request->ajax()) {
            if ($savedCount > 0) {
                return response()->json([
                    'status' => true,
                    'message' => $savedCount . ' adet özel vardiya ataması başarıyla kaydedildi.',
                    'saved_users' => $savedUsers,
                    'errors' => $errors
                ]);
            } else {
                // Hata mesajlarını birleştir
                $errorMessage = 'Hiçbir kayıt oluşturulmadı. Lütfen en az bir personel için tarih aralığı ve vardiya seçiniz.';

                if (count($errors) > 0) {
                    $allErrors = [];
                    foreach ($errors as $userId => $userErrors) {
                        foreach ($userErrors as $field => $error) {
                            $allErrors[] = $error;
                        }
                    }
                    $errorMessage = implode(', ', $allErrors);
                }

                return response()->json([
                    'status' => false,
                    'message' => $errorMessage,
                    'errors' => $errors
                ]);
            }
        }

        // Normal yanıt (AJAX olmayan istek için)
        if ($savedCount > 0) {
            return redirect()->route('backend.' . $this->page . '_list')
                ->with('success', $savedCount . ' adet özel vardiya ataması başarıyla kaydedildi.');
        } else {
            return redirect()->back()->with('error', 'Hiçbir kayıt oluşturulmadı. Lütfen en az bir personel için tarih aralığı ve vardiya seçiniz.');
        }
    }
}
