<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\HourlyLeave\HourlyLeaveListRequest;
use App\Http\Requests\Api\HourlyLeave\HourlyLeaveStoreRequest;
use App\Http\Resources\HourlyLeave\HourlyLeaveResource;
use App\Http\Resources\HourlyLeave\HourlyLeaveCollection;
use App\Http\Resources\Holiday\HolidayTypeResource;
use App\Http\Resources\Holiday\HolidayTypeCollection;
use App\Http\Responses\ApiResponse;
use App\Models\HourlyLeave;
use App\Models\HolidayType;
use App\Services\HourlyLeaveService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HourlyLeaveController extends BaseController
{
    /**
     * HourlyLeaveService instance.
     *
     * @var HourlyLeaveService
     */
    protected HourlyLeaveService $hourlyLeaveService;

    /**
     * HourlyLeaveController constructor.
     *
     * @param HourlyLeaveService $hourlyLeaveService
     */
    public function __construct(HourlyLeaveService $hourlyLeaveService)
    {
        $this->model = new HourlyLeave();
        $this->modelName = 'Saatlik İzin';
        $this->relationships = ['user', 'branch', 'company', 'holidayType'];
        $this->searchableFields = ['date', 'start_time', 'end_time', 'reason'];
        $this->sortableFields = ['id', 'date', 'start_time', 'end_time', 'created_at', 'updated_at'];
        $this->hourlyLeaveService = $hourlyLeaveService;
    }

    /**
     * Veri hazırlama işlemi.
     *
     * @param Request $request
     * @return array
     */
    protected function prepareStoreData(\Illuminate\Foundation\Http\FormRequest|Request $request): array
    {
        $user = Auth::user();
        $data = parent::prepareStoreData($request);

        // Kullanıcı bilgilerini ekle
        $data['user_id'] = $user->id;
        $data['company_id'] = $user->company_id;
        $data['branch_id'] = $user->branch_id;

        // transaction_date gelmezse şu anki zamanı kullan
        if (!isset($data['transaction_date'])) {
            $data['transaction_date'] = Carbon::now()->format('Y-m-d H:i:s');
        }

        return $data;
    }

    /**
     * Kullanıcıya ait saatlik izinleri listeler.
     *
     * @route GET /api/hourly-leaves/list
     * @uses HourlyLeaveCollection
     * @param HourlyLeaveListRequest $request
     * @return JsonResponse
     */
    public function list(HourlyLeaveListRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $filters = $request->validated();

            // HourlyLeaveService üzerinden saatlik izinleri getir
            $hourlyLeaves = $this->hourlyLeaveService->getUserHourlyLeaves(
                $user->id,
                $filters,
                $this->relationships
            );

            if ($hourlyLeaves->count() > 0) {
                return ApiResponse::success(
                    new HourlyLeaveCollection($hourlyLeaves),
                    "Saatlik izin talepleriniz başarıyla listelendi."
                );
            } else {
                return ApiResponse::success(
                    ['data' => []],
                    "Saatlik izin talebiniz bulunmamaktadır."
                );
            }
        } catch (\Exception $e) {
            return ApiResponse::serverError("Saatlik izinler listelenirken bir hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * İzin tiplerini listeler.
     *
     * @route GET /api/hourly-leaves/types
     * @uses HolidayTypeResource
     * @return JsonResponse
     */
    public function types(): JsonResponse
    {
        try {
            $holidayTypes = HolidayType::all();

            return ApiResponse::success(
                new HolidayTypeCollection($holidayTypes),
                "İzin tipleri başarıyla listelendi",
                SymfonyResponse::HTTP_OK
            );
        } catch (\Exception $e) {
            return ApiResponse::serverError("İzin tipleri listelenirken bir hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * Kayıt işlemi sonrası ilave işlemler.
     *
     * @param Model $item
     * @return Model
     */
    protected function saveHook(Model $item): Model
    {
        // Saatlik izin kaydı sonrası yapılacak ek işlemler
        // Örneğin, bildirim gönderme, log kaydetme vb.
        return $item;
    }

        /**
     * Saatlik izin talebi oluşturur.
     *
     * @route POST /api/hourly-leaves/store
     * @param \Illuminate\Foundation\Http\FormRequest|Request $request
     * @return JsonResponse
     */
    public function store(\Illuminate\Foundation\Http\FormRequest|Request $request): JsonResponse
    {
        try {
            // Veri hazırlama
            $data = $this->prepareStoreData($request);

            // Saatlik izin talebi oluşturma
            $hourlyLeave = $this->hourlyLeaveService->createHourlyLeave($data);

            return ApiResponse::success(
                new HourlyLeaveResource($hourlyLeave->load($this->relationships)),
                "Saatlik izin talebi başarıyla oluşturuldu"
            );
        } catch (\Exception $e) {
            return ApiResponse::serverError("Saatlik izin talebi oluşturulurken bir hata oluştu: " . $e->getMessage());
        }
    }
}
