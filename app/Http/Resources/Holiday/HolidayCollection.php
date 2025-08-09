<?php

declare(strict_types=1);

namespace App\Http\Resources\Holiday;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class HolidayCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($holiday) {
                return new HolidayResource($holiday);
            }),
            'meta' => [
                'total' => $this->resource->total(),
                'count' => $this->resource->count(),
                'per_page' => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'last_page' => $this->resource->lastPage(),
            ],
        ];
    }
}
