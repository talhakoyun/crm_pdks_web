<?php

declare(strict_types=1);

namespace App\Http\Resources\Holiday;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class HolidayTypeCollection extends ResourceCollection
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
            'data' => $this->collection->map(function ($holidayType) {
                return new HolidayTypeResource($holidayType);
            }),
        ];
    }
}
