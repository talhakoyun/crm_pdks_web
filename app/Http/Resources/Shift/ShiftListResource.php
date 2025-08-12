<?php

declare(strict_types=1);

namespace App\Http\Resources\Shift;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ShiftListResource - Günlük Vardiya Özeti
 *
 * Bu resource, vardiya takip kayıtlarını şu şekilde gruplar:
 * 1. Günlük giriş-çıkış kayıtları ('shift' tipi)
 * 2. Günlük bölge hareketleri ('zone' tipi)
 *
 * ShiftFollowService'in getUserShiftList metodu tarafından döndürülen
 * formatlı verileri işler.
 */
class ShiftListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        // Service'ten gelen günlük eşleşmiş veri: ['date' => Y-m-d, 'startTime' => H:i, 'endTime' => H:i|-]
        $date = $this['date'] ?? null;
        $startTime = $this['startTime'] ?? null;

        $datetime = null;
        if ($date && $startTime && $startTime !== '-') {
            // 'YYYY-MM-DDTHH:MM:SS.uuuuuuZ' formatı (UTC)
            $datetime = Carbon::parse($date . ' ' . $startTime)
                ->utc()
                ->format('Y-m-d\TH:i:s.u\Z');
        }

        return [
            'datetime' => $datetime,
            'start_time' => $startTime,
            'end_time' => $this['endTime'] ?? null,
        ];
    }
}
