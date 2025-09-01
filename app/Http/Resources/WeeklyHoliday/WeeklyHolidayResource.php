<?php

namespace App\Http\Resources\WeeklyHoliday;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeeklyHolidayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user->name ?? 'Tanımsız',
            'user_email' => $this->user->email ?? 'Tanımsız',
            'branch' => $this->user->branch->title ?? 'Tanımsız',
            'department' => $this->user->department->title ?? 'Tanımsız',
            'holiday_days' => $this->holiday_days,
            'holiday_days_string' => $this->holiday_days_string,
            'holiday_day_names' => $this->holiday_day_names,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('d.m.Y H:i'),
            'updated_at' => $this->updated_at?->format('d.m.Y H:i'),
            'created_by' => $this->createdBy->name ?? 'Sistem',
        ];
    }
}
