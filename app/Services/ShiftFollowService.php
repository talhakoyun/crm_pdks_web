<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Api\ForbiddenException;
use App\Exceptions\Api\ServerException;
use App\Exceptions\Api\UnauthorizedException;
use App\Models\ShiftFollow;
use App\Models\ShiftFollowType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ShiftFollowService
{
    /**
     * Kullanıcının vardiya listesini getirir.
     *
     * @param int $userId
     * @param array|null $dates Tarih aralığı [start_date, end_date]
     * @return array
     */
    public function getUserShiftList(int $userId, ?array $dates = null): array
    {
        // Kullanıcının vardiya bilgilerini çek
        $query = ShiftFollow::where('user_id', $userId);

        // Eğer dates parametresi varsa ve içinde iki tarih varsa, tarih aralığına göre filtrele
        if ($dates !== null && count($dates) === 2) {
            $query->whereBetween('transaction_date', $dates);
        }
        // Dates parametresi yoksa tüm kayıtları getir - eski ay filtrelemesini kaldırıyoruz

        $shiftList = $query->with('branch', 'followType', 'zone')
            ->orderBy('transaction_date')
            ->get();

        return $this->formatShiftList($shiftList);
    }

    /**
     * Vardiya listesini formatlar.
     *
     * @param Collection $shiftList
     * @return array
     */
    private function formatShiftList(Collection $shiftList): array
    {
        $tempList = [];

        foreach ($shiftList as $record) {
            $followType = $record->followType;
            $date = Carbon::parse($record->transaction_date)->format('Y-m-d');
            $time = Carbon::parse($record->transaction_date)->format('H:i');
            $recordId = $record->id; // Benzersiz bir kimlik için kayıt ID'sini kullan

            // Her kayıt için benzersiz bir anahtar oluştur
            $key = $date . '_' . $recordId;

            if ($followType && in_array($followType->type, ['in', 'out'])) {
                // Check-in/check-out kayıtları için
                $tempList[$key] = [
                    'id' => $record->id,
                    'datetime' => $record->transaction_date,
                    'date' => $date,
                    'time' => $time,
                    'type' => 'shift',
                    'action_type' => $followType->type,
                ];
            } else {
                // Zone kayıtları için
                $tempList[$key] = [
                    'id' => $record->id,
                    'datetime' => $record->transaction_date,
                    'date' => $date,
                    'time' => $time,
                    'type' => 'zone',
                ];
            }
        }

        // Kronolojik sıralama
        usort($tempList, function ($a, $b) {
            if (!$a['datetime'] || !$b['datetime']) {
                return 0;
            }

            if ($a['datetime'] == $b['datetime']) {
                return 0;
            }

            return $a['datetime'] < $b['datetime'] ? -1 : 1;
        });

        return array_values($tempList);
    }



    /**
     * Aynı gün içinde check-in kaydı var mı kontrol eder.
     *
     * @param int $userId
     * @param string $checkType
     * @param Carbon|null $date
     * @return bool
     */
    public function hasExistingCheckRecord(int $userId, string $checkType, ?Carbon $date = null): bool
    {
        $date = $date ?? Carbon::now();

        // İlgili vardiya takip tipini bul
        $followType = ShiftFollowType::where('type', $checkType)->first();
        if (!$followType) {
            return false;
        }

        $exists = ShiftFollow::where('user_id', $userId)
            ->where('shift_follow_type_id', $followType->id)
            ->whereDate('transaction_date', $date->toDateString())
            ->exists();

        return $exists;
    }

    /**
     * Check-in işlemi için mesai saati kontrolü yapar.
     *
     * @param int $userId
     * @param int $companyId
     * @param Carbon $checkTime
     * @param string $checkType
     * @param string|null $note
     * @return array|JsonResponse
     */
    public function validateShiftTime(int $userId, $company, Carbon $checkTime, string $checkType, ?string $note = null)
    {
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return response()->json([
                "status" => false,
                "message" => "Kullanıcı bulunamadı.",
                "data" => []
            ], SymfonyResponse::HTTP_BAD_REQUEST);
        }

        // Vardiya saatlerini kontrol et
        if ($user->shiftTime == null && ($user->shift_start_time == null || $user->shift_end_time == null)) {
            return response()->json([
                "status" => false,
                "message" => "Şirketiniz tarafından çalışma saatleri tanımlanmamıştır.",
                "data" => []
            ], SymfonyResponse::HTTP_BAD_REQUEST);
        }

        // Not varsa doğrudan geçir
        if ($note !== null) {
            return ["status" => true];
        }

        // Tolerans süresini hesapla
        $tolerance = $checkType == 'in' ?
            $company->shift_start_tolerance :
            -$company->shift_end_tolerance;

        // Vardiya başlangıç/bitiş saatini al
        $shiftTime = Carbon::parse(
            $checkType == 'in' ?
                $user->shiftTime->start_time ?? $user->shift_start_time :
                $user->shiftTime->end_time ?? $user->shift_end_time
        )
        ->setDate($checkTime->year, $checkTime->month, $checkTime->day)
        ->addMinutes(\intval($tolerance) ?? 0);

        // Giriş/çıkış kontrolü
        if ($checkType == 'out' && $checkTime <= $shiftTime) {
            // Erken çıkış
            return response()->json([
                "status" => false,
                "message" => "Lütfen " . $this->calculateDateDiff($checkTime, $shiftTime) . " erken çıkış sebebinizi belirtiniz.",
                "note_required" => true,
                "data" => []
            ], SymfonyResponse::HTTP_BAD_REQUEST);
        }

        if ($checkType == 'in' && $checkTime >= $shiftTime) {
            // Geç giriş
            return response()->json([
                "status" => false,
                "message" => "Lütfen " . $this->calculateDateDiff($shiftTime, $checkTime) . " geç giriş sebebinizi belirtiniz.",
                "note_required" => true,
                "data" => []
            ], SymfonyResponse::HTTP_BAD_REQUEST);
        }

        return ["status" => true];
    }

    /**
     * İki tarih arasındaki farkı hesaplar.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return string
     */
    private function calculateDateDiff(Carbon $start, Carbon $end): string
    {
        $diff = $end->diff($start);
        $hours = $diff->h + ($diff->days * 24);
        $minutes = $diff->i;
        $seconds = $diff->s;

        $result = [];

        if ($hours > 0) {
            $result[] = $hours . " saat";
        }

        if ($minutes > 0) {
            $result[] = $minutes . " dakika";
        }

        if ($seconds > 0 && count($result) === 0) {
            $result[] = $seconds . " saniye";
        }

        return empty($result) ? "0 dakika" : implode(" ", $result);
    }

    /**
     * Vardiya kaydı oluşturur.
     *
     * @param array $data
     * @return ShiftFollow
     */
    public function createShiftFollow(array $data): ShiftFollow
    {
        return DB::transaction(function() use ($data) {
            return ShiftFollow::create($data);
        });
    }
}
