<?php

namespace App\Http\Resources\Production;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ProductionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_date' => $this->plan?->op?->order?->order_date,
            'order_no' => $this->plan?->op?->order?->order_no,
            'current_code' => $this->plan?->op?->current?->code,
            'item_no' => $this->plan?->op?->item_no,
            'product_group' => $this->plan?->op?->productGroup?->name,
            'gram' => $this->plan?->op?->product?->gram,
            'width' => $this->plan?->op?->product?->width,
            'height' => $this->plan?->op?->product?->height,
            'color_code' => $this->plan?->op?->product?->color?->code,
            'color_name' => $this->plan?->op?->product?->color?->name,
            'bobin' => $this->plan?->op?->bobin,
            'manufactured_bobin' => $this->manufactured_bobin,
            'remaining_bobin' => (($this->plan?->op?->bobin ?? 0) - ($this->manufactured_bobin ?? 0)),
            'metre' => $this->plan?->op?->getMetreCalculate(),
            'square_meters' => $this->plan?->op?->getSquareMetersCalculate(),
            'kilogram' => $this->plan?->op?->getKilogramCalculate(),
        ];
    }
}
