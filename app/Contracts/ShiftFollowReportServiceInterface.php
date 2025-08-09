<?php

declare(strict_types=1);

namespace App\Contracts;

use Carbon\Carbon;

interface ShiftFollowReportServiceInterface
{
    /**
     * Kullanıcının günlük vardiya takip bilgilerini getirir.
     *
     * @param int $userId
     * @param Carbon $date
     * @return array
     */
    public function getDailyReport(int $userId, Carbon $date): array;

    /**
     * Kullanıcının haftalık vardiya takip özetini getirir.
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getWeeklyReport(int $userId, string $startDate, string $endDate): array;
}
