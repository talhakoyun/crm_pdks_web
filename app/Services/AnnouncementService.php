<?php

namespace App\Services;

use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnnouncementService
{
    /**
     * Kullanıcının görebileceği duyuruları getirir.
     *
     * @param int $userId
     * @param array|null $dateRange
     * @return Collection
     */
    public function getUserAnnouncements(int $userId, ?array $dateRange = null): Collection
    {
        $query = Announcement::query()
            ->where('status', true)
            ->whereJsonContains('roles', $userId);

        if ($dateRange) {
            $query->whereBetween('start_date', $dateRange)
                ->whereBetween('end_date', $dateRange);
        } else {
            $now = Carbon::now();
            $query->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now);
        }

        return $query->get();
    }

    /**
     * Yeni bir duyuru oluşturur.
     *
     * @param array $data
     * @return Announcement
     */
    public function createAnnouncement(array $data): Announcement
    {
        return Announcement::create($data);
    }

    /**
     * Duyuruyu günceller.
     *
     * @param Announcement $announcement
     * @param array $data
     * @return bool
     */
    public function updateAnnouncement(Announcement $announcement, array $data): bool
    {
        return $announcement->update($data);
    }

    /**
     * Duyuruyu siler.
     *
     * @param Announcement $announcement
     * @return bool|null
     */
    public function deleteAnnouncement(Announcement $announcement): ?bool
    {
        return $announcement->delete();
    }
}
