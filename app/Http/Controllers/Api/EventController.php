<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Event\EventCollection;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use App\Http\Resources\Event\EventResource;
use App\Services\EventService;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Http\Responses\ApiResponse;

class EventController extends BaseController
{
    /**
     * EventService instance.
     *
     * @var EventService
     */
    protected EventService $eventService;

    /**
     * EventController constructor.
     *
     * @param EventService $eventService
     */
    public function __construct(EventService $eventService)
    {
        $this->model = new Event();
        $this->modelName = 'Etkinlik';
        $this->relationships = ['creator', 'participants'];
        $this->searchableFields = ['title', 'description'];
        $this->sortableFields = ['id', 'created_at', 'updated_at', 'start_date', 'end_date'];
        $this->eventService = $eventService;
    }


    public function participate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id' => 'required|exists:events,id'
            ]);

            $event = Event::findOrFail($request->id);
            $result = $this->eventService->participate($event);

            if (!$result['success']) {
                return ApiResponse::error(
                    $result['message'],
                    SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return ApiResponse::success(
                null,
                $result['message'],
                SymfonyResponse::HTTP_OK
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Etkinliğe katılım isteği gönderilirken bir hata oluştu: ' . $e->getMessage(),
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Kullanıcının katıldığı etkinlikleri listeler.
     *
     * @route GET /api/events/my-participations
     * @return JsonResponse
     */
    public function myParticipations(): JsonResponse
    {
        try {
            $now = Carbon::now();
            $userId = Auth::id();

            // Onaylanan ve süresi geçmemiş etkinlikler
            $upcomingApproved = Event::whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'approved');
            })
            ->where('end_date', '>', $now)
            ->with(['participants' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->get();

            // Onaylanan ve süresi geçmiş etkinlikler
            $pastApproved = Event::whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'approved');
            })
            ->where('end_date', '<=', $now)
            ->with(['participants' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->get();

            // Reddedilen ve süresi geçmemiş etkinlikler
            $upcomingRejected = Event::whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'rejected');
            })
            ->where('end_date', '>', $now)
            ->with(['participants' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->get();

            // Reddedilen ve süresi geçmiş etkinlikler
            $pastRejected = Event::whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'rejected');
            })
            ->where('end_date', '<=', $now)
            ->with(['participants' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->get();

            // Bekleyen ve süresi geçmemiş istekler
            $upcomingPending = Event::whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'pending');
            })
            ->where('end_date', '>', $now)
            ->with(['participants' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->get();

            // Bekleyen ve süresi geçmiş istekler
            $pastPending = Event::whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->where('status', 'pending');
            })
            ->where('end_date', '<=', $now)
            ->with(['participants' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->get();

            return ApiResponse::success([
                'upcoming_approved' => EventResource::collection($upcomingApproved),
                'past_approved' => EventResource::collection($pastApproved),
                'upcoming_rejected' => EventResource::collection($upcomingRejected),
                'past_rejected' => EventResource::collection($pastRejected),
                'upcoming_pending' => EventResource::collection($upcomingPending),
                'past_pending' => EventResource::collection($pastPending)
            ], 'Katılım sağladığınız etkinlikler başarıyla listelendi', SymfonyResponse::HTTP_OK);

        } catch (\Exception $e) {
            return ApiResponse::error(
                'Katılım sağladığınız etkinlikler listelenirken bir hata oluştu: ' . $e->getMessage(),
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR
            );
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
        // Etkinlik kaydı sonrası yapılacak ek işlemler
        // Örneğin, bildirim gönderme, log kaydetme vb.

        return $item;
    }
}
