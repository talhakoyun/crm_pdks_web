<?php

declare(strict_types=1);

namespace App\Http\Resources\Shift;

use Illuminate\Http\Resources\Json\JsonResource;

class ShiftFollowDailyReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'date' => $this['date'],
            'all' => $this['all'],
            'grouped' => $this['grouped'],
        ];
    }
}
