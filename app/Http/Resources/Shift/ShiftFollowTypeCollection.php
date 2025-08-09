<?php

declare(strict_types=1);

namespace App\Http\Resources\Shift;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ShiftFollowTypeCollection extends ResourceCollection
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
            'data' => $this->collection->map(function ($followType) {
                return new ShiftFollowTypeResource($followType);
            }),
        ];
    }
}
