<?php

namespace App\Http\Controllers\Backend;

use App\Models\ShiftDefinition;
use App\Http\Requests\Backend\ShiftDefinitionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ShiftDefinitionController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        parent::__construct();
        $this->title = 'Vardiya Tanımları';
        $this->page = 'shift_definition';
        $this->upload = 'shift_definition';
        $this->model = new ShiftDefinition();
        $this->request = new ShiftDefinitionRequest();
        $this->relation = ['company', 'branch'];
        $this->view = (object)array(
            'breadcrumb' => array(
                'Vardiya Tanımları' => route('backend.shift_definition_list'),
            ),
        );
        parent::__construct();
    }

    /**
     * Vardiya tanımlarını rol bazlı filtrelemek için özelleştirelim
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        // Vardiyaları rol bazlı filtreleme için özel sorgu oluşturalım
        $isAdmin = $user->role_id == 2;
        $isSuperAdmin = $user->role_id == 1;
        $isCompanyOwner = $user->role_id == 3;
        $isCompanyAdmin = $user->role_id == 4;
        $isBranchAdmin = $user->role_id == 5;
        $isDepartmentAdmin = $user->role_id == 6;

        $companyId = $user->company_id;
        $branchId = $user->branch_id;
        $departmentId = $user->department_id;
        $loggedInUserId = Auth::id();

        $this->listQuery = $this->model::query();

        // Süper Admin ve Admin haricinde şirket filtrelemesi uygula
        if (!$isSuperAdmin && !$isAdmin) {
            $this->listQuery->where('company_id', $companyId);

            // Şirket sahibi ve yetkilisi tüm şubelere erişebilir
            if (!$isCompanyOwner && !$isCompanyAdmin && $branchId) {
                $this->listQuery->where('branch_id', $branchId);
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

        return parent::list($request);
    }

    /**
     * Datatable için sütunları özelleştirelim
     */
    public function datatableHook($obj)
    {
        return $obj->editColumn('branch_id', function ($item) {
            return $item->branch?->title ?? '-';
        })->editColumn('company_id', function ($item) {
            return $item->company?->name ?? '-';
        })->editColumn('start_date', function ($item) {
            if (!$item->start_date) {
                return '<span class="badge bg-secondary">Tanımsız</span>';
            }
            return '<span class="badge bg-primary">' . date('d.m.Y', strtotime($item->start_date)) . '</span>';
        })->editColumn('end_date', function ($item) {
            if (!$item->end_date) {
                return '<span class="badge bg-success">Süresiz</span>';
            }
            return '<span class="badge bg-warning">' . date('d.m.Y', strtotime($item->end_date)) . '</span>';
        })->addColumn('working_days', function ($item) {
            $workingDays = $item->getWorkingDays();
            if (empty($workingDays)) {
                return '<span class="badge bg-warning">Tanımsız</span>';
            }

            $dayNames = [
                'monday' => 'Pzt',
                'tuesday' => 'Sal',
                'wednesday' => 'Çar',
                'thursday' => 'Per',
                'friday' => 'Cum',
                'saturday' => 'Cmt',
                'sunday' => 'Paz'
            ];

            $displayDays = array_map(function($day) use ($dayNames) {
                return $dayNames[$day] ?? $day;
            }, $workingDays);

            return '<span class="badge bg-success">' . implode(', ', $displayDays) . '</span>';
        })->addColumn('weekly_hours', function ($item) {
            $hours = $item->getWeeklyWorkingHours();
            if ($hours == 0) {
                return '<span class="badge bg-warning">0 saat</span>';
            }
            return '<span class="badge bg-info">' . number_format($hours, 1) . ' saat</span>';
        })->rawColumns(['start_date', 'end_date', 'working_days', 'weekly_hours']);
    }
}
