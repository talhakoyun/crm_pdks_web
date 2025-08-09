<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Responses\ApiResponse;
use App\Http\Resources\Announcement\AnnouncementResource;
use App\Http\Resources\Announcement\AnnouncementCollection;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AnnouncementController extends BaseController
{
    /**
     * AnnouncementService instance.
     *
     * @var AnnouncementService
     */
    protected AnnouncementService $announcementService;

    /**
     * AnnouncementController constructor.
     *
     * @param AnnouncementService $announcementService
     */
    public function __construct(AnnouncementService $announcementService)
    {
        $this->model = new Announcement();
        $this->modelName = 'Duyuru';
        $this->relationships = ['creator', 'updater'];
        $this->searchableFields = ['title', 'content'];
        $this->sortableFields = ['id', 'created_at', 'updated_at', 'start_date', 'end_date'];
        $this->announcementService = $announcementService;
    }

    /**
     * Kullanıcının görebileceği duyuruları listeler.
     *
     * @route GET /api/announcements
     * @uses AnnouncementResource
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $query = Announcement::query()
                ->where('status', 1)
                ->where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now())
                ->where(function ($query) use ($user) {
                    // Tüm kullanıcılara gönderilen duyurular
                    $query->where(function ($q) use ($user) {
                        $q->where('send_type', 'all')
                            ->where('company_id', $user->company_id);
                    });

                    // Rol bazlı duyurular - Sadece kullanıcının rolüne ait veya özel olarak atanmış duyurular
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('send_type', 'role')
                            ->where(function ($q) use ($user) {
                                // Kullanıcının rolüne atanmış ve tüm rol kullanıcılarına gönderilen duyurular
                                $q->where(function ($q) use ($user) {
                                    $q->whereJsonContains('roles', (string)$user->role_id)
                                        ->where('role_user_type', 'all');
                                })
                                // VEYA özel olarak bu kullanıcıya atanmış duyurular
                                ->orWhere(function ($q) use ($user) {
                                    $q->where('role_user_type', 'specific')
                                        ->whereJsonContains('role_users', (string)$user->id);
                                });
                            });
                    });

                    // Şube bazlı duyurular - Sadece kullanıcının şubesine ait veya özel olarak atanmış duyurular
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('send_type', 'branch')
                            ->where(function ($q) use ($user) {
                                // Kullanıcının şubesine atanmış ve tüm şube kullanıcılarına gönderilen duyurular
                                $q->where(function ($q) use ($user) {
                                    $q->whereJsonContains('branches', (string)$user->branch_id)
                                        ->where('branch_user_type', 'all');
                                })
                                // VEYA özel olarak bu kullanıcıya atanmış duyurular
                                ->orWhere(function ($q) use ($user) {
                                    $q->where('branch_user_type', 'specific')
                                        ->whereJsonContains('branch_users', (string)$user->id);
                                });
                            });
                    });

                    // Departman bazlı duyurular - Sadece kullanıcının departmanına ait veya özel olarak atanmış duyurular
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('send_type', 'department')
                            ->where(function ($q) use ($user) {
                                // Kullanıcının departmanına atanmış ve tüm departman kullanıcılarına gönderilen duyurular
                                $q->where(function ($q) use ($user) {
                                    $q->whereJsonContains('departments', (string)$user->department_id)
                                        ->where('department_user_type', 'all');
                                })
                                // VEYA özel olarak bu kullanıcıya atanmış duyurular
                                ->orWhere(function ($q) use ($user) {
                                    $q->where('department_user_type', 'specific')
                                        ->whereJsonContains('department_users', (string)$user->id);
                                });
                            });
                    });
                });

            // Arama
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            // Sıralama
            $sortField = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            if (in_array($sortField, $this->sortableFields)) {
                $query->orderBy($sortField, $sortOrder);
            }

            $perPage = $request->get('per_page', 10);
            $announcements = $query->paginate($perPage);

            return ApiResponse::success(
                new AnnouncementCollection($announcements),
                'Duyurular başarıyla listelendi',
                SymfonyResponse::HTTP_OK
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Duyurular listelenirken bir hata oluştu: ' . $e->getMessage(),
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Belirli bir duyurunun detayını gösterir.
     *
     * @route GET /api/announcements/{id}
     * @uses AnnouncementResource
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $announcement = Announcement::where('id', $request->id)
                ->where('status', true)
                ->where(function ($query) use ($user) {
                    // Tüm kullanıcılara gönderilen duyurular
                    $query->where(function ($q) use ($user) {
                        $q->where('send_type', 'all')
                            ->where('company_id', $user->company_id);
                    });

                    // Rol bazlı duyurular - Sadece kullanıcının rolüne ait veya özel olarak atanmış duyurular
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('send_type', 'role')
                            ->where(function ($q) use ($user) {
                                // Kullanıcının rolüne atanmış ve tüm rol kullanıcılarına gönderilen duyurular
                                $q->where(function ($q) use ($user) {
                                    $q->whereJsonContains('roles', (string)$user->role_id)
                                        ->where('role_user_type', 'all');
                                })
                                // VEYA özel olarak bu kullanıcıya atanmış duyurular
                                ->orWhere(function ($q) use ($user) {
                                    $q->where('role_user_type', 'specific')
                                        ->whereJsonContains('role_users', (string)$user->id);
                                });
                            });
                    });

                    // Şube bazlı duyurular - Sadece kullanıcının şubesine ait veya özel olarak atanmış duyurular
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('send_type', 'branch')
                            ->where(function ($q) use ($user) {
                                // Kullanıcının şubesine atanmış ve tüm şube kullanıcılarına gönderilen duyurular
                                $q->where(function ($q) use ($user) {
                                    $q->whereJsonContains('branches', (string)$user->branch_id)
                                        ->where('branch_user_type', 'all');
                                })
                                // VEYA özel olarak bu kullanıcıya atanmış duyurular
                                ->orWhere(function ($q) use ($user) {
                                    $q->where('branch_user_type', 'specific')
                                        ->whereJsonContains('branch_users', (string)$user->id);
                                });
                            });
                    });

                    // Departman bazlı duyurular - Sadece kullanıcının departmanına ait veya özel olarak atanmış duyurular
                    $query->orWhere(function ($q) use ($user) {
                        $q->where('send_type', 'department')
                            ->where(function ($q) use ($user) {
                                // Kullanıcının departmanına atanmış ve tüm departman kullanıcılarına gönderilen duyurular
                                $q->where(function ($q) use ($user) {
                                    $q->whereJsonContains('departments', (string)$user->department_id)
                                        ->where('department_user_type', 'all');
                                })
                                // VEYA özel olarak bu kullanıcıya atanmış duyurular
                                ->orWhere(function ($q) use ($user) {
                                    $q->where('department_user_type', 'specific')
                                        ->whereJsonContains('department_users', (string)$user->id);
                                });
                            });
                    });
                })
                ->first();

            if (!$announcement) {
                return ApiResponse::error(
                    'Duyuru bulunamadı',
                    SymfonyResponse::HTTP_NOT_FOUND
                );
            }

            // Duyuruyu okundu olarak işaretle
            $announcement->markAsRead($user->id);

            return ApiResponse::success(
                new AnnouncementResource($announcement),
                'Duyuru detayı başarıyla getirildi',
                SymfonyResponse::HTTP_OK
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Duyuru detayı getirilirken bir hata oluştu: ' . $e->getMessage(),
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
        // Duyuru kaydı sonrası yapılacak ek işlemler
        // Örneğin, bildirim gönderme, log kaydetme vb.

        return $item;
    }
}
