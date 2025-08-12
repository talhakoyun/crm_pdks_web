<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ShiftFollowReportServiceInterface;
use App\Models\ShiftFollow;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ShiftFollowReportService implements ShiftFollowReportServiceInterface
{
    /**
     * ShiftFollow model instance.
     *
     * @var ShiftFollow
     */
    protected ShiftFollow $shiftFollowModel;

    /**
     * ShiftFollowReportService constructor.
     *
     * @param ShiftFollow $shiftFollowModel
     */
    public function __construct(ShiftFollow $shiftFollowModel)
    {
        $this->shiftFollowModel = $shiftFollowModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getDailyReport(int $userId, Carbon $date): array
    {
        // Kullanıcının belirtilen tarihteki vardiya takip kayıtlarını getir
        $items = $this->getShiftFollowsByDate($userId, $date);

        // Tiplerine göre gruplama
        $groupedItems = $this->groupItemsByType($items);

        return [
            'date' => $date->format('Y-m-d'),
            'all' => $items,
            'grouped' => $groupedItems
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getWeeklyReport(int $userId, string $startDate, string $endDate): array
    {
        // Tarih aralığını doğrula
        $this->validateDateRange($startDate, $endDate);

        // Günlük özet verileri hazırla
        $dailySummaries = $this->prepareDailySummaries($userId, $startDate, $endDate);

        // Haftalık toplam istatistikleri hesapla
        $weeklyTotals = $this->calculateWeeklyTotals($dailySummaries);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_id' => $userId,
            'daily_summaries' => $dailySummaries,
            'weekly_totals' => $weeklyTotals
        ];
    }

    /**
     * Kullanıcının belirtilen tarihteki vardiya takip kayıtlarını getirir.
     *
     * @param int $userId
     * @param Carbon $date
     * @return Collection
     */
    private function getShiftFollowsByDate(int $userId, Carbon $date): Collection
    {
        return $this->shiftFollowModel->newQuery()
            ->where('user_id', $userId)
            ->whereDate('transaction_date', $date->format('Y-m-d'))
            ->with(['user', 'branch', 'zone', 'shift', 'followType'])
            ->orderBy('transaction_date', 'asc')
            ->get();
    }

    /**
     * Vardiya takip kayıtlarını tiplerine göre gruplar.
     *
     * @param Collection $items
     * @return array
     */
    private function groupItemsByType(Collection $items): array
    {
        return [
            'entries' => $items->filter(function ($item) {
                return $item->followType && $item->followType->type === 'in';
            })->values(),
            'exits' => $items->filter(function ($item) {
                return $item->followType && $item->followType->type === 'check_out';
            })->values(),
            'zone_entries' => $items->filter(function ($item) {
                return $item->followType && $item->followType->type === 'zone_entry';
            })->values(),
            'zone_exits' => $items->filter(function ($item) {
                return $item->followType && $item->followType->type === 'zone_exit';
            })->values(),
            'breaks' => $items->filter(function ($item) {
                return $item->followType &&
                    ($item->followType->type === 'break_start' || $item->followType->type === 'break_end');
            })->values()
        ];
    }

    /**
     * Tarih aralığını doğrular.
     *
     * @param string $startDate
     * @param string $endDate
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateDateRange(string $startDate, string $endDate): void
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            throw new \InvalidArgumentException('Geçersiz tarih formatı. YYYY-MM-DD kullanmalısınız.');
        }
    }

    /**
     * Günlük özet verileri hazırlar.
     *
     * @param int $userId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function prepareDailySummaries(int $userId, string $startDate, string $endDate): array
    {
        $dailySummaries = [];
        $currentDate = Carbon::parse($startDate)->startOfDay();
        $lastDate = Carbon::parse($endDate)->startOfDay();

        while ($currentDate->lte($lastDate)) {
            $dateStr = $currentDate->format('Y-m-d');

            // O günün giriş-çıkışlarını bul
            $entries = $this->getShiftFollowsByDate($userId, $currentDate);

            // İlk giriş ve son çıkış saatleri
            $firstEntry = $entries->first(function ($item) {
                return $item->followType && $item->followType->type === 'in';
            });

            $lastExit = $entries->sortByDesc('transaction_date')->first(function ($item) {
                return $item->followType && $item->followType->type === 'out';
            });

            // Çalışma süresi hesapla
            $workDuration = $this->calculateWorkDuration($firstEntry, $lastExit);

            // Mola süreleri
            $breakDuration = $this->calculateBreakDuration($entries);

            // Özet bilgisini ekle
            $dailySummaries[] = [
                'date' => $dateStr,
                'day_name' => $currentDate->locale('tr')->dayName,
                'first_entry' => $firstEntry ? $firstEntry->transaction_date : null,
                'last_exit' => $lastExit ? $lastExit->transaction_date : null,
                'total_work_minutes' => $workDuration,
                'total_break_minutes' => $breakDuration,
                'net_work_minutes' => $workDuration !== null ? ($workDuration - $breakDuration) : null,
                'entry_count' => $entries->filter(function ($item) {
                    return $item->followType && $item->followType->type === 'in';
                })->count(),
                'exit_count' => $entries->filter(function ($item) {
                    return $item->followType && $item->followType->type === 'out';
                })->count(),
                'break_count' => $entries->filter(function ($item) {
                    return $item->followType && ($item->followType->type === 'break_start' || $item->followType->type === 'break_end');
                })->count() / 2, // Mola başlangıç ve bitişleri ikili olarak sayılır
                'all_records' => $entries->count()
            ];

            $currentDate->addDay();
        }

        return $dailySummaries;
    }

    /**
     * Çalışma süresini hesaplar.
     *
     * @param mixed $firstEntry
     * @param mixed $lastExit
     * @return float|null
     */
    private function calculateWorkDuration($firstEntry, $lastExit): ?float
    {
        if (!$firstEntry || !$lastExit) {
            return null;
        }

        $startTime = Carbon::parse($firstEntry->transaction_date);
        $endTime = Carbon::parse($lastExit->transaction_date);

        // Çalışma süresini hesapla (giriş ve çıkış arasındaki fark)
        // Çıkış zamanı giriş zamanından sonra olmalı
        if ($endTime->gt($startTime)) {
            // Saniye cinsinden farkı hesapla ve dakikaya çevir
            $diffInSeconds = $endTime->getTimestamp() - $startTime->getTimestamp();
            return $diffInSeconds / 60;
        }

        // Eğer çıkış zamanı giriş zamanından önce ise, hata durumu
        return 0;
    }

    /**
     * Mola süresini hesaplar.
     *
     * @param Collection $entries
     * @return float
     */
    private function calculateBreakDuration(Collection $entries): float
    {
        $breakDuration = 0;
        $breakStart = null;

        foreach ($entries as $entry) {
            if ($entry->followType && $entry->followType->type === 'break_start') {
                $breakStart = Carbon::parse($entry->transaction_date);
            } elseif ($entry->followType && $entry->followType->type === 'break_end' && $breakStart) {
                $breakEnd = Carbon::parse($entry->transaction_date);

                // Mola süresini hesapla (mola bitiş - mola başlangıç)
                // Mola bitiş zamanı mola başlangıç zamanından sonra olmalı
                if ($breakEnd->gt($breakStart)) {
                    // Saniye cinsinden farkı hesapla ve dakikaya çevir
                    $diffInSeconds = $breakEnd->getTimestamp() - $breakStart->getTimestamp();
                    $breakDuration += $diffInSeconds / 60;
                }

                $breakStart = null;
            }
        }

        return $breakDuration;
    }

    /**
     * Haftalık toplam istatistikleri hesaplar.
     *
     * @param array $dailySummaries
     * @return array
     */
    private function calculateWeeklyTotals(array $dailySummaries): array
    {
        $totalWorkMinutes = array_sum(array_column($dailySummaries, 'total_work_minutes'));
        $totalBreakMinutes = array_sum(array_column($dailySummaries, 'total_break_minutes'));
        $totalNetWorkMinutes = array_sum(array_column($dailySummaries, 'net_work_minutes'));

        return [
            'total_work_minutes' => $totalWorkMinutes,
            'total_work_hours' => round($totalWorkMinutes / 60, 1),
            'total_break_minutes' => $totalBreakMinutes,
            'total_break_hours' => round($totalBreakMinutes / 60, 1),
            'total_net_work_minutes' => $totalNetWorkMinutes,
            'total_net_work_hours' => round($totalNetWorkMinutes / 60, 1),
        ];
    }
}
