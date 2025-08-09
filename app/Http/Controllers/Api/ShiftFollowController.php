<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Responses\ApiResponse;
use App\Http\Resources\Shift\ShiftListResource;
use App\Http\Resources\Shift\ShiftFollowResource;
use App\Http\Resources\Shift\ShiftFollowCollection;
use App\Http\Resources\Shift\ShiftFollowTypeResource;
use App\Http\Resources\Shift\ShiftFollowTypeCollection;
use App\Models\Setting;
use App\Models\ShiftDefinition;
use App\Models\ShiftFollow;
use App\Models\ShiftFollowType;
use App\Models\UserShift;
use App\Models\UserShiftCustom;
use App\Models\UserBranches;
use App\Models\UserZones;
use App\Models\Branch;
use App\Models\Zone;
use App\Models\UserPermit;
use App\Services\ShiftFollowService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ShiftFollowController extends BaseController
{
    /**
     * ShiftFollowService instance.
     *
     * @var ShiftFollowService
     */
    protected ShiftFollowService $shiftFollowService;

    /**
     * ShiftFollowController constructor.
     *
     * @param ShiftFollowService $shiftFollowService
     */
    public function __construct(ShiftFollowService $shiftFollowService)
    {
        $this->model = new ShiftFollow();
        $this->modelName = 'Vardiya Takibi';
        $this->relationships = ['user', 'branch', 'zone', 'shift', 'followType'];
        $this->searchableFields = ['transaction_date', 'note'];
        $this->sortableFields = ['id', 'transaction_date', 'created_at', 'updated_at'];
        $this->shiftFollowService = $shiftFollowService;
    }

    public const TYPES = [
        1 => 'check_in',
        2 => 'check_out',
        3 => 'zone'
    ];

    /**
     * Store için veriyi hazırlar.
     *
     * @param \Illuminate\Foundation\Http\FormRequest|Request $request
     * @return array
     */
    protected function prepareStoreData(\Illuminate\Foundation\Http\FormRequest|Request $request): array
    {
        // Önce BaseController'ın prepareStoreData metodunu çağıralım
        $data = parent::prepareStoreData($request);

        // IP adresini ve User Agent'ı ekle
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->userAgent();

        // Pozisyon bilgisi varsa Point formatına dönüştür
        if (isset($data['positions']) && is_array($data['positions'])) {
            $data['positions'] = new Point(
                (float)$data['positions']['latitude'],
                (float)$data['positions']['longitude']
            );
        }

        return $data;
    }

    /**
     * Kullanıcının günlük vardiya listesini döndürür.
     * Bu endpoint tarihe göre gruplandırılmış, vardiya ve bölge tipinde özetlenmiş bir liste döndürür.
     * start_date ve end_date parametreleri gönderilirse o tarih aralığı için kayıtlar döndürülür,
     * aksi takdirde kullanıcının tüm kayıtları döndürülür.
     *
     * @route GET /api/shift-follow/list
     * @uses ShiftListResource - Günlük özet veri için
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $user = Auth::user();
        $dates = null;

        // Eğer hem start_date hem de end_date varsa, tarih aralığı dizisi oluştur
        if ($request->has('start_date') && $request->has('end_date')) {
            $dates = [$request->start_date, $request->end_date];
        }

        // ShiftFollowService üzerinden verileri getir
        // Eğer $dates null ise tüm kayıtlar getirilecek, değilse tarih aralığı filtrelenecek
        $shiftList = $this->shiftFollowService->getUserShiftList($user->id, $dates);

        // Resource ile dönüşüm yap
        $formattedList = collect($shiftList)->map(function ($item) {
            return new ShiftListResource($item);
        });

        // Filtre bilgisine göre mesaj oluştur
        $message = $dates
            ? "Belirtilen tarih aralığındaki vardiya listesi başarıyla alındı"
            : "Tüm vardiya listesi başarıyla alındı";

        return ApiResponse::success($formattedList, $message, SymfonyResponse::HTTP_OK);
    }

    /**
     * Vardiya takip tiplerini listeler.
     *
     * @route GET /api/shift-follow/follow-types
     * @uses ShiftFollowTypeResource - Vardiya tipleri için
     * @return JsonResponse
     */
    public function types(): JsonResponse
    {
        try {
            $followTypes = ShiftFollowType::all();

            return ApiResponse::success(
                new ShiftFollowTypeCollection($followTypes),
                "Vardiya takip tipleri başarıyla listelendi",
                SymfonyResponse::HTTP_OK
            );
        } catch (\Exception $e) {
            return ApiResponse::serverError("Vardiya takip tipleri listelenirken bir hata oluştu: " . $e->getMessage());
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
        // Vardiya takip işlemi sonrası yapılacak ek işlemler
        // Örneğin, vardiya takip tipine göre bildirim gönderme, log kaydetme vb.

        return $item;
    }

    /**
     * İki nokta arasındaki mesafeyi hesaplar (metre cinsinden).
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Metre cinsinden dünya yarıçapı

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $lon1Rad = deg2rad($lon1);
        $lon2Rad = deg2rad($lon2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        $a = sin($deltaLat/2) * sin($deltaLat/2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon/2) * sin($deltaLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Polygon içindeki merkez noktayı hesaplar (centroid).
     * Basit polygon için kullanılabilir.
     *
     * @param object $polygon
     * @return array|null [lat, lng] formatında merkez noktası veya null
     */
    private function calculatePolygonCentroid($polygon): ?array
    {
        // Polygon formatına göre kontroller yapalım
        if (!$polygon || !is_object($polygon)) {
            return null;
        }

        // Polygon'un koordinatlarını alın
        $coordinates = null;

        // Polygon'dan koordinatları almaya çalışalım (farklı formatlara uyumlu)
        if (method_exists($polygon, 'getCoordinates')) {
            $coordinates = $polygon->getCoordinates();
        } elseif (isset($polygon->coordinates) && is_array($polygon->coordinates)) {
            $coordinates = $polygon->coordinates;
        } elseif (isset($polygon->rings) && is_array($polygon->rings)) {
            $coordinates = $polygon->rings[0] ?? null;
        }

        if (!$coordinates || !is_array($coordinates) || empty($coordinates)) {
            return null;
        }

        // İlk array seviyesini kontrol edelim
        if (isset($coordinates[0]) && is_array($coordinates[0]) && isset($coordinates[0][0]) && is_array($coordinates[0][0])) {
            $coordinates = $coordinates[0]; // İlk ring alınır
        }

        // En basit halde merkez noktayı hesaplayalım
        $latSum = 0;
        $lngSum = 0;
        $count = 0;

        foreach ($coordinates as $point) {
            // Koordinat dizisi formatını kontrol et
            if (is_array($point) && count($point) >= 2) {
                $latSum += is_numeric($point[1]) ? (float)$point[1] : 0;
                $lngSum += is_numeric($point[0]) ? (float)$point[0] : 0;
                $count++;
            }
        }

        if ($count === 0) {
            return null;
        }

        return [
            'lat' => $latSum / $count,
            'lng' => $lngSum / $count
        ];
    }

    /**
     * Noktanın polygon içinde olup olmadığını kontrol eder.
     *
     * @param float $lat
     * @param float $lng
     * @param object $polygon
     * @return bool
     */
    private function isPointInPolygon(float $lat, float $lng, $polygon): bool
    {
        if (!$polygon) {
            return false;
        }

        // Koordinatları alalım
        $coordinates = null;

        if (method_exists($polygon, 'getCoordinates')) {
            $coordinates = $polygon->getCoordinates();
        } elseif (isset($polygon->coordinates) && is_array($polygon->coordinates)) {
            $coordinates = $polygon->coordinates;
        } elseif (isset($polygon->rings) && is_array($polygon->rings)) {
            $coordinates = $polygon->rings[0] ?? null;
        }

        if (!$coordinates || !is_array($coordinates)) {
            return false;
        }

        // İlk array seviyesini kontrol edelim
        if (isset($coordinates[0]) && is_array($coordinates[0]) && isset($coordinates[0][0]) && is_array($coordinates[0][0])) {
            $coordinates = $coordinates[0]; // İlk ring alınır
        }

        $i = 0;
        $j = count($coordinates) - 1;
        $inside = false;

        for (; $i < count($coordinates); $j = $i++) {
            $xi = is_array($coordinates[$i]) ? (float)$coordinates[$i][0] : 0;
            $yi = is_array($coordinates[$i]) ? (float)$coordinates[$i][1] : 0;
            $xj = is_array($coordinates[$j]) ? (float)$coordinates[$j][0] : 0;
            $yj = is_array($coordinates[$j]) ? (float)$coordinates[$j][1] : 0;

            $intersect = (($yi > $lng) != ($yj > $lng))
                && ($lat < ($xj - $xi) * ($lng - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    /**
     * Yeni bir vardiya takip kaydı oluşturur.
     *
     * @route POST /api/shift-follow/store
     * @uses ShiftFollowResource
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Veri hazırlama
            $data = $this->prepareStoreData($request);
            $user = Auth::user();
            // transaction_date gelmezse şu anki zamanı kullan
            if (!isset($data['transaction_date'])) {
                $data['transaction_date'] = Carbon::now()->format('Y-m-d H:i:s');
            }

            $transactionDate = Carbon::parse($data['transaction_date']);
            $currentDate = $transactionDate->format('Y-m-d');

            // Kullanıcının vardiyasını kontrol et
            $userShift = $this->getUserCurrentShift($user->id, $currentDate);

            if (!$userShift) {
                return ApiResponse::error(
                    'Bu tarih için atanmış vardiya bulunamadı.',
                    SymfonyResponse::HTTP_BAD_REQUEST
                );
            }

            // Kullanıcı izinlerini kontrol et
            $userPermit = UserPermit::where('user_id', $user->id)->where('is_active', 1)->first();

            // Kullanıcının pozisyon bilgisi
            if (!isset($data['positions']) || !($data['positions'] instanceof Point)) {
                return ApiResponse::error(
                    'Geçerli konum bilgisi bulunamadı.',
                    SymfonyResponse::HTTP_BAD_REQUEST
                );
            }

            $userLat = $data['positions']->latitude;
            $userLon = $data['positions']->longitude;

            // Kullanıcının izin verilen şubeleri
            $allowedBranches = UserBranches::where('user_id', $user->id)
                ->where('is_active', 1)
                ->pluck('branch_id')
                ->toArray();

            // Kullanıcının izin verilen bölgeleri
            $allowedZones = UserZones::where('user_id', $user->id)
                ->where('is_active', 1)
                ->pluck('zone_id')
                ->toArray();

            // Zone kontrolü yapılacak mı?
            $checkZone = $userPermit ? (bool)$userPermit->allow_zone : false;

            // Dışarıda giriş yapma izni var mı?
            $allowOutside = $userPermit ? (bool)$userPermit->allow_outside : false;

            // Zone esnek mi?
            $zoneFlexible = $userPermit ? (bool)$userPermit->zone_flexible : false;

            // Çevrimdışı giriş izni var mı?
            $allowOffline = $userPermit ? (bool)$userPermit->allow_offline : false;

            // Çevrimdışı durumda izin kontrolü
            if (isset($data['is_offline']) && $data['is_offline'] && !$allowOffline) {
                return ApiResponse::error(
                    "Çevrimdışı giriş yapma yetkiniz bulunmamaktadır.",
                    SymfonyResponse::HTTP_FORBIDDEN
                );
            }

            // Kullanıcı konum kontrolü gerektirmiyorsa (dışarıda giriş izni veya esnek zone varsa)
            if ($allowOutside || !$checkZone || $zoneFlexible) {
                // Konum kontrolü yapmadan işlemi devam ettir
            } else {
                // Konum bilgisinden zone tespiti yapma
                $foundZoneAndBranch = $this->findZoneAndBranchByPosition($userLat, $userLon, $allowedZones);
                if ($foundZoneAndBranch === null) {
                    return ApiResponse::error(
                        "Bulunduğunuz konum hiçbir yetkili olduğunuz bölge içinde değil veya bölgelere çok uzaksınız.",
                        SymfonyResponse::HTTP_BAD_REQUEST
                    );
                }

                // Bulunan zone ve branch'ı data'ya ekle
                $data['zone_id'] = $foundZoneAndBranch['zone_id'];
                $data['branch_id'] = $foundZoneAndBranch['branch_id'];
            }

            $inTolerance = Setting::where('key', 'in_tolerance')->first()->value ?? 0;
            $outTolerance = Setting::where('key', 'out_tolerance')->first()->value ?? 0;

            if (isset($data['shift_follow_type_id'])) {
                $followType = ShiftFollowType::find($data['shift_follow_type_id']);

                $checkInType = ShiftFollowType::where('type', 'check_in')->first();
                $checkOutType = ShiftFollowType::where('type', 'check_out')->first();

                $hasCheckIn = ShiftFollow::where('user_id', $user->id)
                    ->where('shift_follow_type_id', $checkInType->id)
                    ->whereDate('transaction_date', $currentDate)
                    ->exists();

                $hasCheckOut = ShiftFollow::where('user_id', $user->id)
                    ->where('shift_follow_type_id', $checkOutType->id)
                    ->whereDate('transaction_date', $currentDate)
                    ->exists();

                $lastRecord = ShiftFollow::where('user_id', $user->id)
                    ->whereDate('transaction_date', $currentDate)
                    ->latest()
                    ->first();

                // Giriş kaydı yapılıyorsa
                if ($followType && $followType->type === 'check_in') {
                    if ($checkInType && $checkOutType) {
                        // Eğer giriş kaydı var ve çıkış kaydı yoksa hata döndür
                        if ($hasCheckIn && !$hasCheckOut) {
                            return ApiResponse::error(
                                'Aynı gün için çıkış kaydı olmadan tekrar giriş kaydı yapamazsınız.',
                                SymfonyResponse::HTTP_BAD_REQUEST
                            );
                        }

                        // Eğer son kayıt giriş kaydı ise tekrar giriş gelirse hata döndür
                        if($hasCheckIn && $lastRecord && $lastRecord->shift_follow_type_id == $checkInType->id) {
                            return ApiResponse::error(
                                'Aynı gün için giriş yapmışsınız. Önce çıkış yapmalısınız.',
                                SymfonyResponse::HTTP_BAD_REQUEST
                            );
                        }

                        $shiftStartTime = Carbon::parse($currentDate . ' ' . $userShift->start_time);
                        // Geç gelme kontrolü
                        if ($transactionDate > $shiftStartTime) {
                            $lateMinutes = $shiftStartTime->diffInMinutes($transactionDate);
                            // Eğer tolerans aşıldıysa açıklama zorunlu
                            if ($lateMinutes > $inTolerance) {
                                if (empty($data['note'])) {
                                    return ApiResponse::error(
                                        'Geç giriş yaptığınız için açıklama girmeniz zorunludur.',
                                        SymfonyResponse::HTTP_BAD_REQUEST
                                    );
                                }

                                $data['is_late'] = true;
                                $data['late_minutes'] = $lateMinutes;
                            }
                        }
                    }
                } elseif ($followType && $followType->type === 'check_out') {
                    if ($checkInType && $checkOutType) {
                        // Eğer giriş kaydı yoksa çıkış yapılamaz
                        if (!$hasCheckIn) {
                            return ApiResponse::error(
                                'Aynı gün için giriş kaydı olmadan çıkış yapamazsınız.',
                                SymfonyResponse::HTTP_BAD_REQUEST
                            );
                        }

                        // Eğer son kayıt çıkış kaydı ise tekrar çıkış gelirse hata döndür
                        if($hasCheckOut && $lastRecord && $lastRecord->shift_follow_type_id == $checkOutType->id) {
                            return ApiResponse::error(
                                'Aynı gün için çıkış yapmışsınız. Önce giriş yapmalısınız.',
                                SymfonyResponse::HTTP_BAD_REQUEST
                            );
                        }

                        // Erken çıkma kontrolü - ShiftDefinition tablosundaki end_time kullanarak
                        $shiftEndTime = Carbon::parse($currentDate . ' ' . $userShift->end_time);

                        // Eğer transactionDate, shiftEndTime'dan önce ise (yani erken çıkıyorsa)
                        if ($transactionDate < $shiftEndTime) {
                            $earlyMinutes = $shiftEndTime->diffInMinutes($transactionDate);

                            // Eğer tolerans aşıldıysa açıklama zorunlu
                            if ($earlyMinutes > $outTolerance) {
                                if (empty($data['note'])) {
                                    return ApiResponse::error(
                                        'Erken çıkış yaptığınız için açıklama girmeniz zorunludur.',
                                        SymfonyResponse::HTTP_BAD_REQUEST
                                    );
                                }

                                // Erken çıkma durumunu kaydet
                                $data['is_early_out'] = true;
                                $data['early_out_minutes'] = $earlyMinutes;
                            }
                        }
                    }
                }
            }

            // User ID ekleme
            $data['user_id'] = $user->id;

            // Company ID eksikse kullanıcıdan al
            if (!isset($data['company_id'])) {
                $data['company_id'] = $user->company_id;
            }

            // ShiftFollowService üzerinden oluşturma işlemini yap
            $shiftFollow = $this->shiftFollowService->createShiftFollow($data);

            // İlişkileri yükle
            if (!empty($this->relationships)) {
                $shiftFollow->load($this->relationships);
            }

            // saveHook metodu varsa çağır
            if (method_exists($this, 'saveHook')) {
                $shiftFollow = $this->saveHook($shiftFollow);
            }

            // Yanıtı ShiftFollowResource üzerinden şekillendir
            $message = "";

            if (isset($followType)) {
                $type = $followType->type;
                $message = $type === 'check_in' ? 'Giriş kaydınız başarıyla oluşturuldu' : 'Çıkış kaydınız başarıyla oluşturuldu';
            } else {
                $message = "Vardiya takip kaydınız başarıyla oluşturuldu";
            }

            return ApiResponse::success(
                new ShiftFollowResource($shiftFollow),
                $message,
                SymfonyResponse::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Vardiya takip kaydı oluşturulurken bir hata oluştu: ' . $e->getMessage(),
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Verilen konum bilgisine göre kullanıcının yetkili olduğu bölge (zone) ve şube (branch) bilgisini bulur.
     *
     * @param float $lat Enlem
     * @param float $lon Boylam
     * @param array $allowedZoneIds Kullanıcının yetkili olduğu bölge ID'leri
     * @param float $maxDistance Maksimum mesafe (metre cinsinden)
     * @return array|null ['zone_id' => int, 'branch_id' => int] formatında veya null
     */
    private function findZoneAndBranchByPosition(float $lat, float $lon, array $allowedZoneIds, float $maxDistance = 100): ?array
    {
        if (empty($allowedZoneIds)) {
            return null;
        }

        // Kullanıcının yetkili olduğu bölgeleri al
        $zones = Zone::whereIn('id', $allowedZoneIds)->get();

        if ($zones->isEmpty()) {
            return null;
        }

        $foundZone = null;
        $closestDistance = PHP_FLOAT_MAX;
        $closestZone = null;

        foreach ($zones as $zone) {
            if ($zone->positions) {
                // Önce polygon içinde kontrol et (daha kesin sonuç)
                $isInPolygon = $this->isPointInPolygon($lat, $lon, $zone->positions);

                if ($isInPolygon) {
                    // Polygon içindeyse hemen bu zone'u kullan
                    $foundZone = $zone;
                    break;
                }

                // Polygon içinde değilse, merkeze olan mesafeyi kontrol et
                $center = $this->calculatePolygonCentroid($zone->positions);

                if ($center && isset($center['lat']) && isset($center['lng'])) {
                    $distance = $this->calculateDistance($lat, $lon, $center['lat'], $center['lng']);

                    if ($distance < $closestDistance) {
                        $closestDistance = $distance;
                        $closestZone = $zone;
                    }
                }
            }
        }

        // Polygon içinde bir zone bulundu mu?
        if ($foundZone) {
            return [
                'zone_id' => $foundZone->id,
                'branch_id' => $foundZone->branch_id
            ];
        }

        // Polygon içinde bulunamadıysa, en yakın zone'u kontrol et
        if ($closestZone && $closestDistance <= $maxDistance) {
            return [
                'zone_id' => $closestZone->id,
                'branch_id' => $closestZone->branch_id
            ];
        }

        // Hiçbir zone bulunamadıysa null döndür
        return null;
    }

    /**
     * QR kod şifresini çözer.
     *
     * @param string $base64Value Şifrelenmiş veri
     * @param string|null $date Tarih (null ise bugün)
     * @return string
     */
    private function decrypt(string $base64Value, $date = null): string
    {
        $dayOfYear = !is_null($date) ? Carbon::parse($date)->dayOfYear() : Carbon::today()->dayOfYear();
        $keyUtf8 = "mtk-" . $dayOfYear . '-soft1994';
        $ivUtf8 = "snm-" . $dayOfYear . '-soft1994';
        return openssl_decrypt(
            $base64Value,
            'AES-256-CBC',
            substr(hash('sha256', $keyUtf8), 0, 32),
            0,
            substr(hash('sha256', $ivUtf8), 0, 16),
        );
    }

    /**
     * QR kod ile vardiya takip kaydı oluşturur.
     *
     * @route POST /api/shift-follow/qr-store
     * @uses ShiftFollowResource
     * @param Request $request
     * @return JsonResponse
     */
    public function qrStore(Request $request): JsonResponse
    {
        try {
            // QR kodu kontrol et
            $qr = $request->qr_str;
            $type = $request->type; // 1: Giriş, 2: Çıkış
            $note = $request->note; // Açıklama (geç giriş, erken çıkış vb. durumlar için)

            // Giriş/çıkış tipini belirle
            $followType = null;
            if ($type == 1) {
                $followType = ShiftFollowType::where('type', 'check_in')->first();
            } else if ($type == 2) {
                $followType = ShiftFollowType::where('type', 'check_out')->first();
            }

            if (!$followType) {
                return ApiResponse::error(
                    'Geçersiz işlem tipi.',
                    SymfonyResponse::HTTP_BAD_REQUEST
                );
            }

            $dynamicQr = null;
            $now = Carbon::now();

            // QR kod validasyonu
            if ($qr) {
                $qr_decode = base64_decode($qr);
                $qr_decode = json_decode($qr_decode, true);

                if (!isset($qr_decode['is_dynamic']) || !isset($qr_decode['date']) || !isset($qr_decode['data'])) {
                    return ApiResponse::error(
                        'Karekod ayrıştırılamadı.(1)',
                        SymfonyResponse::HTTP_BAD_REQUEST
                    );
                }

                if ($qr_decode['is_dynamic'] == 1) {
                    $qrDate = Carbon::parse($qr_decode['date']);

                    if ($qrDate->format('d.m.Y H:i') != $now->format('d.m.Y H:i')) {
                        return ApiResponse::error(
                            'Karekod zamanı hatalı.',
                            SymfonyResponse::HTTP_BAD_REQUEST
                        );
                    }

                    // QR kodun geçerliliği 1 dakika
                    if (!$qrDate->between($now->copy()->subMinute(), $now)) {
                        return ApiResponse::error(
                            'Karekod süresi dolmuş.',
                            SymfonyResponse::HTTP_BAD_REQUEST
                        );
                    }
                }

                $decrypt = $this->decrypt($qr_decode['data'], $qr_decode['date']);
                if ($decrypt != '8-' . $qr_decode['date'] . '-tk') {
                    return ApiResponse::error(
                        'Karekod doğrulama hatası.',
                        SymfonyResponse::HTTP_BAD_REQUEST
                    );
                }

                $dynamicQr = $qr_decode['is_dynamic'];
            } else {
                return ApiResponse::error(
                    'Karekod bilgisi gereklidir.',
                    SymfonyResponse::HTTP_BAD_REQUEST
                );
            }

            // Standart vardiya takip işlemleri
            $user = Auth::user();

            // Veri hazırlama
            $data = [];
            if ($request->has('positions')) {
                $data['positions'] = new Point(
                    (float)$request->positions['latitude'],
                    (float)$request->positions['longitude']
                );
            } else {
                return ApiResponse::error(
                    'Konum bilgisi gereklidir.',
                    SymfonyResponse::HTTP_BAD_REQUEST
                );
            }

            $data['transaction_date'] = $now->format('Y-m-d H:i:s');
            $data['shift_follow_type_id'] = $followType->id;
            $data['note'] = $note;
            $data['ip_address'] = $request->ip();
            $data['user_agent'] = $request->userAgent();
            $data['is_qr'] = true;
            $data['qr_type'] = $dynamicQr ? 'dynamic' : 'static';

            $currentDate = $now->format('Y-m-d');

            // Kullanıcının vardiyasını kontrol et
            $userShift = $this->getUserCurrentShift($user->id, $currentDate);

            if (!$userShift) {
                return ApiResponse::error(
                    'Bu tarih için atanmış vardiya bulunamadı.',
                    SymfonyResponse::HTTP_BAD_REQUEST
                );
            }

            // Kullanıcı izinlerini kontrol et
            $userPermit = UserPermit::where('user_id', $user->id)->where('is_active', 1)->first();

            $userLat = $data['positions']->latitude;
            $userLon = $data['positions']->longitude;

            // Kullanıcının izin verilen şubeleri
            $allowedBranches = UserBranches::where('user_id', $user->id)
                ->where('is_active', 1)
                ->pluck('branch_id')
                ->toArray();

            // Kullanıcının izin verilen bölgeleri
            $allowedZones = UserZones::where('user_id', $user->id)
                ->where('is_active', 1)
                ->pluck('zone_id')
                ->toArray();

            // Zone kontrolü yapılacak mı?
            $checkZone = $userPermit ? (bool)$userPermit->allow_zone : false;

            // Dışarıda giriş yapma izni var mı?
            $allowOutside = $userPermit ? (bool)$userPermit->allow_outside : false;

            // Zone esnek mi?
            $zoneFlexible = $userPermit ? (bool)$userPermit->zone_flexible : false;

            // Çevrimdışı giriş izni var mı?
            $allowOffline = $userPermit ? (bool)$userPermit->allow_offline : false;

            // Çevrimdışı durumda izin kontrolü
            if (isset($data['is_offline']) && $data['is_offline'] && !$allowOffline) {
                return ApiResponse::error(
                    "Çevrimdışı giriş yapma yetkiniz bulunmamaktadır.",
                    SymfonyResponse::HTTP_FORBIDDEN
                );
            }

            // Kullanıcı konum kontrolü gerektirmiyorsa (dışarıda giriş izni veya esnek zone varsa)
            if ($allowOutside || !$checkZone || $zoneFlexible) {
                // Konum kontrolü yapmadan işlemi devam ettir
            } else {
                // Konum bilgisinden zone tespiti yapma
                $foundZoneAndBranch = $this->findZoneAndBranchByPosition($userLat, $userLon, $allowedZones);

                if ($foundZoneAndBranch === null) {
                    return ApiResponse::error(
                        "Bulunduğunuz konum hiçbir yetkili olduğunuz bölge içinde değil veya bölgelere çok uzaksınız.",
                        SymfonyResponse::HTTP_BAD_REQUEST
                    );
                }

                // Bulunan zone ve branch'ı data'ya ekle
                $data['zone_id'] = $foundZoneAndBranch['zone_id'];
                $data['branch_id'] = $foundZoneAndBranch['branch_id'];
            }

            $inTolerance = Setting::where('key', 'in_tolerance')->first()->value ?? 0;
            $outTolerance = Setting::where('key', 'out_tolerance')->first()->value ?? 0;

            // Giriş/çıkış sıralaması kontrolü
            $checkInType = ShiftFollowType::where('type', 'check_in')->first();
            $checkOutType = ShiftFollowType::where('type', 'check_out')->first();

            $hasCheckIn = ShiftFollow::where('user_id', $user->id)
                ->where('shift_follow_type_id', $checkInType->id)
                ->whereDate('transaction_date', $currentDate)
                ->exists();

            $hasCheckOut = ShiftFollow::where('user_id', $user->id)
                ->where('shift_follow_type_id', $checkOutType->id)
                ->whereDate('transaction_date', $currentDate)
                ->exists();

            $lastRecord = ShiftFollow::where('user_id', $user->id)
                ->whereDate('transaction_date', $currentDate)
                ->latest()
                ->first();

            // Giriş kaydı yapılıyorsa
            if ($type == 1) {
                if ($checkInType && $checkOutType) {
                    // Eğer giriş kaydı var ve çıkış kaydı yoksa hata döndür
                    if ($hasCheckIn && !$hasCheckOut) {
                        return ApiResponse::error(
                            'Aynı gün için çıkış kaydı olmadan tekrar giriş kaydı yapamazsınız.',
                            SymfonyResponse::HTTP_BAD_REQUEST
                        );
                    }

                    // Eğer son kayıt giriş kaydı ise tekrar giriş gelirse hata döndür
                    if($hasCheckIn && $lastRecord && $lastRecord->shift_follow_type_id == $checkInType->id) {
                        return ApiResponse::error(
                            'Aynı gün için giriş yapmışsınız. Önce çıkış yapmalısınız.',
                            SymfonyResponse::HTTP_BAD_REQUEST
                        );
                    }

                    $shiftStartTime = Carbon::parse($currentDate . ' ' . $userShift->start_time);
                    // Geç gelme kontrolü
                    if ($now > $shiftStartTime) {
                        $lateMinutes = $shiftStartTime->diffInMinutes($now);
                        // Eğer tolerans aşıldıysa açıklama zorunlu
                        if ($lateMinutes > $inTolerance) {
                            if (empty($data['note'])) {
                                return ApiResponse::error(
                                    'Geç giriş yaptığınız için açıklama girmeniz zorunludur.',
                                    SymfonyResponse::HTTP_BAD_REQUEST
                                );
                            }

                            $data['is_late'] = true;
                            $data['late_minutes'] = $lateMinutes;
                        }
                    }
                }
            } elseif ($type == 2) {
                if ($checkInType && $checkOutType) {
                    // Eğer giriş kaydı yoksa çıkış yapılamaz
                    if (!$hasCheckIn) {
                        return ApiResponse::error(
                            'Aynı gün için giriş kaydı olmadan çıkış yapamazsınız.',
                            SymfonyResponse::HTTP_BAD_REQUEST
                        );
                    }

                    // Eğer son kayıt çıkış kaydı ise tekrar çıkış gelirse hata döndür
                    if($hasCheckOut && $lastRecord && $lastRecord->shift_follow_type_id == $checkOutType->id) {
                        return ApiResponse::error(
                            'Aynı gün için çıkış yapmışsınız. Önce giriş yapmalısınız.',
                            SymfonyResponse::HTTP_BAD_REQUEST
                        );
                    }

                    // Erken çıkma kontrolü - ShiftDefinition tablosundaki end_time kullanarak
                    $shiftEndTime = Carbon::parse($currentDate . ' ' . $userShift->end_time);

                    // Eğer now, shiftEndTime'dan önce ise (yani erken çıkıyorsa)
                    if ($now < $shiftEndTime) {
                        $earlyMinutes = $shiftEndTime->diffInMinutes($now);

                        // Eğer tolerans aşıldıysa açıklama zorunlu
                        if ($earlyMinutes > $outTolerance) {
                            if (empty($data['note'])) {
                                return ApiResponse::error(
                                    'Erken çıkış yaptığınız için açıklama girmeniz zorunludur.',
                                    SymfonyResponse::HTTP_BAD_REQUEST
                                );
                            }

                            // Erken çıkma durumunu kaydet
                            $data['is_early_out'] = true;
                            $data['early_out_minutes'] = $earlyMinutes;
                        }
                    }
                }
            }

            // User ID ve Company ID ekleme
            $data['user_id'] = $user->id;

            if (!isset($data['company_id'])) {
                $data['company_id'] = $user->company_id;
            }

            // ShiftFollowService üzerinden oluşturma işlemini yap
            $shiftFollow = $this->shiftFollowService->createShiftFollow($data);

            // İlişkileri yükle
            if (!empty($this->relationships)) {
                $shiftFollow->load($this->relationships);
            }

            // saveHook metodu varsa çağır
            if (method_exists($this, 'saveHook')) {
                $shiftFollow = $this->saveHook($shiftFollow);
            }

            // Yanıtı ShiftFollowResource üzerinden şekillendir
            $message = $type == 1 ? 'QR kod ile giriş kaydınız başarıyla oluşturuldu' : 'QR kod ile çıkış kaydınız başarıyla oluşturuldu';

            return ApiResponse::success(
                new ShiftFollowResource($shiftFollow),
                $message,
                SymfonyResponse::HTTP_CREATED
            );

        } catch (\Exception $e) {
            return ApiResponse::error(
                'QR kod ile vardiya takip kaydı oluşturulurken bir hata oluştu: ' . $e->getMessage(),
                SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Kullanıcının belirli bir tarih için vardiyasını döndürür
     * Önce user_shift_customs tablosunda arar, bulamazsa user_shifts tablosundan döndürür
     *
     * @param int $userId
     * @param string $date Y-m-d formatında tarih
     * @return mixed|null Vardiya bilgisi veya null
     */
    private function getUserCurrentShift(int $userId, string $date)
    {
        $customShift = UserShiftCustom::where('user_id', $userId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('is_active', 1)
            ->first();

        if ($customShift) {
            $shiftDefinition = ShiftDefinition::find($customShift->shift_definition_id);
            if ($shiftDefinition) {
                return $shiftDefinition;
            }
        }

        // Eğer özel vardiya bulunamazsa, normal vardiyayı kontrol et
        $userShift = UserShift::where('user_id', $userId)
            ->where('is_active', 1)
            ->first();

        if ($userShift) {
            // Vardiya tanımını al
            $shiftDefinition = ShiftDefinition::find($userShift->shift_definition_id);
            if ($shiftDefinition) {
                return $shiftDefinition;
            }
        }

        return null;
    }
}
