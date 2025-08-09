<?php

namespace App\Http\Resources\Shipment;

use Illuminate\Http\Resources\Json\JsonResource;

class ShipmentResource extends JsonResource
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
            'shipment_date' => $this->shipment_date?->format('d-m-Y'),
            'current_code' => $this->production?->plan?->op?->order?->current?->code,
            'current_name' => $this->production?->plan?->op?->order?->current?->name,
            'order_no' => $this->production?->plan?->op?->order?->order_no,
            'order_date' => $this->production?->plan?->op?->order?->order_date,
            'item_no' => $this->production?->plan?->op?->item_no,
            'product_group_code' => $this->production?->plan?->op?->product?->productGroup?->code,
            'product_group_name' => $this->production?->plan?->op?->product?->productGroup?->name,
            'product_code' => $this->production?->plan?->op?->product?->code,
            'gram' => $this->production?->plan?->op?->product?->gram,
            'width' => $this->production?->plan?->op?->product?->width,
            'height' => $this->production?->plan?->op?->product?->height,
            'color_code' => $this->production?->plan?->op?->product?->color?->code,
            'color_name' => $this->production?->plan?->op?->product?->color?->name,
            'metre' => $this->production?->plan?->op?->getMetreCalculate(),
            'square_meters' => $this->production?->plan?->op?->getSquareMetersCalculate(),
            'kilogram' => $this->production?->plan?->op?->getKilogramCalculate(),
            'label_type_id' => $this->production?->plan?->op?->order?->label?->id,
            'label_type_name' => $this->production?->plan?->op?->order?->label?->name,
            'product_name' => $this->production?->plan?->op?->product?->fullname,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d-m-Y H:i:s'),
        ];
    }
}
