<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class OfficialHolidayService
{
    private const API_URL = 'https://api.genelpara.com/tatil/';
    private const CACHE_KEY = 'official_holidays_';
    private const CACHE_TTL = 86400; // 1 gün

    /**
     * Belirli bir yıla ait resmi tatilleri getirir
     *
     * @param int|null $year Yıl (null ise içinde bulunduğumuz yıl)
     * @return array Tatil günleri dizisi
     */
    public function getOfficialHolidays(?int $year = null): array
    {
        $year = $year ?? Carbon::now()->year;
        $cacheKey = self::CACHE_KEY . $year;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($year) {
            try {
                $response = Http::get(self::API_URL . $year);

                if ($response->successful()) {
                    $holidays = $response->json('data', []);
                    return $this->formatHolidays($holidays, $year);
                }

                return [];
            } catch (\Exception $e) {
                // API hatası durumunda boş dizi döndür
                return [];
            }
        });
    }

    /**
     * API'den gelen tatil günlerini formatlayarak döndürür
     *
     * @param array $holidays API'den gelen tatil günleri
     * @param int $year Yıl
     * @return array Formatlanmış tatil günleri
     */
    private function formatHolidays(array $holidays, int $year): array
    {
        $formattedHolidays = [];

        foreach ($holidays as $holiday) {
            // Tarih formatını düzenleme
            if (isset($holiday['date'])) {
                $date = Carbon::createFromFormat('d.m.Y', $holiday['date']);

                $formattedHolidays[] = [
                    'title' => $holiday['title'] ?? 'Bilinmeyen Tatil',
                    'description' => $holiday['description'] ?? '',
                    'start_date' => $date->format('Y-m-d'),
                    'end_date' => $date->format('Y-m-d'),
                    'is_half_day' => $holiday['half_day'] ?? false,
                    'is_official' => true
                ];
            }
        }

        return $formattedHolidays;
    }


}
