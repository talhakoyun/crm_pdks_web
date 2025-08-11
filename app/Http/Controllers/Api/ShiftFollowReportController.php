<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\ShiftFollowReportServiceInterface;
use App\Http\Requests\Api\ShiftFollow\ShiftFollowDailyRequest;
use App\Http\Requests\Api\ShiftFollow\ShiftFollowWeeklyRequest;
use App\Http\Resources\Shift\ShiftFollowDailyReportResource;
use App\Http\Resources\Shift\ShiftFollowWeeklyReportResource;
use App\Http\Responses\ApiResponse;
use App\Models\ShiftFollow;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ShiftFollowReportController extends BaseController
{
    /**
     * ShiftFollowReportService instance.
     *
     * @var ShiftFollowReportServiceInterface
     */
    protected ShiftFollowReportServiceInterface $reportService;

    /**
     * ShiftFollowReportController constructor.
     *
     * @param ShiftFollowReportServiceInterface $reportService
     */
    public function __construct(ShiftFollowReportServiceInterface $reportService)
    {
        $this->model = new ShiftFollow();
        $this->modelName = 'Vardiya Takip Raporu';
        $this->relationships = ['user', 'branch', 'zone', 'shift', 'followType'];
        $this->searchableFields = ['transaction_date', 'note'];
        $this->sortableFields = ['id', 'transaction_date', 'created_at', 'updated_at'];
        $this->reportService = $reportService;
    }

    /**
     * Kullanıcının günlük vardiya takip bilgilerini döndürür.
     *
     * @param ShiftFollowDailyRequest $request
     * @return JsonResponse
     */
    public function daily(ShiftFollowDailyRequest $request): JsonResponse
    {
        try {
            // Kullanıcı bilgisini al
            $userId = $request->input('user_id');

            // Eğer user_id belirtilmemişse giriş yapan kullanıcının id'sini kullan
            if (!$userId && Auth::user()) {
                $userId = Auth::user()->id;
            }

            if (!$userId) {
                return ApiResponse::error(
                    'Kullanıcı bilgisi gereklidir',
                    SymfonyResponse::HTTP_BAD_REQUEST
                );
            }

            // Tarih belirtilmemişse bugünü kullan
            $dateStr = $request->input('date', Carbon::now()->format('Y-m-d'));
            $date = Carbon::parse($dateStr)->startOfDay();

            // Servis üzerinden günlük raporu al
            $report = $this->reportService->getDailyReport($userId, $date);
            $reportResource = new ShiftFollowDailyReportResource($report);
            return ApiResponse::success(
                [$reportResource],
                'Günlük vardiya takip bilgileri başarıyla alındı'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Vardiya takip bilgileri alınırken bir hata oluştu: ' . $e->getMessage(),
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Kullanıcının haftalık vardiya takip özetini döndürür.
     *
     * @param ShiftFollowWeeklyRequest $request
     * @return JsonResponse
     */
    public function weeklyReport(ShiftFollowWeeklyRequest $request): JsonResponse
    {
        try {
            // Kullanıcı bilgisini al
            $userId = $request->input('user_id');

            // Eğer user_id belirtilmemişse giriş yapan kullanıcının id'sini kullan
            if (!$userId && Auth::user()) {
                $userId = Auth::user()->id;
            }

            if (!$userId) {
                return ApiResponse::error(
                    'Kullanıcı bilgisi gereklidir',
                    SymfonyResponse::HTTP_BAD_REQUEST
                );
            }

            // Tarih aralığını al
            $startDate = $request->input('start_date', now()->startOfWeek()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfWeek()->format('Y-m-d'));

            // Servis üzerinden haftalık raporu al
            $report = $this->reportService->getWeeklyReport($userId, $startDate, $endDate);
            $reportResource = new ShiftFollowWeeklyReportResource($report);
            return ApiResponse::success(
                [$reportResource],
                'Haftalık vardiya raporu başarıyla oluşturuldu'
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Haftalık rapor oluşturulurken bir hata oluştu: ' . $e->getMessage(),
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
