<?php

declare(strict_types=1);

namespace App\Http\Resources\Shift;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ShiftFollowResource - Ham Vardiya Takip Kaydı
 *
 * Bu resource, ayrıntılı ve filtrelenebilir vardiya takip kayıtlarını
 * işlemek için kullanılır. Her bir takip kaydını ayrı ayrı gösterir.
 *
 * Kullanım: /api/shift-follow/records API endpointi tarafından
 * listShiftFollows metodunda kullanılır.
 */
class ShiftFollowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_date' => $this->transaction_date,
            'type' => $this->followType?->type,
            'status' => $this->status,
            'status_minutes' => $this->status_minutes,
            'shift' => $this->whenLoaded('shift', function() {
                return [
                    'id' => $this->shift->id,
                    'title' => $this->shift->title,
                    'start_time' => $this->shift->start_time,
                    'end_time' => $this->shift->end_time,
                ];
            }),
        ];
    }
}
