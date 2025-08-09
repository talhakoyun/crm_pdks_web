<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Holiday;
use App\Models\HolidayType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Point;

class HolidayService
{
    /**
     * Kullanıcının izin taleplerini tarih aralığına göre filtreler.
     *
     * @param int $userId Kullanıcı ID
     * @param array|null $filters Filtreler (date_start, date_end, status, sort_by, sort_order, per_page)
     * @param array $relationships Yüklenecek ilişkiler
     * @return LengthAwarePaginator
     */
    public function getUserHolidays(int $userId, ?array $filters = null, array $relationships = []): LengthAwarePaginator
    {
        // Query başlatma
        $query = Holiday::where('user_id', $userId);

        // İlişkileri yükle
        if (!empty($relationships)) {
            $query->with($relationships);
        }

        // Filtreler
        if ($filters) {
            // Tarih filtresi
            if (isset($filters['date_start']) && isset($filters['date_end'])) {
                $query->byDateRange($filters['date_start'], $filters['date_end']);
            } elseif (isset($filters['date_start'])) {
                $query->where('start_date', '>=', $filters['date_start']);
            } elseif (isset($filters['date_end'])) {
                $query->where('end_date', '<=', $filters['date_end']);
            }

            // Durum filtresi
            if (isset($filters['status']) && $filters['status'] !== 'all') {
                $query->where('status', $filters['status']);
            }

            // Sıralama
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Varsayılan sıralama
            $query->orderBy('created_at', 'desc');
        }

        // Sayfalama
        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    /**
     * Bir kullanıcı için belirli tarih aralığında zaten izin var mı kontrol eder.
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    public function hasExistingHoliday(int $userId, string $startDate, string $endDate): bool
    {
        return Holiday::where('user_id', $userId)
            ->where(function($query) use ($startDate, $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    // Başlangıç tarihi, mevcut izinlerin aralığında mı?
                    $q->where('start_date', '<=', $startDate)
                      ->where('end_date', '>=', $startDate);
                })
                ->orWhere(function($q) use ($startDate, $endDate) {
                    // Bitiş tarihi, mevcut izinlerin aralığında mı?
                    $q->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $endDate);
                })
                ->orWhere(function($q) use ($startDate, $endDate) {
                    // Yeni izin, mevcut bir izni kapsıyor mu?
                    $q->where('start_date', '>=', $startDate)
                      ->where('end_date', '<=', $endDate);
                });
            })
            ->exists();
    }

    /**
     * İzin talebi oluşturur.
     *
     * @param array $data
     * @param bool $isPendingRequest Eğer true ise, izin "pending" durumunda oluşturulur
     * @return Holiday
     */
    public function createHoliday(array $data, bool $isPendingRequest = false): Holiday
    {
        return DB::transaction(function() use ($data, $isPendingRequest) {
            // Kullanıcı verileri
            $user = Auth::user();

            // Temel verileri hazırla
            $holidayData = [
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'branch_id' => $user->branch_id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'note' => $data['note'] ?? null,
                'type' => $data['type'] ?? null,
                'created_by' => $user->id
            ];

            // Eğer pending request ise, durumu "pending" olarak ayarla
            if ($isPendingRequest) {
                $holidayData['status'] = 'pending';
            }

            return Holiday::create($holidayData);
        });
    }

    /**
     * İzni onaylar.
     *
     * @param int $holidayId
     * @param int $approverId
     * @return Holiday
     */
    public function approveHoliday(int $holidayId, int $approverId): Holiday
    {
        return DB::transaction(function() use ($holidayId, $approverId) {
            $holiday = Holiday::findOrFail($holidayId);
            $holiday->status = 'approved';
            $holiday->approved_by = $approverId;
            $holiday->approved_at = Carbon::now();
            $holiday->save();

            return $holiday;
        });
    }

    /**
     * İzni reddeder.
     *
     * @param int $holidayId
     * @param int $rejecterId
     * @param string|null $rejectReason
     * @return Holiday
     */
    public function rejectHoliday(int $holidayId, int $rejecterId, ?string $rejectReason = null): Holiday
    {
        return DB::transaction(function() use ($holidayId, $rejecterId, $rejectReason) {
            $holiday = Holiday::findOrFail($holidayId);
            $holiday->status = 'rejected';
            $holiday->rejected_by = $rejecterId;
            $holiday->rejected_at = Carbon::now();

            if ($rejectReason) {
                $holiday->reject_reason = $rejectReason;
            }

            $holiday->save();

            return $holiday;
        });
    }

    /**
     * İzin taleplerini listeleyen filtreleme metodunun daha genel versiyonu.
     * Bu metot yöneticiler için tüm izinleri listelemek için kullanılabilir.
     *
     * @param array|null $filters
     * @param array $relationships
     * @return LengthAwarePaginator
     */
    public function listHolidays(?array $filters = null, array $relationships = []): LengthAwarePaginator
    {
        $query = Holiday::query();

        // İlişkileri yükle
        if (!empty($relationships)) {
            $query->with($relationships);
        }

        // Filtreler
        if ($filters) {
            // Kullanıcı filtresi
            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }

            // Şirket filtresi
            if (isset($filters['company_id'])) {
                $query->where('company_id', $filters['company_id']);
            }

            // Şube filtresi
            if (isset($filters['branch_id'])) {
                $query->where('branch_id', $filters['branch_id']);
            }

            // Tarih filtresi
            if (isset($filters['date_start']) && isset($filters['date_end'])) {
                $query->byDateRange($filters['date_start'], $filters['date_end']);
            } elseif (isset($filters['date_start'])) {
                $query->where('start_date', '>=', $filters['date_start']);
            } elseif (isset($filters['date_end'])) {
                $query->where('end_date', '<=', $filters['date_end']);
            }

            // Durum filtresi
            if (isset($filters['status']) && $filters['status'] !== 'all') {
                $query->where('status', $filters['status']);
            }

            // Tip filtresi
            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            // Sıralama
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);
        } else {
            // Varsayılan sıralama
            $query->orderBy('created_at', 'desc');
        }

        // Sayfalama
        $perPage = $filters['per_page'] ?? 15;
        return $query->paginate($perPage);
    }
}
