<?php

namespace App\Http\Resources\WeeklyHoliday;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class WeeklyHolidayCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'per_page' => $request->get('per_page', 15),
                'current_page' => $request->get('page', 1),
            ]
        ];
    }
}
