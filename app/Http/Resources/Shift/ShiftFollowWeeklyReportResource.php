<?php

declare(strict_types=1);

namespace App\Http\Resources\Shift;

use Illuminate\Http\Resources\Json\JsonResource;

class ShiftFollowWeeklyReportResource extends JsonResource
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
            'start_date' => $this['start_date'],
            'end_date' => $this['end_date'],
            'user_id' => $this['user_id'],
            'daily_summaries' => $this['daily_summaries'],
            'weekly_totals' => $this['weekly_totals'],
        ];
    }
}
