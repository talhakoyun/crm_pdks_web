<?php

namespace App\Http\Controllers\Backend;

use App\Http\Requests\Backend\HolidayRequest;
use App\Models\Holiday;
use App\Models\HolidayType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HolidayController extends BaseController
{
    use BasePattern;

    public function __construct()
    {
        $this->title = 'İzin Talepleri';
        $this->page = 'holiday';
        $this->upload = 'holiday';
        $this->model = new Holiday();
        $this->request = new HolidayRequest();

        $this->view = (object)array(
            'breadcrumb' => array(
                'İzin Talepleri' => route('backend.holiday_list'),
            ),
        );

        view()->share('users', User::where('role_id', 7)->get());
        view()->share('holidayTypes', HolidayType::all());
        parent::__construct();
    }

    public function datatableHook($datatable)
    {
        $datatable->editColumn('user_id', function ($item) {
            $fullName = $item->user?->name . ' ' . $item->user?->surname;
            return '<div class="d-flex align-items-center justify-content-center"><iconify-icon icon="mdi:account" style="font-size: 18px; margin-right: 5px;"></iconify-icon>' . $fullName . '</div>';
        })->editColumn('type', function ($item) {
            return '<div class="d-flex align-items-center justify-content-center"><iconify-icon icon="mdi:card-text" style="font-size: 18px; margin-right: 5px;"></iconify-icon><span class="badge bg-info">' . $item->holidayType?->title . '</span></div>';
        })->editColumn('status', function ($item) {
            $statusBadge = $item->status == 'pending' ? '<span class="badge bg-warning">' . 'Beklemede' . '</span>' : ($item->status == 'approved' ? '<span class="badge bg-success">' . 'Onaylandı' . '</span>' : '<span class="badge bg-danger">' . 'Reddedildi' . '</span>');
            $statusIcon = $item->status == 'pending' ? 'mdi:clock' : ($item->status == 'approved' ? 'mdi:check-circle' : 'mdi:close-circle');
            return '<div class="d-flex align-items-center justify-content-center"><iconify-icon icon="' . $statusIcon . '" style="font-size: 18px; margin-right: 5px;"></iconify-icon>' . $statusBadge . '</div>';
        })->editColumn('start_date', function ($item) {
            return '<div class="d-flex align-items-center justify-content-center"><iconify-icon icon="mdi:calendar-start" style="font-size: 18px; margin-right: 5px;"></iconify-icon>' . Carbon::parse($item->start_date)->format('d.m.Y') . '</div>';
        })->editColumn('end_date', function ($item) {
            return '<div class="d-flex align-items-center justify-content-center"><iconify-icon icon="mdi:calendar-end" style="font-size: 18px; margin-right: 5px;"></iconify-icon>' . Carbon::parse($item->end_date)->format('d.m.Y') . '</div>';
        })->editColumn('status', function ($item) {
            $statusClass = $item->status == 'pending' ? 'warning' : ($item->status == 'approved' ? 'success' : 'danger');
            $statusText = $item->status == 'pending' ? 'Beklemede' : ($item->status == 'approved' ? 'Onaylandı' : 'Reddedildi');
            return '<div class="d-flex align-items-center justify-content-center">
                        <iconify-icon icon="mdi:calendar-check" style="font-size: 18px; margin-right: 5px;"></iconify-icon>
                        <span class="bg-' . $statusClass . '-focus text-' . $statusClass . '-600 border border-' . $statusClass . '-main px-24 py-4 radius-4 fw-medium text-sm">
                            ' . $statusText . '
                        </span>
                    </div>';
        });

        return $datatable;
    }

    /**
     * İzin talebinin durumunu değiştir (onaylama/reddetme)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request)
    {
        // İstek validasyonu
        $validator = validator($request->all(), [
            'id' => 'required|exists:holidays,id',
            'status' => 'required|in:approved,rejected',
            'description' => 'nullable|string|min:3'
        ], [
            'id.required' => 'İzin bilgisi gereklidir',
            'id.exists' => 'İzin kaydı bulunamadı',
            'status.required' => 'Durum bilgisi gereklidir',
            'status.in' => 'Geçersiz durum değeri',
            'description.string' => 'Açıklama metin formatında olmalıdır',
            'description.min' => 'Açıklama en az 3 karakter olmalıdır'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        // İzin kaydını bul
        $holiday = Holiday::find($request->id);

        // İzinin durumunu güncelle
        $holiday->status = $request->status;
        $holiday->status_description = $request->description;
        $holiday->status_changed_by = Auth::user()->id;
        $holiday->status_changed_at = now();
        $holiday->save();

        return response()->json([
            'success' => true,
            'message' => $request->status == 'approved' ? 'İzin talebi onaylandı' : 'İzin talebi reddedildi'
        ]);
    }
}
