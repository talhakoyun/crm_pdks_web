<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HourlyLeave;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class HourlyLeaveService
{
    /**
     * Kullanıcının saatlik izinlerini getirir.
     *
     * @param int $userId
     * @param array $filters
     * @param array $relationships
     * @return Collection|LengthAwarePaginator
     */
    public function getUserHourlyLeaves(int $userId, array $filters = [], array $relationships = []): Collection|LengthAwarePaginator
    {
        $query = HourlyLeave::with($relationships)->where('user_id', $userId);

        // Tarih filtresi
        if (isset($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        // Durum filtresi
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // İzin tipi filtresi
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Arama filtresi
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('date', 'like', "%{$search}%")
                  ->orWhere('start_time', 'like', "%{$search}%")
                  ->orWhere('end_time', 'like', "%{$search}%");
            });
        }

        // Sıralama
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Sayfalama
        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Yeni saatlik izin talebi oluşturur.
     *
     * @param array $data
     * @return HourlyLeave
     */
    public function createHourlyLeave(array $data): HourlyLeave
    {
        // Varsayılan durum
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }

        return HourlyLeave::create($data);
    }

    /**
     * Saatlik izin talebini günceller.
     *
     * @param int $id
     * @param array $data
     * @return HourlyLeave|null
     */
    public function updateHourlyLeave(int $id, array $data): ?HourlyLeave
    {
        $hourlyLeave = HourlyLeave::find($id);

        if ($hourlyLeave) {
            $hourlyLeave->update($data);
            return $hourlyLeave;
        }

        return null;
    }

    /**
     * Saatlik izin talebini siler.
     *
     * @param int $id
     * @return bool
     */
    public function deleteHourlyLeave(int $id): bool
    {
        $hourlyLeave = HourlyLeave::find($id);

        if ($hourlyLeave) {
            return $hourlyLeave->delete();
        }

        return false;
    }

    /**
     * Kullanıcının belirli bir tarihte saatlik izin talebi var mı kontrol eder.
     *
     * @param int $userId
     * @param string $date
     * @return bool
     */
    public function hasHourlyLeaveOnDate(int $userId, string $date): bool
    {
        return HourlyLeave::where('user_id', $userId)
                         ->where('date', $date)
                         ->exists();
    }

    /**
     * Kullanıcının belirli bir tarih aralığında saatlik izin talepleri var mı kontrol eder.
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    public function hasHourlyLeaveInDateRange(int $userId, string $startDate, string $endDate): bool
    {
        return HourlyLeave::where('user_id', $userId)
                         ->whereBetween('date', [$startDate, $endDate])
                         ->exists();
    }
}
