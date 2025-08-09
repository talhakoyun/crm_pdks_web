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
        $type = $this['type'] ?? 'unknown';
        $formattedDate = isset($this['date']) ? $this['date'] : null;

        $data = [
            'id' => $this['id'] ?? null,
            'date' => $formattedDate,
            'time' => $this['time'] ?? null,
            'datetime' => $this['datetime'] ?? null,
            'type' => $type,
        ];

        // 'shift' tipi için giriş/çıkış bilgileri
        if ($type === 'shift') {
            $data['action_type'] = $this['action_type'] ?? null; // check_in veya check_out
            $data['branch'] = $this['branch'] ?? null;
            $data['zone'] = $this['zone'] ?? null;
        }

        // 'zone' tipi için bölge bilgileri
        if ($type === 'zone') {
            $data['zone_name'] = $this['zone_name'] ?? 'Bilinmeyen';
            $data['zone_id'] = $this['zone_id'] ?? null;
        }

        return $data;
    }
}
