<?php

declare(strict_types=1);

namespace App\Http\Resources\Holiday;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayResource extends JsonResource
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
            case 'canceled':
                $confirm = 2;
                $confirm_text = 'reddedildi';
                break;
        }
        return [
            'id' => $this->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'duration' => $this->calculateDuration(),
            'type' => new HolidayTypeResource($this->holidayType),
            'status' => $confirm,
            'status_text' => ucwords($confirm_text),
            'note' => $this->note,
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
        ];
    }

    /**
     * İzin süresini hesaplar.
     *
     * @return int
     */
    private function calculateDuration(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);

        // İş günü olarak hesaplamak için +1 ekliyoruz (bitiş günü dahil)
        return (int)($startDate->diffInDays($endDate) + 1);
    }
}
