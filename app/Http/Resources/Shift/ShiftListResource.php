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

        $data = [
            'id' => $this['id'] ?? null,
            'dateTime' => Carbon::parse($this['datetime'])->format('Y-m-d'),
        ];

        // 'shift' tipi için giriş/çıkış bilgileri
        $data['action_type'] = $this['action_type'] ?? null; // check_in veya check_out

        return $data;
    }
}
