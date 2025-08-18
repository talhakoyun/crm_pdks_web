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
        // Vardiyaları rol bazlı filtreleme için özel sorgu oluşturalım
        $roleData = $this->getRoleDataFromRequest($request);
        extract($roleData);
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
        });
    }
}
