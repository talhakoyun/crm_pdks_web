<?php

declare(strict_types=1);

namespace App\Http\Resources\HourlyLeave;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HourlyLeaveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        switch ($this->status) {
            case 'pending':
                $confirm = 0;
                $confirm_text = 'beklemede';
                break;
            case 'approved':
                $confirm = 1;
                $confirm_text = 'onaylandı';
                break;
            case 'rejected':
                $confirm = 2;
                $confirm_text = 'reddedildi';
                break;
            default:
                $confirm = 0;
                $confirm_text = 'beklemede';
        }

        return [
            'id' => $this->id,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'duration' => $this->calculateDuration(),
            'type' => new \App\Http\Resources\Holiday\HolidayTypeResource($this->holidayType),
            'status' => $confirm,
            'status_text' => ucwords($confirm_text),
            'reason' => $this->reason,
            'branch' => $this->whenLoaded('branch', function() {
                return [
                    'id' => $this->branch->id,
                    'title' => $this->branch->title,
                ];
            }),
            'company' => $this->whenLoaded('company', function() {
                return [
                    'id' => $this->company->id,
                    'title' => $this->company->title,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Saatlik izin süresini hesaplar (dakika cinsinden).
     *
     * @return int
     */
    private function calculateDuration(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        return (int)$startTime->diffInMinutes($endTime);
    }
} 