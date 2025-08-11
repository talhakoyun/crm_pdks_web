<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\Holiday\HolidayListRequest;
use App\Http\Requests\Api\Holiday\HolidayRequestRequest;
use App\Http\Requests\Api\Holiday\HolidayStoreRequest;
use App\Http\Resources\Holiday\HolidayResource;
use App\Http\Resources\Holiday\HolidayCollection;
use App\Http\Resources\Holiday\HolidayTypeResource;
use App\Http\Resources\Holiday\HolidayTypeCollection;
use App\Http\Responses\ApiResponse;
use App\Models\Holiday;
use App\Models\HolidayType;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class HolidayController extends BaseController
{
    /**
     * HolidayService instance.
     *
     * @var HolidayService
     */
    protected HolidayService $holidayService;

    /**
     * HolidayController constructor.
     *
     * @param HolidayService $holidayService
     */
    public function __construct(HolidayService $holidayService)
    {
        $this->model = new Holiday();
        $this->modelName = 'İzin';
        $this->relationships = ['user', 'branch', 'company'];
        $this->searchableFields = ['start_date', 'end_date', 'note'];
        $this->sortableFields = ['id', 'start_date', 'end_date', 'created_at', 'updated_at'];
        $this->holidayService = $holidayService;
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
     * Kullanıcıya ait izinleri listeler.
     *
     * @route GET /api/holidays/list
     * @uses HolidayCollection
     * @param HolidayListRequest $request
     * @return JsonResponse
     */
    public function list(HolidayListRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $filters = $request->validated();

            // HolidayService üzerinden izinleri getir
            $holidays = $this->holidayService->getUserHolidays(
                $user->id,
                $filters,
                $this->relationships
            );
            $holidayResource = new HolidayCollection($holidays);
            if ($holidays->count() > 0) {
                // Koleksiyonu çöz ve sadece içteki 'data' dizisini döndür
                $resolved = $holidayResource->resolve(request());
                $dataOnly = $resolved['data'] ?? $resolved;
                return ApiResponse::success(
                    $dataOnly,
                    "İzin talepleriniz başarıyla listelendi."
                );
            } else {
                return ApiResponse::success(
                    [],
                    "İzin talebiniz bulunmamaktadır."
                );
            }
        } catch (\Exception $e) {
            return ApiResponse::serverError("İzinler listelenirken bir hata oluştu: " . $e->getMessage());
        }
    }

    /**
     * İzin tiplerini listeler.
     *
     * @route GET /api/holidays/types
     * @uses HolidayTypeResource
     * @return JsonResponse
     */
    public function types(): JsonResponse
    {
        try {
            $holidayTypes = HolidayType::all();

            $typesResource = new HolidayTypeCollection($holidayTypes);
            $resolved = $typesResource->resolve(request());
            $dataOnly = $resolved['data'] ?? $resolved;
            return ApiResponse::success(
                $dataOnly,
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
        // İzin kaydı sonrası yapılacak ek işlemler
        // Örneğin, bildirim gönderme, log kaydetme vb.
        return $item;
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Veri hazırlama
            $data = $this->prepareStoreData($request);

            // İzin talebi oluşturma
            $holiday = $this->holidayService->createHoliday($data);

            return ApiResponse::success([$holiday], "İzin talebi başarıyla oluşturuldu");
        } catch (\Exception $e) {
            return ApiResponse::serverError("İzin talebi oluşturulurken bir hata oluştu: " . $e->getMessage());
        }
    }
}
